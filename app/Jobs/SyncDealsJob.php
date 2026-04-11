<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\Deal;
use App\Models\Trade;
use App\Services\MT5RestAPIService;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncDealsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $timeout = 600; // 10 minutes max per job
    public $tries = 3;
    public $maxExceptions = 2;
    public $backoff = [30, 120]; // retry after 30s, then 2min
    public $uniqueFor = 300; // 5 min uniqueness per account set

    protected $accountIds;
    protected $maxPagesPerAccount;

    /** Max deals to fetch in a single REST call per account */
    protected const MAX_DEALS_PER_REST_CALL = 5000;

    /** Days since last sync before an incremental account is treated as "stale" and processed individually */
    protected const STALE_INCREMENTAL_DAYS = 7;

    public function uniqueId(): string
    {
        return 'sync-deals-' . collect($this->accountIds)->sort()->join('-');
    }

    public function __construct(array $accountIds, int $maxPagesPerAccount = 20)
    {
        $this->accountIds = $accountIds;
        $this->maxPagesPerAccount = $maxPagesPerAccount;
        $this->onQueue('syncaccountstrades');
    }

    /**
     * Handle job failure after all retries exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SyncDealsJob permanently failed', [
            'account_ids' => $this->accountIds,
            'error' => $exception->getMessage(),
        ]);

        Account::whereIn('id', $this->accountIds)->update([
            'sync_status' => 'needs_retry',
            'sync_error' => substr($exception->getMessage(), 0, 500),
        ]);
    }

    public function handle(): void
    {
        $jobStart = microtime(true);
        $currentPhase = 'init';

        // Register a shutdown function to capture timeouts before the worker kills us
        $accountIds = $this->accountIds;
        register_shutdown_function(function () use ($jobStart, &$currentPhase, $accountIds) {
            $elapsed = round(microtime(true) - $jobStart, 2);
            if ($elapsed > ($this->timeout * 0.9)) {
                Log::error('SyncDealsJob: Likely timeout detected in shutdown', [
                    'phase' => $currentPhase,
                    'elapsed_seconds' => $elapsed,
                    'timeout' => $this->timeout,
                    'account_ids' => $accountIds,
                ]);
                Account::whereIn('id', $accountIds)
                    ->where('sync_status', 'pending')
                    ->update([
                        'sync_status' => 'needs_retry',
                        'sync_error' => "Timeout after {$elapsed}s during phase: {$currentPhase}",
                    ]);
            }
        });

        // ── Load accounts and build login → account map ──
        $accounts = Account::whereIn('id', $this->accountIds)->get()->keyBy('id');
        if ($accounts->isEmpty()) {
            return;
        }

        // Build per-account time windows (incremental sync)
        $accountWindows = [];
        foreach ($accounts as $acct) {
            $lastSync = $acct->deals_synced_to;
            $from = $lastSync
                ? Carbon::parse($lastSync)->subHour()->timestamp
                : Carbon::parse('2024-09-01')->timestamp;
            $to = Carbon::parse('2080-03-31')->timestamp;

            $accountWindows[$acct->id] = [
                'account' => $acct,
                'login' => (int) $acct->code,
                'from' => $from,
                'to' => $to,
            ];
        }

        // ── Sync all accounts via REST batch API (no socket fallback) ──
        $restResult = $this->syncViaRestBatch($accountWindows);

        $totalDealsInserted = $restResult['inserted'];
        $closedPositionsBatch = $restResult['closed_positions'];
        $failedAccountIds = $restResult['failed_account_ids'] ?? [];

        // Mark failed accounts for retry on next cycle
        if (!empty($failedAccountIds)) {
            Log::warning('SyncDealsJob: Accounts failed REST sync, will retry next cycle', [
                'failed_accounts' => count($failedAccountIds),
                'failed_account_ids' => $failedAccountIds,
            ]);
            Account::whereIn('id', $failedAccountIds)->update([
                'sync_status' => 'error',
                'sync_error' => 'REST API fetch failed, will retry next cycle',
            ]);
        }

        // Dispatch remaining closed positions
        if (!empty($closedPositionsBatch)) {
            ProcessClosedDealCommissionJob::dispatch($closedPositionsBatch)
                ->onQueue('distributeibcommission');
        }

        // Mark successfully synced accounts
        $successfulIds = array_diff($this->accountIds, $failedAccountIds);
        if (!empty($successfulIds)) {
            Account::whereIn('id', $successfulIds)->update([
                'sync_status' => 'synced',
                'sync_error' => null,
                'sync_stuck_count' => 0,
            ]);
        }

        Log::info("SyncDealsJob completed", [
            'accounts' => count($this->accountIds),
            'total_deals_inserted' => $totalDealsInserted,
            'rest_success' => count($this->accountIds) - count($failedAccountIds),
            'rest_failed' => count($failedAccountIds),
            'duration_seconds' => round(microtime(true) - $jobStart, 2),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  REST Batch Path
    // ─────────────────────────────────────────────────────────

    /**
     * Fetch deals for all accounts in one (or few) REST calls,
     * then upsert into DB and detect closed positions.
     */
    protected function syncViaRestBatch(array $accountWindows): array
    {
        $inserted = 0;
        $closedPositions = [];
        $failedAccountIds = [];

        try {
            $restService = app(MT5RestAPIService::class);
        } catch (Exception $e) {
            Log::warning('SyncDealsJob: REST service unavailable', [
                'error' => $e->getMessage(),
            ]);
            return [
                'inserted' => 0,
                'closed_positions' => [],
                'failed_account_ids' => array_keys($accountWindows),
            ];
        }

        // Separate accounts into 3 buckets based on sync recency:
        // - Fresh incremental: synced within last N days → safe for batch REST
        // - Stale incremental: synced > N days ago → process individually with volume awareness
        // - Never-synced: no deals_synced_to → process individually with volume awareness
        $freshIncrementalAccounts = [];
        $staleAccounts = []; // stale-incremental + never-synced
        $staleThresholdDate = Carbon::now()->subDays(self::STALE_INCREMENTAL_DAYS);

        foreach ($accountWindows as $acctId => $w) {
            if ($w['account']->deals_synced_to === null) {
                $staleAccounts[$acctId] = $w;
            } elseif (Carbon::parse($w['account']->deals_synced_to)->lt($staleThresholdDate)) {
                $staleAccounts[$acctId] = $w;
            } else {
                $freshIncrementalAccounts[$acctId] = $w;
            }
        }

        // Pre-load existing deal_ids for all accounts (one query)
        $allAccountIds = array_keys($accountWindows);
        $existingDealIds = Deal::whereIn('account_id', $allAccountIds)
            ->pluck('deal_id')
            ->flip()
            ->toArray();

        // ── 1. Process fresh incremental accounts via batch REST (tight window) ──
        if (!empty($freshIncrementalAccounts)) {
            $globalFrom = PHP_INT_MAX;
            $globalTo = 0;
            $logins = [];

            foreach ($freshIncrementalAccounts as $acctId => $w) {
                $globalFrom = min($globalFrom, $w['from']);
                $globalTo = max($globalTo, $w['to']);
                $logins[] = $w['login'];
            }

            Log::info('SyncDealsJob: Fetching fresh incremental deals via REST batch', [
                'account_count' => count($freshIncrementalAccounts),
                'global_from' => Carbon::createFromTimestamp($globalFrom)->toDateTimeString(),
                'global_to' => Carbon::createFromTimestamp($globalTo)->toDateTimeString(),
            ]);

            $batchResult = $restService->getBatchDeals($logins, $globalFrom, $globalTo);
            $dealsByLogin = $batchResult['deals'];

            foreach ($freshIncrementalAccounts as $acctId => $w) {
                $login = (string) $w['login'];
                $account = $w['account'];
                $rawDeals = $dealsByLogin[$login] ?? null;

                if ($rawDeals === null) {
                    $failedAccountIds[] = $acctId;
                    continue;
                }

                $accountFrom = $w['from'];
                $result = $this->processRestDeals($rawDeals, $acctId, $account, $accountFrom, $existingDealIds);
                $inserted += $result['inserted'];
                $closedPositions = array_merge($closedPositions, $result['closed_positions']);

                $this->syncTrades($acctId, $account, $result['new_deal_ids']);

                if (count($closedPositions) >= 100) {
                    ProcessClosedDealCommissionJob::dispatch(array_splice($closedPositions, 0, 100))
                        ->onQueue('distributeibcommission');
                }
            }
        }

        // ── 2. Process stale / never-synced accounts individually with volume awareness ──
        if (!empty($staleAccounts)) {
            $staleIncrementalCount = count(array_filter($staleAccounts, fn($w) => $w['account']->deals_synced_to !== null));
            $neverSyncedCount = count($staleAccounts) - $staleIncrementalCount;

            Log::info('SyncDealsJob: Processing stale/never-synced accounts with date-range splitting', [
                'total' => count($staleAccounts),
                'stale_incremental' => $staleIncrementalCount,
                'never_synced' => $neverSyncedCount,
                'stale_threshold_days' => self::STALE_INCREMENTAL_DAYS,
            ]);

            foreach ($staleAccounts as $acctId => $w) {
                try {
                    $result = $this->syncFirstTimeAccountViaRest($restService, $acctId, $w, $existingDealIds);
                    $inserted += $result['inserted'];
                    $closedPositions = array_merge($closedPositions, $result['closed_positions']);

                    if (count($closedPositions) >= 100) {
                        ProcessClosedDealCommissionJob::dispatch(array_splice($closedPositions, 0, 100))
                            ->onQueue('distributeibcommission');
                    }
                } catch (Exception $e) {
                    Log::error("SyncDealsJob: First-time sync failed for account {$acctId}", [
                        'error' => $e->getMessage(),
                    ]);
                    $failedAccountIds[] = $acctId;
                }
            }
        }

        return [
            'inserted' => $inserted,
            'closed_positions' => $closedPositions,
            'failed_account_ids' => $failedAccountIds,
        ];
    }

    /**
     * Sync a never-synced account via REST API with date-range splitting for high-volume accounts.
     *
     * Uses getDealTotals() to check volume, then:
     * - <= MAX_DEALS_PER_REST_CALL: single REST call
     * - > MAX_DEALS_PER_REST_CALL: split time range into smaller chunks
     */
    protected function syncFirstTimeAccountViaRest(
        MT5RestAPIService $restService,
        string $accountId,
        array $window,
        array &$existingDealIds
    ): array {
        $login = $window['login'];
        $account = $window['account'];
        $from = $window['from'];
        $to = $window['to'];

        // Get total deal count to plan fetch strategy (1 lightweight API call)
        $totals = $restService->getDealTotals([$login], $from, $to);
        $totalDeals = $totals[(string) $login] ?? 0;

        Log::info("SyncDealsJob: First-time account deal count", [
            'account_id' => $accountId,
            'login' => $login,
            'total_deals' => $totalDeals,
        ]);

        if ($totalDeals === 0) {
            // No deals — just update cursors
            $this->processRestDeals([], $accountId, $account, $from, $existingDealIds);
            return ['inserted' => 0, 'closed_positions' => [], 'new_deal_ids' => []];
        }

        if ($totalDeals <= self::MAX_DEALS_PER_REST_CALL) {
            // Small enough — fetch everything in one REST call
            $batchResult = $restService->getBatchDeals([$login], $from, $to);
            $rawDeals = $batchResult['deals'][(string) $login] ?? [];

            if (empty($rawDeals) && $totalDeals > 0) {
                throw new Exception("REST API returned no data for login {$login} (expected {$totalDeals} deals)");
            }

            $result = $this->processRestDeals($rawDeals, $accountId, $account, $from, $existingDealIds);
            $this->syncTrades($accountId, $account, $result['new_deal_ids']);
            return $result;
        }

        // ── High-volume account: split date range into manageable chunks ──
        $effectiveTo = Carbon::now()->timestamp;
        $chunks = $this->calculateDateRangeChunks($from, $effectiveTo, $totalDeals);

        Log::info("SyncDealsJob: Splitting high-volume first-time account into date chunks", [
            'account_id' => $accountId,
            'login' => $login,
            'total_deals' => $totalDeals,
            'chunks' => count($chunks),
            'max_deals_per_call' => self::MAX_DEALS_PER_REST_CALL,
        ]);

        $totalInserted = 0;
        $allClosedPositions = [];
        $allNewDealIds = [];

        foreach ($chunks as $i => $chunk) {
            // Use the original far-future $to for the last chunk to catch everything
            $chunkTo = ($i === count($chunks) - 1) ? $to : $chunk['to'];

            $batchResult = $restService->getBatchDeals([$login], $chunk['from'], $chunkTo);
            $rawDeals = $batchResult['deals'][(string) $login] ?? [];

            if (empty($rawDeals)) {
                Log::info("SyncDealsJob: Chunk " . ($i + 1) . "/" . count($chunks) . " empty for {$login}");
                continue;
            }

            // Only mark complete on the last chunk
            $isLastChunk = ($i === count($chunks) - 1);
            $result = $this->processRestDeals($rawDeals, $accountId, $account, $chunk['from'], $existingDealIds, $isLastChunk);
            $totalInserted += $result['inserted'];
            $allClosedPositions = array_merge($allClosedPositions, $result['closed_positions']);
            $allNewDealIds = array_merge($allNewDealIds, $result['new_deal_ids']);

            Log::info("SyncDealsJob: Chunk completed", [
                'account_id' => $accountId,
                'login' => $login,
                'chunk' => ($i + 1) . '/' . count($chunks),
                'deals_in_chunk' => count($rawDeals),
                'inserted' => $result['inserted'],
            ]);
        }

        // Sync trades once with all new deal IDs from all chunks
        $this->syncTrades($accountId, $account, $allNewDealIds);

        return ['inserted' => $totalInserted, 'closed_positions' => $allClosedPositions, 'new_deal_ids' => $allNewDealIds];
    }

    /**
     * Calculate optimal date-range chunks to keep each REST call under the deal limit.
     *
     * Evenly divides the time range based on total deals / max per call.
     * Deal distribution isn't perfectly uniform, but this keeps each chunk
     * within a reasonable size for the REST API.
     */
    protected function calculateDateRangeChunks(int $from, int $to, int $totalDeals): array
    {
        $numChunks = (int) ceil($totalDeals / self::MAX_DEALS_PER_REST_CALL);
        $totalSeconds = max($to - $from, 1);
        $chunkDuration = (int) ceil($totalSeconds / $numChunks);

        $chunks = [];
        $currentFrom = $from;
        for ($i = 0; $i < $numChunks; $i++) {
            $currentTo = min($currentFrom + $chunkDuration, $to);
            $chunks[] = ['from' => $currentFrom, 'to' => $currentTo];
            $currentFrom = $currentTo;
        }

        return $chunks;
    }

    /**
     * Transform REST deal arrays into DB rows, upsert, and detect closes.
     */
    protected function processRestDeals(array $rawDeals, string $accountId, Account $account, int $accountFrom, array &$existingDealIds, bool $markComplete = true): array
    {
        $dealsToInsert = [];
        $closedPositions = [];
        $newDealIds = [];
        $latestTimeDone = null;

        foreach ($rawDeals as $deal) {
            $dealId = $deal['Deal'] ?? null;
            if (!$dealId) {
                continue;
            }

            // Only buy/sell (Action 0=buy, 1=sell)
            $action = (int) ($deal['Action'] ?? -1);
            if (!in_array($action, [0, 1])) {
                continue;
            }

            // Skip entries without symbol (deposits/withdrawals from MT5 API)
            $symbol = $deal['Symbol'] ?? '';
            if (empty(trim($symbol))) {
                continue;
            }

            $timeDone = Carbon::createFromTimestamp((int) $deal['Time']);

            // Always track latest time for cursor advancement (even for dupes)
            if (!$latestTimeDone || $timeDone->gt($latestTimeDone)) {
                $latestTimeDone = $timeDone;
            }

            // Skip already-synced deals
            if (isset($existingDealIds[$dealId])) {
                continue;
            }

            // Skip deals before this account's incremental cursor
            if ($timeDone->timestamp < $accountFrom) {
                continue;
            }

            $volume = ((int) ($deal['Volume'] ?? 0)) / 10000;
            $entry = (int) ($deal['Entry'] ?? 0);
            $positionId = (int) ($deal['PositionID'] ?? 0);

            $dealsToInsert[] = [
                'account_id' => $accountId,
                'deal_id' => $dealId,
                'order_id' => $deal['Order'] ?? null,
                'position_id' => $positionId,
                'symbol' => $deal['Symbol'] ?? '',
                'type' => $action,
                'action' => $action,
                'entry' => $entry,
                'volume' => $volume,
                'price' => (float) ($deal['Price'] ?? 0),
                'profit' => (float) ($deal['Profit'] ?? 0),
                'swap' => (float) ($deal['Storage'] ?? 0),
                'commission' => (float) ($deal['Commission'] ?? 0),
                'comment' => $deal['Comment'] ?? null,
                'reason' => $deal['Reason'] ?? null,
                'time_done' => $timeDone,
                'time_msc' => $deal['TimeMsc'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $existingDealIds[$dealId] = true;
            $newDealIds[] = $dealId;

            // Track close deals for commission
            if ($entry == 1 && $positionId > 0) {
                $closedPositions[] = [
                    'account_id' => $accountId,
                    'position_id' => $positionId,
                    'deal_id' => $dealId,
                    'order_id' => $deal['Order'] ?? null,
                    'symbol' => $deal['Symbol'] ?? '',
                    'volume' => $volume,
                    'time_done' => $timeDone->toDateTimeString(),
                ];
            }
        }

        if (!empty($dealsToInsert)) {
            // Chunk upserts to avoid packet-size limits
            foreach (array_chunk($dealsToInsert, 500) as $chunk) {
                Deal::upsert($chunk, ['deal_id'], [
                    'order_id',
                    'position_id',
                    'symbol',
                    'type',
                    'action',
                    'entry',
                    'volume',
                    'price',
                    'profit',
                    'swap',
                    'commission',
                    'comment',
                    'reason',
                    'time_done',
                    'time_msc',
                    'updated_at',
                ]);
            }
        }

        // Always update sync cursors — even if no new deals were found
        $syncUpdate = [
            'deals_last_fetch_at' => now(),
            'trade_sync_status' => 'success',
            'last_trade_sync_at' => now(),
            'deals_synced_to' => $latestTimeDone ?? now(),
            'deals_synced_from' => $account->deals_synced_from ?? Carbon::parse('2024-09-01'),
        ];
        if ($markComplete) {
            $syncUpdate['deals_sync_complete'] = true;
        }
        Account::where('id', $accountId)->update($syncUpdate);
        Cache::forget("account:{$accountId}");

        return ['inserted' => count($dealsToInsert), 'closed_positions' => $closedPositions, 'new_deal_ids' => $newDealIds];
    }

    // ─────────────────────────────────────────────────────────
    //  Trades Table Sync (build position-level records from deals)
    // ─────────────────────────────────────────────────────────

    /**
     * Build/update trades table rows from newly inserted deals.
     * Groups deals by position_id: 1 deal = open trade, 2+ deals = closed trade.
     */
    protected function syncTrades(string $accountId, Account $account, array $newDealIds): void
    {
        if (empty($newDealIds)) {
            return;
        }

        // Get unique position_ids from the new deals
        $positionIds = Deal::where('account_id', $accountId)
            ->whereIn('deal_id', $newDealIds)
            ->where('position_id', '>', 0)
            ->distinct()
            ->pluck('position_id')
            ->toArray();

        if (empty($positionIds)) {
            return;
        }

        // Load ALL deals for these positions (need both open + close to build trades)
        $dealsByPosition = Deal::where('account_id', $accountId)
            ->whereIn('position_id', $positionIds)
            ->whereIn('action', [0, 1]) // buy/sell only
            ->orderBy('time_done', 'asc')
            ->get()
            ->groupBy('position_id');

        $tradesToUpsert = [];

        foreach ($dealsByPosition as $positionId => $deals) {
            if ($positionId == 0) {
                continue;
            }

            $openDeal = $deals->firstWhere('entry', 0);  // entry=in
            $closeDeal = $deals->firstWhere('entry', 1);  // entry=out

            if (!$openDeal) {
                continue;
            }

            $typeString = $openDeal->action == 0 ? 'buy' : 'sell';

            if ($closeDeal) {
                // Closed trade
                $tradesToUpsert[] = [
                    'account_id' => $accountId,
                    'code' => $account->code,
                    'order_id' => $openDeal->order_id,
                    'position_id' => $positionId,
                    'symbol' => $openDeal->symbol,
                    'type' => $typeString,
                    'volume' => $openDeal->volume,
                    'volume_ext' => 0,
                    'open_price' => $openDeal->price,
                    'close_price' => $closeDeal->price,
                    'profit' => $closeDeal->profit,
                    'swap' => $closeDeal->swap,
                    'commission' => $openDeal->commission + $closeDeal->commission,
                    'sl' => 0,
                    'tp' => 0,
                    'comment' => $openDeal->comment,
                    'status' => 'closed',
                    'open_time' => $openDeal->time_done,
                    'close_time' => $closeDeal->time_done,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                // Open trade (no close deal yet)
                $tradesToUpsert[] = [
                    'account_id' => $accountId,
                    'code' => $account->code,
                    'order_id' => $openDeal->order_id,
                    'position_id' => $positionId,
                    'symbol' => $openDeal->symbol,
                    'type' => $typeString,
                    'volume' => $openDeal->volume,
                    'volume_ext' => 0,
                    'open_price' => $openDeal->price,
                    'close_price' => null,
                    'profit' => 0,
                    'swap' => 0,
                    'commission' => $openDeal->commission,
                    'sl' => 0,
                    'tp' => 0,
                    'comment' => $openDeal->comment,
                    'status' => 'open',
                    'open_time' => $openDeal->time_done,
                    'close_time' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (count($tradesToUpsert) >= 500) {
                Trade::upsert($tradesToUpsert, ['account_id', 'position_id'], [
                    'order_id',
                    'symbol',
                    'type',
                    'volume',
                    'open_price',
                    'close_price',
                    'profit',
                    'swap',
                    'commission',
                    'comment',
                    'status',
                    'open_time',
                    'close_time',
                    'updated_at',
                ]);
                $tradesToUpsert = [];
            }
        }

        if (!empty($tradesToUpsert)) {
            Trade::upsert($tradesToUpsert, ['account_id', 'position_id'], [
                'order_id',
                'symbol',
                'type',
                'volume',
                'open_price',
                'close_price',
                'profit',
                'swap',
                'commission',
                'comment',
                'status',
                'open_time',
                'close_time',
                'updated_at',
            ]);
        }
    }
}
