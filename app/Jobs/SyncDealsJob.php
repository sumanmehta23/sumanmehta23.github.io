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
    //  Volume-Aware Account Processing
    // ─────────────────────────────────────────────────────────

    /**
     * Process all accounts individually with volume-aware fetching.
     *
     * Each account gets its own getDealTotals check, then either:
     * - Small volume: single REST call
     * - High volume: adaptive date-range chunking that verifies chunk sizes
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

        // Pre-load existing deal_ids for all accounts (one query)
        $allAccountIds = array_keys($accountWindows);
        $existingDealIds = Deal::whereIn('account_id', $allAccountIds)
            ->pluck('deal_id')
            ->flip()
            ->toArray();

        Log::info('SyncDealsJob: Processing accounts individually with volume awareness', [
            'account_count' => count($accountWindows),
        ]);

        // Process each account individually — no more batching multiple accounts
        // into one giant REST call that can timeout with high-volume accounts
        foreach ($accountWindows as $acctId => $w) {
            try {
                $result = $this->syncSingleAccount($restService, $acctId, $w, $existingDealIds);
                $inserted += $result['inserted'];
                $closedPositions = array_merge($closedPositions, $result['closed_positions']);

                if (count($closedPositions) >= 100) {
                    ProcessClosedDealCommissionJob::dispatch(array_splice($closedPositions, 0, 100))
                        ->onQueue('distributeibcommission');
                }
            } catch (Exception $e) {
                Log::error("SyncDealsJob: Sync failed for account {$acctId}", [
                    'error' => $e->getMessage(),
                ]);
                $failedAccountIds[] = $acctId;
            }
        }

        return [
            'inserted' => $inserted,
            'closed_positions' => $closedPositions,
            'failed_account_ids' => $failedAccountIds,
        ];
    }

    /**
     * Sync a single account via REST API with volume-aware fetching.
     *
     * Uses getDealTotals() to check deal count, then:
     * - 0 deals: just update cursors
     * - <= MAX_DEALS_PER_REST_CALL: single REST call
     * - > MAX_DEALS_PER_REST_CALL: adaptive date-range chunking
     */
    protected function syncSingleAccount(
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

        // Store the count for future batching decisions by the command
        Account::where('id', $accountId)->update([
            'pending_deal_count' => $totalDeals,
            'pending_deal_count_at' => now(),
        ]);

        Log::info("SyncDealsJob: Account deal count", [
            'account_id' => $accountId,
            'login' => $login,
            'total_deals' => $totalDeals,
            'from' => Carbon::createFromTimestamp($from)->toDateTimeString(),
            'to' => Carbon::createFromTimestamp(min($to, time()))->toDateTimeString(),
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

        // ── High-volume account: adaptive date-range chunking ──
        return $this->syncWithAdaptiveChunking($restService, $accountId, $login, $account, $from, $to, $totalDeals, $existingDealIds);
    }

    /**
     * Adaptive date-range chunking for high-volume accounts.
     *
     * Unlike fixed chunking, this verifies each chunk's deal count before fetching.
     * If a chunk has too many deals (uneven distribution), it halves the chunk duration
     * and retries — guaranteeing no single REST call fetches more than MAX_DEALS_PER_REST_CALL.
     */
    protected function syncWithAdaptiveChunking(
        MT5RestAPIService $restService,
        string $accountId,
        int $login,
        Account $account,
        int $from,
        int $to,
        int $totalDeals,
        array &$existingDealIds
    ): array {
        $effectiveTo = min($to, Carbon::now()->timestamp);

        // Start with estimated chunks (2x safety factor for uneven deal distribution)
        $numChunks = max(1, (int) ceil($totalDeals / self::MAX_DEALS_PER_REST_CALL) * 2);
        $chunkDuration = max(3600, (int) ceil(($effectiveTo - $from) / $numChunks)); // min 1 hour

        Log::info("SyncDealsJob: Adaptive chunking for high-volume account", [
            'account_id' => $accountId,
            'login' => $login,
            'total_deals' => $totalDeals,
            'estimated_chunks' => $numChunks,
            'chunk_duration_hours' => round($chunkDuration / 3600, 1),
        ]);

        $totalInserted = 0;
        $allClosedPositions = [];
        $allNewDealIds = [];
        $currentFrom = $from;
        $chunkIndex = 0;
        $maxIterations = $numChunks * 4; // safety limit to prevent infinite loops

        while ($currentFrom < $effectiveTo && $chunkIndex < $maxIterations) {
            $chunkIndex++;
            $currentTo = min($currentFrom + $chunkDuration, $effectiveTo);
            $isLastChunk = ($currentTo >= $effectiveTo);

            // For the last chunk, use the original far-future $to to catch everything
            $fetchTo = $isLastChunk ? $to : $currentTo;

            // Verify chunk size before fetching (lightweight count call)
            $chunkTotals = $restService->getDealTotals([$login], $currentFrom, $fetchTo);
            $chunkCount = $chunkTotals[(string) $login] ?? 0;

            // If chunk is too large and can still be split, halve the duration
            if ($chunkCount > self::MAX_DEALS_PER_REST_CALL && ($currentTo - $currentFrom) > 3600) {
                $chunkDuration = max(3600, (int) ($chunkDuration / 2));
                Log::info("SyncDealsJob: Chunk too large, splitting further", [
                    'account_id' => $accountId,
                    'chunk_deals' => $chunkCount,
                    'new_chunk_duration_hours' => round($chunkDuration / 3600, 1),
                ]);
                continue; // retry with smaller chunk (don't advance currentFrom)
            }

            // Fetch and process deals for this chunk
            $batchResult = $restService->getBatchDeals([$login], $currentFrom, $fetchTo);
            $rawDeals = $batchResult['deals'][(string) $login] ?? [];

            if (!empty($rawDeals)) {
                $result = $this->processRestDeals($rawDeals, $accountId, $account, $currentFrom, $existingDealIds, $isLastChunk);
                $totalInserted += $result['inserted'];
                $allClosedPositions = array_merge($allClosedPositions, $result['closed_positions']);
                $allNewDealIds = array_merge($allNewDealIds, $result['new_deal_ids']);

                Log::info("SyncDealsJob: Adaptive chunk completed", [
                    'account_id' => $accountId,
                    'chunk' => $chunkIndex,
                    'deals_in_chunk' => count($rawDeals),
                    'inserted' => $result['inserted'],
                    'chunk_from' => Carbon::createFromTimestamp($currentFrom)->toDateTimeString(),
                    'chunk_to' => Carbon::createFromTimestamp($fetchTo)->toDateTimeString(),
                ]);
            } elseif ($isLastChunk) {
                // Even if empty, mark complete on last chunk
                $this->processRestDeals([], $accountId, $account, $currentFrom, $existingDealIds, true);
            }

            $currentFrom = $currentTo;
        }

        // Sync trades once with all new deal IDs from all chunks
        $this->syncTrades($accountId, $account, $allNewDealIds);

        return ['inserted' => $totalInserted, 'closed_positions' => $allClosedPositions, 'new_deal_ids' => $allNewDealIds];
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
