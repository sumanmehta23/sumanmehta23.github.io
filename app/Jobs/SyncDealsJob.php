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
    protected $accountSyncRanges = []; // Track [from => timestamp, to => timestamp] per account

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

            // Validate lastSync before using it
            // If it's corrupted (zero date, invalid, etc), default to standard start
            $from = null;
            if ($lastSync && $lastSync !== '0000-00-00 00:00:00') {
                try {
                    $lastSyncCarbon = Carbon::parse($lastSync);
                    $fromTimestamp = $lastSyncCarbon->subHour()->timestamp;

                    // Validate the calculated from timestamp is reasonable (after 2024-01-01)
                    if ($fromTimestamp > 1704067200) {
                        $from = $fromTimestamp;
                    }
                } catch (\Exception $e) {
                    Log::warning("SyncDealsJob: Could not parse deals_synced_to, using default", [
                        'account_id' => $acct->id,
                        'deals_synced_to' => $lastSync,
                        'error' => $e->getMessage(),
                    ]);
                    // Fall through to use default
                }
            }

            // Use default if lastSync was invalid
            if ($from === null) {
                $from = Carbon::parse('2024-09-01')->timestamp;
            }

            // Cap window end to current time (not into far future like 2080)
            // This prevents unrealistic timestamps that MySQL rejects
            $to = Carbon::now()->timestamp;

            $accountWindows[$acct->id] = [
                'account' => $acct,
                'login' => (int) $acct->code,
                'from' => $from,
                'to' => $to,
            ];

            // Initialize sync range tracking for this account
            $this->accountSyncRanges[$acct->id] = [
                'from' => $from,
                'to' => $from, // will be updated as we process deals
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
            foreach ($successfulIds as $accountId) {
                if (isset($this->accountSyncRanges[$accountId])) {
                    $syncRange = $this->accountSyncRanges[$accountId];
                    $account = $accounts[$accountId];

                    // Validate timestamps before converting
                    // Ensure we have valid timestamps (not 0, not null, not too far in future)
                    $fromTimestamp = $syncRange['from'];
                    $toTimestamp = $syncRange['to'];

                    // Validate: from should be reasonable (after 2024-01-01)
                    if (!is_numeric($fromTimestamp) || $fromTimestamp < 1704067200) { // 2024-01-01
                        Log::warning("SyncDealsJob: Invalid 'from' timestamp, resetting to default", [
                            'account_id' => $accountId,
                            'from_timestamp' => $fromTimestamp,
                        ]);
                        // Use the start date as fallback
                        $fromTimestamp = Carbon::parse('2024-09-01')->timestamp;
                    }

                    // Validate: to should be reasonable (after from, before far future)
                    if (!is_numeric($toTimestamp) || $toTimestamp <= $fromTimestamp || $toTimestamp < 1704067200) {
                        Log::warning("SyncDealsJob: Invalid 'to' timestamp, skipping update", [
                            'account_id' => $accountId,
                            'from_timestamp' => $fromTimestamp,
                            'to_timestamp' => $toTimestamp,
                        ]);
                        continue;
                    }

                    // Convert to Carbon safely
                    $dealsFromCarbon = Carbon::createFromTimestamp($fromTimestamp);
                    $dealsToCarbon = Carbon::createFromTimestamp($toTimestamp);

                    // Double-check the Carbon objects are valid (not the zero date)
                    if ($dealsFromCarbon->year < 2024 || $dealsToCarbon->year < 2024) {
                        Log::warning("SyncDealsJob: Carbon conversion produced invalid date, skipping update", [
                            'account_id' => $accountId,
                            'from_carbon' => $dealsFromCarbon->toDateTimeString(),
                            'to_carbon' => $dealsToCarbon->toDateTimeString(),
                        ]);
                        continue;
                    }

                    Account::where('id', $accountId)->update([
                        'sync_status' => 'synced',
                        'sync_error' => null,
                        'sync_stuck_count' => 0,
                        'deals_synced_from' => $dealsFromCarbon->toDateTimeString(),
                        'deals_synced_to' => $dealsToCarbon->toDateTimeString(),
                    ]);

                    // Log::info("SyncDealsJob: Updated sync range for account", [
                    //     'account_id' => $accountId,
                    //     'account_code' => $account->code,
                    //     'deals_synced_from' => $dealsFromCarbon->toDateTimeString(),
                    //     'deals_synced_to' => $dealsToCarbon->toDateTimeString(),
                    // ]);
                }
            }
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
    //  Hybrid Batch/Adaptive Syncing (Speed + Reliability)
    // ─────────────────────────────────────────────────────────

    /**
     * Hybrid approach: batch sync most accounts, adaptive chunk only the risky ones.
     *
     * Separates accounts into:
     * 1. Safe for batching: small volume (<50k) + narrow window (<20 days) → batch REST call
     * 2. High-risk: high volume (>50k) OR wide window (>20 days) → adaptive chunking
     *
     * This maintains performance of batch syncing while protecting against timeouts.
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

        // ── Classify accounts: safe for batching vs high-risk ──
        $safeForBatching = [];
        $highRisk = [];
        $minDealsForAdaptive = 50000;  // Threshold for adaptive chunking
        $minWindowDaysForAdaptive = 20; // If window > 20 days, use adaptive chunking
        $minWindowSecsForAdaptive = $minWindowDaysForAdaptive * 86400;

        foreach ($accountWindows as $acctId => $w) {
            $from = $w['from'];
            $to = $w['to'];
            $windowSeconds = $to - $from;

            // Quick getDealTotals call to decide routing
            $login = (int) $w['login'];
            try {
                $totals = $restService->getDealTotals([$login], $from, $to);
                $dealCount = $totals[(string) $login] ?? 0;

                // Store the count
                Account::where('id', $acctId)->update([
                    'pending_deal_count' => $dealCount,
                    'pending_deal_count_at' => now(),
                ]);

                // Decide routing: high deals OR wide window?
                if ($dealCount > $minDealsForAdaptive && $windowSeconds > $minWindowSecsForAdaptive) {
                    $highRisk[$acctId] = $w;
                    Log::info("SyncDealsJob: High-risk account (adaptive chunking)", [
                        'account_id' => $acctId,
                        'deals' => $dealCount,
                        'window_days' => round($windowSeconds / 86400, 1),
                    ]);
                } else {
                    $safeForBatching[$acctId] = $w;
                }
            } catch (Exception $e) {
                Log::warning("SyncDealsJob: Could not get deal count for {$acctId}, using adaptive chunking", [
                    'error' => $e->getMessage(),
                ]);
                $highRisk[$acctId] = $w;
            }
        }

        // ── 1. Batch sync all safe accounts (fast path) ──
        if (!empty($safeForBatching)) {
            Log::info("SyncDealsJob: Batch syncing safe accounts", [
                'count' => count($safeForBatching),
                'total_accounts' => count($accountWindows),
            ]);

            $batchResult = $this->batchSyncSafeAccounts($restService, $safeForBatching, $existingDealIds);
            $inserted += $batchResult['inserted'];
            $closedPositions = array_merge($closedPositions, $batchResult['closed_positions']);
            $failedAccountIds = array_merge($failedAccountIds, $batchResult['failed_account_ids']);
        }

        // ── 2. Individually sync high-risk accounts with adaptive chunking (reliable path) ──
        if (!empty($highRisk)) {
            Log::info("SyncDealsJob: Adaptive chunking high-risk accounts", [
                'count' => count($highRisk),
            ]);

            foreach ($highRisk as $acctId => $w) {
                try {
                    $result = $this->syncHighRiskAccountWithAdaptiveChunking($restService, $acctId, $w, $existingDealIds);
                    $inserted += $result['inserted'];
                    $closedPositions = array_merge($closedPositions, $result['closed_positions']);

//                    if (count($closedPositions) >= 100) {
                        ProcessClosedDealCommissionJob::dispatch(array_splice($closedPositions, 0, 100))
                            ->onQueue('distributeibcommission');
//                    }
                } catch (Exception $e) {
                    Log::error("SyncDealsJob: High-risk sync failed for {$acctId}", [
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
     * Fast path: batch sync multiple safe accounts in one/few REST calls.
     */
    protected function batchSyncSafeAccounts(
        MT5RestAPIService $restService,
        array $safeAccounts,
        array &$existingDealIds
    ): array {
        $inserted = 0;
        $closedPositions = [];
        $failedAccountIds = [];

        // Build global time window across all safe accounts
        $globalFrom = PHP_INT_MAX;
        $globalTo = 0;
        $logins = [];

        foreach ($safeAccounts as $acctId => $w) {
            $globalFrom = min($globalFrom, $w['from']);
            $globalTo = max($globalTo, $w['to']);
            $logins[] = $w['login'];
        }

        Log::info("SyncDealsJob: Batch fetch window", [
            'from' => Carbon::createFromTimestamp($globalFrom)->toDateTimeString(),
            'to' => Carbon::createFromTimestamp($globalTo)->toDateTimeString(),
            'account_count' => count($safeAccounts),
        ]);

        try {
            $batchResult = $restService->getBatchDeals($logins, $globalFrom, $globalTo);
            $dealsByLogin = $batchResult['deals'];
            Log::info("SyncDealsJob: Batch REST call completed", $batchResult);
            // Process deals per account
            foreach ($safeAccounts as $acctId => $w) {
                $login = (string) $w['login'];
                $account = $w['account'];
                $rawDeals = $dealsByLogin[$login] ?? null;

                if ($rawDeals === null) {
                    // No deals returned, but this is NOT a failure
                    // It just means there are no trades in this range
                    // Still need to update sync range to mark this period as checked
                    Log::info("SyncDealsJob: No deals found for {$acctId} in batch (normal, not an error)", [
                        'login' => $login,
                    ]);

                    // Treat as successful with empty deals list
                    $result = $this->processRestDeals([], $acctId, $account, $w['from'], $existingDealIds, true, $w['to']);
                    $inserted += $result['inserted'];
                    $closedPositions = array_merge($closedPositions, $result['closed_positions']);

                    $this->syncTrades($acctId, $account, $result['new_deal_ids']);

                    ProcessClosedDealCommissionJob::dispatch(array_splice($closedPositions, 0, 100))
                        ->onQueue('distributeibcommission');
                    continue;
                }

                // Pass the window end so sync range advances even with sparse deals
                $result = $this->processRestDeals($rawDeals, $acctId, $account, $w['from'], $existingDealIds, true, $w['to']);
                $inserted += $result['inserted'];
                $closedPositions = array_merge($closedPositions, $result['closed_positions']);

                $this->syncTrades($acctId, $account, $result['new_deal_ids']);

                ProcessClosedDealCommissionJob::dispatch(array_splice($closedPositions, 0, 100))
                        ->onQueue('distributeibcommission');
            }
        } catch (Exception $e) {
            Log::error("SyncDealsJob: Batch REST call failed", [
                'error' => $e->getMessage(),
                'account_count' => count($safeAccounts),
            ]);
            $failedAccountIds = array_keys($safeAccounts);
        }

        return [
            'inserted' => $inserted,
            'closed_positions' => $closedPositions,
            'failed_account_ids' => $failedAccountIds,
        ];
    }

    /**
     * Reliable path: sync high-risk account individually with adaptive chunking.
     */
    protected function syncHighRiskAccountWithAdaptiveChunking(
        MT5RestAPIService $restService,
        string $accountId,
        array $window,
        array &$existingDealIds
    ): array {
        $login = $window['login'];
        $account = $window['account'];
        $from = $window['from'];
        $to = $window['to'];

        // Get total deal count for chunking strategy
        $totals = $restService->getDealTotals([$login], $from, $to);
        $totalDeals = $totals[(string) $login] ?? 0;

        Log::info("SyncDealsJob: High-risk account sync started", [
            'account_id' => $accountId,
            'login' => $login,
            'total_deals' => $totalDeals,
            'window_days' => round(($to - $from) / 86400, 1),
        ]);

        if ($totalDeals === 0) {
            $this->processRestDeals([], $accountId, $account, $from, $existingDealIds, true, $to);
            return ['inserted' => 0, 'closed_positions' => [], 'new_deal_ids' => []];
        }

        if ($totalDeals <= self::MAX_DEALS_PER_REST_CALL) {
            // Even risky account might be small enough for single call
            $batchResult = $restService->getBatchDeals([$login], $from, $to);
            $rawDeals = $batchResult['deals'][(string) $login] ?? [];

            if (empty($rawDeals) && $totalDeals > 0) {
                throw new Exception("REST API returned no data for login {$login} (expected {$totalDeals} deals)");
            }

            $result = $this->processRestDeals($rawDeals, $accountId, $account, $from, $existingDealIds, true, $to);
            $this->syncTrades($accountId, $account, $result['new_deal_ids']);
            return $result;
        }

        // ── Use adaptive chunking for this high-risk account ──
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
                // For adaptive chunking, pass the chunk's end as the window end
                $result = $this->processRestDeals($rawDeals, $accountId, $account, $currentFrom, $existingDealIds, $isLastChunk, $currentTo);
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
                // Even if empty, mark complete on last chunk with the window end
                $this->processRestDeals([], $accountId, $account, $currentFrom, $existingDealIds, true, $currentTo);
            }

            $currentFrom = $currentTo;
        }

        // Sync trades once with all new deal IDs from all chunks
        $this->syncTrades($accountId, $account, $allNewDealIds);

        return ['inserted' => $totalInserted, 'closed_positions' => $allClosedPositions, 'new_deal_ids' => $allNewDealIds];
    }

    /**
     * Transform REST deal arrays into DB rows, upsert, and detect closes.
     *
     * Also updates the sync range tracking for this account based on deals processed.
     * Even if no deals are returned, we update the range to mark this interval as synced.
     */
    protected function processRestDeals(array $rawDeals, string $accountId, Account $account, int $accountFrom, array &$existingDealIds, bool $markComplete = true, ?int $syncWindowEnd = null): array
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

        // Update the sync range for this account
        // Use the MAXIMUM of:
        // 1. Latest deal time (if deals exist)
        // 2. The end of the sync window requested (if provided)
        // This ensures we advance the cursor even for ranges with sparse deals
        $syncRangeTo = $latestTimeDone ? $latestTimeDone->timestamp : null;

        if ($syncWindowEnd !== null && ($syncRangeTo === null || $syncWindowEnd > $syncRangeTo)) {
            // Use the window end if it's later than the latest deal, or if no deals exist
            $syncRangeTo = $syncWindowEnd;
        } elseif ($syncRangeTo === null && $markComplete) {
            // Fallback: if no deals and no window end, use current time
            $syncRangeTo = Carbon::now()->timestamp;
        }

        // Validate timestamp before storing
        if ($syncRangeTo !== null && is_numeric($syncRangeTo) && $syncRangeTo > 1704067200) { // Valid if after 2024-01-01
            $this->accountSyncRanges[$accountId]['to'] = $syncRangeTo;
            // Log::debug("SyncDealsJob: Updated sync range 'to'", [
            //     'account_id' => $accountId,
            //     'latest_deal_time' => $latestTimeDone ? $latestTimeDone->timestamp : null,
            //     'sync_window_end' => $syncWindowEnd,
            //     'final_to' => $syncRangeTo,
            //     'final_to_readable' => Carbon::createFromTimestamp($syncRangeTo)->toDateTimeString(),
            // ]);
        } else {
            Log::warning("SyncDealsJob: Invalid sync range 'to' value, not updating", [
                'account_id' => $accountId,
                'sync_range_to' => $syncRangeTo,
                'latest_deal_time' => $latestTimeDone ? $latestTimeDone->timestamp : null,
                'sync_window_end' => $syncWindowEnd,
            ]);
        }

        // Only update last_fetch_at; cursors (deals_synced_from/to) will be updated after full sync success
        $syncUpdate = [
            'deals_last_fetch_at' => now(),
        ];
        Account::where('id', $accountId)->update($syncUpdate);
        Cache::forget("account:{$accountId}");

        return [
            'inserted' => count($dealsToInsert),
            'closed_positions' => $closedPositions,
            'new_deal_ids' => $newDealIds,
            'latest_deal_time' => $latestTimeDone,
        ];
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
