<?php

namespace App\Jobs;

use App\Models\Trade;
use App\Models\Account;
use App\Models\Deal;
use App\Jobs\DealSyncJob;
use App\Services\TradeCacheService;
use App\Services\UniversalMT5Service;
use App\MT5\MTRetCode;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Carbon\Carbon;

/**
 * Batch Sync Trades Job - Multiple Accounts per Connection
 *
 * This job processes multiple accounts in a single job to:
 * 1. Reuse MT5 connection across accounts (major performance gain)
 * 2. Reduce job overhead (fewer queue items)
 * 3. Better resource utilization
 * 4. Maintain reliability with proper error handling per account
 */
class BatchSyncTradesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $accounts;
    protected $fromTimes;
    protected $maxTradesLimit;
    protected $minTradesLimit;
    protected $maxPagesPerSync; // Limit pages fetched per sync to ensure fair queue distribution
    public $timeout = 300; // 5 minutes for batch
    public $tries = 2;
    public $retryAfter = 300; // Wait 5 minutes before retrying to allow MT5 server to recover
    public $uniqueFor = 600; // Prevent duplicates for 10 minutes

    /**
     * Get the unique ID for the job to prevent concurrent executions for same accounts.
     */
    public function uniqueId()
    {
        $accountCodes = collect($this->accounts)->pluck('code')->sort()->join('-');
        return "batch-sync-trades-{$accountCodes}";
    }

    public function __construct(array $accounts, array $fromTimes = [], int $maxTradesLimit = null, int $minTradesLimit = null, int $maxPagesPerSync = null)
    {
        // Convert Account models or arrays to serializable array format
        $this->accounts = collect($accounts)->map(function ($account) {
            // Handle both Account models and arrays
            if (is_array($account)) {
                return [
                    'id' => $account['id'],
                    'code' => $account['code'],
                    'demo' => $account['demo'] ?? false,
                    'last_balance_sync_at' => $account['last_balance_sync_at'] ?? null,
                    'last_trade_at' => $account['last_trade_at'] ?? null,
                ];
            } else {
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'demo' => $account->demo,
                    'last_balance_sync_at' => $account->last_balance_sync_at,
                    'last_trade_at' => $account->last_trade_at,
                ];
            }
        })->toArray();

        $this->fromTimes = $fromTimes;
        $this->maxTradesLimit = $maxTradesLimit;
        $this->minTradesLimit = $minTradesLimit;
        $this->maxPagesPerSync = $maxPagesPerSync ?? config('sync-all-trades.batch_sync.max_pages_per_sync', 20);

        // Set timeout based on number of accounts with optimized timing
        // Base: 5 minutes, then 60 seconds per account (reduced from 90) + 2 minute buffer (reduced from 5)
        $this->timeout = max(300, count($accounts) * 60 + 120);
    }

    public function handle(UniversalMT5Service $mt5Service, TradeCacheService $cacheService)
    {
        $jobStartTime = microtime(true);
        $accountCodes = collect($this->accounts)->pluck('code')->join(', ');
        $accountCount = count($this->accounts);
        $startMemory = memory_get_usage(true);
        $currentAccountCode = null;
        $currentPhase = 'initialization';
        $partialSyncAccounts = []; // Track accounts that need re-queueing due to page limits

        // Additional duplicate protection using manual cache locks
        $lockKey = "batch-sync-lock-" . collect($this->accounts)->pluck('code')->sort()->join('-');
        $lock = Cache::lock($lockKey, 600); // 10 minute lock

        if (!$lock->get()) {
            Log::warning("BatchSyncTradesJob skipped - another instance is already running for accounts: {$accountCodes}");
            return;
        }

        try {
            Log::info("Starting BatchSyncTradesJob for {$accountCount} accounts: {$accountCodes} (Memory: " . round($startMemory / 1024 / 1024, 2) . "MB)");

            $startTime = now();
            $results = [
                'processed' => 0,
                'success' => 0,
                'errors' => 0,
                'no_changes' => 0,
                'not_found' => 0,
                'skipped' => 0
            ];

            $connectionTime = 0;
            $accountTimings = [];

            try {
                // Track MT5 connection time
                $currentPhase = 'mt5_connection';
                $connectionStart = microtime(true);
                if (!$mt5Service->connect()) {
                    throw new \Exception("Failed to establish MT5 connection (via pool)");
                }
                $connectionTime = round((microtime(true) - $connectionStart) * 1000, 2);
                $api = $mt5Service->getApi();

                // Pre-warm cache for all accounts in this batch
                $currentPhase = 'cache_warmup';
                $accountModels = collect($this->accounts)->map(fn($acc) => Account::find($acc['id']))->filter();
                $cacheService->warmupAccounts($accountModels->all());

                foreach ($this->accounts as $index => $accountData) {
                    $accountIterationStart = microtime(true);
                    $currentAccountCode = $accountData['code'];
                    $currentPhase = "account_sync:{$currentAccountCode}";

                    try {
                        // Convert array back to Account model for processing
                        $account = Account::find($accountData['id']);
                        if (!$account) {
                            Log::warning("Account {$accountData['code']} not found in database");
                            $results['not_found']++;
                            $results['processed']++;
                            continue;
                        }

                        $fromTime = $this->fromTimes[$index] ?? now()->subDays(7);
                        $result = $this->syncSingleAccount($api, $account, $fromTime, $cacheService);

                        // Map the result status to the correct results array key
                        switch ($result) {
                            case 'error':
                                $results['errors']++;
                                break;
                            case 'success':
                                $results['success']++;
                                break;
                            case 'no_changes':
                                $results['no_changes']++;
                                break;
                            case 'not_found':
                                $results['not_found']++;
                                break;
                            case 'partial_sync':
                                // Account hit page limit - will be re-queued automatically
                                if (!isset($results['partial_sync'])) {
                                    $results['partial_sync'] = 0;
                                }
                                $results['partial_sync']++;
                                $partialSyncAccounts[] = $accountData; // Store for re-queueing
                                break;
                            case 'skipped_high_volume':
                            case 'skipped_low_volume':
                                // Track skipped accounts separately but don't count as errors
                                if (!isset($results['skipped'])) {
                                    $results['skipped'] = 0;
                                }
                                $results['skipped']++;
                                break;
                            default:
                                Log::warning("Unknown sync result status: {$result} for account {$account->code}");
                                $results['errors']++;
                                break;
                        }
                        $results['processed']++;

                        $accountTime = round((microtime(true) - $accountIterationStart) * 1000, 2);
                        $accountTimings[] = ['account' => $account->code, 'time' => $accountTime, 'result' => $result];

                        Log::debug("Account {$account->code}: {$result} ({$accountTime}ms)");
                    } catch (\Throwable $e) {
                        $results['errors']++;
                        $results['processed']++;
                        $accountTime = round((microtime(true) - $accountIterationStart) * 1000, 2);
                        $accountTimings[] = ['account' => $accountData['code'], 'time' => $accountTime, 'result' => 'error'];

                        Log::error("CRITICAL ERROR: Error while syncing account {$accountData['code']}", [
                            'exception_class' => get_class($e),
                            'exception_message' => $e->getMessage(),
                            'exception_code' => $e->getCode(),
                            'exception_file' => $e->getFile(),
                            'exception_line' => $e->getLine(),
                            'stack_trace' => $e->getTraceAsString(),
                            'account_code' => $accountData['code'],
                            'account_id' => $accountData['id'],
                            'job_attempt' => $this->attempts(),
                            'job_id' => $this->job->getJobId() ?? 'unknown'
                        ]);

                        // Report error to connection pool for adaptive management
                        $mt5Service->reportError();
                    }

                    // Small delay between accounts to avoid overwhelming MT5
                    if ($index < count($this->accounts) - 1) {
                        usleep(100000); // 0.1 second - optimized for better throughput
                    }
                }
            } catch (\Throwable $e) {
                Log::error("CRITICAL EXCEPTION in BatchSyncTradesJob connection/iteration phase", [
                    'exception_class' => get_class($e),
                    'exception_message' => $e->getMessage(),
                    'exception_code' => $e->getCode(),
                    'exception_file' => $e->getFile(),
                    'exception_line' => $e->getLine(),
                    'current_phase' => $currentPhase,
                    'current_account' => $currentAccountCode,
                    'accounts' => collect($this->accounts)->pluck('code')->toArray(),
                    'job_attempt' => $this->attempts(),
                    'job_id' => $this->job->getJobId() ?? 'unknown',
                    'stack_trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

            $duration = $startTime->diffInSeconds(now());
            $totalJobTime = round((microtime(true) - $jobStartTime) * 1000, 2);
            $avgPerAccount = round($duration / $accountCount, 2);
            $avgPerAccountMs = round($totalJobTime / $accountCount, 2);
            $endMemory = memory_get_usage(true);
            $memoryUsed = round(($endMemory - $startMemory) / 1024 / 1024, 2);
            $peakMemory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

            // Calculate statistics
            $accountTimes = array_column($accountTimings, 'time');
            $minTime = !empty($accountTimes) ? min($accountTimes) : 0;
            $maxTime = !empty($accountTimes) ? max($accountTimes) : 0;
            $medianTime = !empty($accountTimes) ? $this->calculateMedian($accountTimes) : 0;

            // Detailed performance breakdown
            $performanceReport = [
                'job_total_ms' => $totalJobTime,
                'connection_ms' => $connectionTime,
                'avg_per_account_ms' => $avgPerAccountMs,
                'min_account_ms' => $minTime,
                'max_account_ms' => $maxTime,
                'median_account_ms' => $medianTime,
                'memory_used_mb' => $memoryUsed,
                'peak_memory_mb' => $peakMemory,
                'account_breakdown' => $accountTimings
            ];

            Log::info("BatchSyncTradesJob COMPLETED - Summary Report", [
                'total_accounts' => $accountCount,
                'processed' => $results['processed'],
                'successful' => $results['success'],
                'no_changes' => $results['no_changes'],
                'errors' => $results['errors'],
                'not_found' => $results['not_found'],
                'skipped' => $results['skipped'],
                'total_time_ms' => $totalJobTime,
                'connection_time_ms' => $connectionTime,
                'avg_per_account_ms' => $avgPerAccountMs,
                'min_time_ms' => $minTime,
                'max_time_ms' => $maxTime,
                'median_time_ms' => $medianTime,
                'memory_used_mb' => $memoryUsed,
                'peak_memory_mb' => $peakMemory,
                'accounts' => collect($this->accounts)->pluck('code')->toArray(),
                'job_id' => $this->job->getJobId() ?? 'unknown',
                'attempt' => $this->attempts()
            ]);

            // AUTO RE-QUEUE: Handle accounts that hit page limits during pagination
            // This ensures fair distribution - high-volume accounts don't block others
            if (!empty($partialSyncAccounts)) {
                Log::info("AUTO_REQUEUE: " . count($partialSyncAccounts) . " account(s) hit page limits and will be re-queued for continuation");
                foreach ($partialSyncAccounts as $partialAccount) {
                    Log::info("REQUEUE: Account {$partialAccount['code']} (ID: {$partialAccount['id']}) - page limit reached, re-queueing for next sync");

                    // Re-dispatch the same account for continuation
                    // It will calculate fresh sync range based on last successfully synced trades
                    $retryJob = new BatchSyncTradesJob(
                        [$partialAccount],
                        [],
                        $this->maxTradesLimit,
                        $this->minTradesLimit,
                        $this->maxPagesPerSync
                    );
                    dispatch($retryJob);
                }
                Log::info("AUTO_REQUEUE: Dispatched " . count($partialSyncAccounts) . " continuation jobs");
            }

            // Clear sync-in-progress cache for all accounts in this batch
            $this->clearBatchSyncInProgressCache();
        } catch (\Exception $e) {
            Log::error("FATAL ERROR: Unexpected exception in BatchSyncTradesJob main try block", [
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'exception_code' => $e->getCode(),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
                'current_phase' => $currentPhase,
                'current_account' => $currentAccountCode,
                'accounts' => collect($this->accounts)->pluck('code')->toArray(),
                'job_attempt' => $this->attempts(),
                'job_id' => $this->job->getJobId() ?? 'unknown',
                'stack_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        } finally {
            // Always release the lock
            $lock->release();
        }
    }

    private function calculateMedian(array $values): float
    {
        sort($values);
        $count = count($values);
        $middle = floor(($count - 1) / 2);

        if ($count % 2) {
            return $values[$middle];
        } else {
            return ($values[$middle] + $values[$middle + 1]) / 2;
        }
    }

    /**
     * IMPROVED SYNC STRATEGY: Calculate optimal sync time range based on database state
     * 
     * Instead of always syncing 7+ days of data, intelligently determine:
     * - What trades we already have
     * - What new dates we need to check
     * - How far back to look based on account activity patterns
     * - IMPORTANT: Check for open positions that need continuous monitoring
     * 
     * Returns: ['from' => Carbon, 'strategy' => string]
     */
    protected function getSmartSyncTimeRange(Account $account, Carbon $fallbackFromTime): array
    {
        try {
            // CRITICAL: Check for open positions (trades still running)
            // These need to be synced regardless of when they were opened
            $openPositions = Trade::where('account_id', $account->id)
                ->whereNull('close_time')
                ->select('open_time', 'position_id', 'symbol')
                ->orderBy('open_time')
                ->get();

            if ($openPositions->count() > 0) {
                // Account has open positions - MUST sync to check their current status
                $oldestOpenTrade = $openPositions->first();
                $oldestOpenTime = Carbon::parse($oldestOpenTrade->open_time);
                $hoursSinceOldestOpen = now()->diffInHours($oldestOpenTime);
                $daysSinceOldestOpen = now()->diffInDays($oldestOpenTime);

                Log::info("DEBUG[{$account->code}]: Found {$openPositions->count()} OPEN positions. Oldest opened {$daysSinceOldestOpen} days ago ({$oldestOpenTrade->symbol}). Must sync to update status.");

                // For open trades, always sync back to at least when the oldest one opened
                // Plus buffer time to catch any related activity
                $bufferHours = min($hoursSinceOldestOpen + 24, 168); // Max 7 days buffer
                $syncFromTime = $oldestOpenTime->subHours(2);

                return [
                    'from' => $syncFromTime,
                    'strategy' => 'OPEN_POSITION_SYNC (' . $openPositions->count() . ' active positions, oldest: ' . $daysSinceOldestOpen . ' days)'
                ];
            }

            // Get the latest CLOSED trade in our database for this account
            $latestTrade = Trade::where('account_id', $account->id)
                ->whereNotNull('close_time')
                ->orderByDesc('close_time')
                ->select('open_time', 'close_time', 'created_at')
                ->first();

            if (!$latestTrade) {
                // No trades in database - do full sync from fallback time
                return [
                    'from' => $fallbackFromTime,
                    'strategy' => 'FULL_INITIAL_SYNC (no history)'
                ];
            }

            // Calculate how long ago the latest CLOSED trade was
            $latestTradeTime = Carbon::parse($latestTrade->close_time);
            $hoursSinceLastTrade = now()->diffInHours($latestTradeTime);
            $daysSinceLastTrade = now()->diffInDays($latestTradeTime);

            // Strategy 1: Very Recent Activity (< 1 hour)
            // Sync last 2 hours to catch any trades in near real-time
            if ($hoursSinceLastTrade <= 1) {
                $syncFromTime = now()->subHours(2);
                return [
                    'from' => $syncFromTime,
                    'strategy' => 'INCREMENTAL_VERY_RECENT (last 2 hours)'
                ];
            }

            // Strategy 2: Recent Activity (< 1 day)
            // Sync last 2 days to catch any new trades
            if ($daysSinceLastTrade <= 1) {
                $syncFromTime = now()->subDays(2);
                return [
                    'from' => $syncFromTime,
                    'strategy' => 'INCREMENTAL_RECENT (last 2 days)'
                ];
            }

            // Strategy 3: Moderately Recent (1-7 days)
            // Sync last 14 days to account for potential delays
            if ($daysSinceLastTrade <= 7) {
                $syncFromTime = now()->subDays(14);
                return [
                    'from' => $syncFromTime,
                    'strategy' => 'INCREMENTAL_MODERATE (last 14 days)'
                ];
            }

            // Strategy 4: Inactive (7-30 days)
            // Only check active hours - might have overnight trades
            if ($daysSinceLastTrade <= 30) {
                // Still sync back 30 days to see if account regains activity
                $syncFromTime = now()->subDays(30);
                return [
                    'from' => $syncFromTime,
                    'strategy' => 'INCREMENTAL_INACTIVE (last 30 days - hoping for activity)'
                ];
            }

            // Strategy 5: Very Inactive (> 30 days)
            // Check just last 24 hours in case account comes back to life
            // This avoids massive 7-day syncs for dormant accounts
            $syncFromTime = now()->subDays(1);
            return [
                'from' => $syncFromTime,
                'strategy' => 'MINIMAL_CHECK_DORMANT (last 24 hours - account inactive ' . $daysSinceLastTrade . ' days)'
            ];
        } catch (\Exception $e) {
            // If anything goes wrong, fall back to the provided time
            Log::warning("Error calculating smart sync time range for {$account->code}: " . $e->getMessage());
            return [
                'from' => $fallbackFromTime,
                'strategy' => 'FALLBACK (error in calculation)'
            ];
        }
    }

    protected function syncSingleAccount($api, Account $account, Carbon $fromTime, TradeCacheService $cacheService): string
    {
        $accountStartTime = microtime(true);
        $timings = [];
        $apiCalls = [];
        $hitPageLimit = false; // Track if we hit pagination limit (for fair queue distribution)

        if (!$account->code) {
            return 'error';
        }

        // Phase 0: IMPROVED SYNC STRATEGY - Calculate optimal sync time range
        // Instead of always syncing from passed $fromTime, use smart incremental sync
        $phaseStart = microtime(true);
        $optimizedFromTime = $this->getSmartSyncTimeRange($account, $fromTime);
        $fromTime = $optimizedFromTime['from'];
        $syncStrategy = $optimizedFromTime['strategy'];
        $timings['sync_range_calculation'] = round((microtime(true) - $phaseStart) * 1000, 2);

        Log::info("SYNC_STRATEGY[{$account->code}]: Using {$syncStrategy} - syncing from {$fromTime->format('Y-m-d H:i:s')}");

        // Phase 1: MT5 User Check
        $phaseStart = microtime(true);
        $mt5_user = null;
        $error_code = $api->UserGet($account->code, $mt5_user);
        $timings['mt5_user_check'] = round((microtime(true) - $phaseStart) * 1000, 2);
        $apiCalls[] = ['UserGet', $timings['mt5_user_check']];

        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::warning("MT5 user not found for account1 {$account->code}");
            $this->updateSyncStatus($account, 'not_found');
            return 'not_found';
        }

        try {
            // NEW CHECK: Look for trades with profit = 0
            $phaseStart = microtime(true);

            $zeroProfitTrades = Trade::where('account_id', $account->id)
                ->where('profit', 0)
                ->whereDoesntHave('deals') // Exclude trades with matching position_id in deals
                ->orderBy('created_at', 'desc')
                ->get();
            // Log::info("zeroProfitTrades ".json_encode($zeroProfitTrades));
            if ($zeroProfitTrades->isNotEmpty()) {
                // Get earliest trade with profit = 0
                $earliestZeroProfitTrade = $zeroProfitTrades->first();
                $syncFromTime = Carbon::parse($earliestZeroProfitTrade->open_time)->subHours(5);
                // Log::info("DEBUG[{$account->code}]: Found {$zeroProfitTrades->count()} trades with zero profit. " .
                //     "Triggering DealSyncJob from {$syncFromTime} (2 hours before earliest zero-profit trade at {$earliestZeroProfitTrade->created_at})");

                // Dispatch deal sync job starting from 2 hours before earliest zero-profit trade
                $dealSyncJob = new DealSyncJob([$account], [$syncFromTime]);
                $dealSyncJob->handle(app(\App\Services\UniversalMT5Service::class), $cacheService);

                // Invalidate cache after deal sync
                $cacheService->invalidateAccountDeals($account);
                $cacheService->invalidateAccount($account);

                // Log::info("DEBUG[{$account->code}]: Deal sync completed for zero-profit trades, proceeding with trade sync");
            }

            // PHASE 1 (MOVED): Check Deal Data Freshness FIRST - Avoid unnecessary MT5 API calls
            $phaseStart = microtime(true);

            $fromDateDb = $fromTime->format('Y-m-d H:i:s'); // For database queries
            $toDateDb = now()->addHours(4)->format('Y-m-d H:i:s'); // For database queries

            // Check if deal data is fresh (based on when we last synced, not deal coverage)
            $isDealDataFresh = $account->isDealDataFresh();

            if (!$isDealDataFresh) {
                // Deal data is stale (we haven't synced recently), need to sync first
                $syncRange = $account->getRequiredDealSyncRange();
                $syncFromTime = $syncRange['from'];
                $syncToTime = $syncRange['to'];

                // Log::info("DEBUG[{$account->code}]: Deal data not recently synced, syncing deals from {$syncFromTime} to {$syncToTime}");

                // Dispatch deal sync job and wait for it to complete

                $dealSyncJob = new DealSyncJob([$account], [$syncFromTime]);
                $dealSyncJob->handle(app(\App\Services\UniversalMT5Service::class), $cacheService);

                // Ensure cache is invalidated after deal sync
                $cacheService->invalidateAccountDeals($account);

                // Log::info("DEBUG[{$account->code}]: Deal sync completed, proceeding with trade sync");
            } else {
                // Log::info("DEBUG[{$account->code}]: Deal data is recently synced (last fetch: {$account->deals_last_fetch_at}), using existing deals");
            }

            // PRIORITY OPTIMIZATION: Check MT5 deal total count vs database count for ENTIRE date range FIRST
            // Log::info("DEBUG[{$account->code}]: Checking MT5 deal total count vs database count for entire requested range to avoid unnecessary processing...");
            $dealTotalStart = microtime(true);
            $mt5DealTotal = 0;
            $fromTimestamp = $fromTime->timestamp; // Unix timestamp for MT5 API
            $toTimestamp = now()->addHours(4)->timestamp; // Unix timestamp for MT5 API
            $fromDateDb = $fromTime->format('Y-m-d H:i:s'); // For database queries (SAME RANGE)
            $toDateDb = now()->addHours(4)->format('Y-m-d H:i:s'); // For database queries (SAME RANGE)

            $error_code = $this->executeWithRetries(function () use ($api, $account, $fromTimestamp, $toTimestamp, &$mt5DealTotal) {
                return $api->DealGetTotal($account->code, $fromTimestamp, $toTimestamp, $mt5DealTotal);
            });
            $dealTotalTime = round((microtime(true) - $dealTotalStart) * 1000, 2);
            $apiCalls[] = ['DealGetTotal', $dealTotalTime];
            if ($error_code == MTRetCode::MT_RET_OK) {
                // Count existing deals in our database for the EXACT SAME RANGE
                $dbDealCount = Deal::where('account_id', $account->id)
                    ->whereBetween('time_done', [$fromDateDb, $toDateDb])
                    ->count();

                // Log::info("DEBUG[{$account->code}]: MT5 deal total: {$mt5DealTotal}, DB deal count: {$dbDealCount} (range: {$fromDateDb} to {$toDateDb}, check took {$dealTotalTime}ms)");

                if ($mt5DealTotal == $dbDealCount) {
                    if ($mt5DealTotal > 0) {
                        // Database is perfectly in sync with MT5 - use database deals!
                        // Log::info("DEBUG[{$account->code}]: Deal counts match perfectly! Using DATABASE OPTIMIZATION - no MT5 processing needed.");

                        // Get existing deals and process them directly
                        $allDeals = Deal::where('account_id', $account->id)
                            ->whereBetween('time_done', [$fromDateDb, $toDateDb])
                            ->get();

                        $result = $this->processDealsBatch($account, $allDeals, $fromTime, $cacheService);

                        $timings['total_processing'] = round((microtime(true) - $accountStartTime) * 1000, 2);
                        // Log::info("PERFORMANCE[{$account->code}]: Completed in {$timings['total_processing']}ms using COMPREHENSIVE DEAL COUNT optimization (avoided ALL MT5 processing!)");

                        return $result;
                    } else {
                        // Both MT5 and DB report 0 deals for this range
                        // Log::info("DEBUG[{$account->code}]: Both MT5 and DB report 0 deals for range. No activity to sync.");

                        $this->updateSyncStatus($account, 'success');
                        $timings['total_processing'] = round((microtime(true) - $accountStartTime) * 1000, 2);
                        // Log::info("PERFORMANCE[{$account->code}]: Completed in {$timings['total_processing']}ms (no deals optimization)");

                        return 'no_changes';
                    }
                } else {
                    // Deal counts differ - need to sync the difference
                    $dealDifference = $mt5DealTotal - $dbDealCount;
                    Log::debug("DEBUG[{$account->code}]: Deal count mismatch! MT5: {$mt5DealTotal}, DB: {$dbDealCount}, Difference: {$dealDifference}. Forcing deal sync first...");

                    // FORCE DEAL SYNC: When deal counts don't match, we must sync deals first
                    // This ensures we have all the missing deals before proceeding with trade sync
                    Log::debug("DEBUG[{$account->code}]: Syncing missing deals due to count mismatch before proceeding with trade sync");

                    // Force deal sync for the same date range to get missing deals
                    $dealSyncJob = new DealSyncJob([$account], [$fromTime]);
                    $dealSyncJob->handle(app(\App\Services\UniversalMT5Service::class), $cacheService);

                    // Invalidate cache after deal sync to ensure fresh data
                    $cacheService->invalidateAccountDeals($account);

                    // Log::info("DEBUG[{$account->code}]: Deal sync completed for mismatch resolution - now proceeding with trade sync");

                    // FORCE FULL MT5 SYNC: Skip all database optimizations when deal counts don't match
                    // This ensures we get the missing deals from MT5 instead of using stale database data
                    Log::debug("DEBUG[{$account->code}]: Skipping database optimizations due to deal count mismatch - proceeding with MT5 API sync");
                    // Continue to MT5 API calls below (skip all the database optimization logic)
                }
            } else {
                Log::warning("DEBUG[{$account->code}]: DealGetTotal failed with error: " . MTRetCode::GetError($error_code) . ". Proceeding with fallback sync...");
            }

            // SMART INCREMENTAL SYNC: Use last known deal time as starting point
            // BUT ONLY if deal counts matched above (no mismatch detected)
            $latestDeal = Deal::where('account_id', $account->id)->latest('time_done')->first();
            $hasDealCountMismatch = ($error_code == MTRetCode::MT_RET_OK && $mt5DealTotal != $dbDealCount);
            $dealDifference = $hasDealCountMismatch ? ($mt5DealTotal - $dbDealCount) : 0;

            if ($latestDeal && !$hasDealCountMismatch) {
                $latestDealTime = Carbon::parse($latestDeal->time_done);
                $daysSinceLastDeal = now()->diffInDays($latestDealTime);

                // If latest deal is BEFORE the requested sync range, account has no activity in requested period
                if ($latestDealTime < $fromTime) {
                    Log::debug("DEBUG[{$account->code}]: Account inactive - latest deal ({$latestDeal->time_done}) is before requested range ({$fromDateDb}). No new activity to sync.");

                    $this->updateSyncStatus($account, 'success');
                    $timings['total_processing'] = round((microtime(true) - $accountStartTime) * 1000, 2);
                    // Log::info("PERFORMANCE[{$account->code}]: Completed in {$timings['total_processing']}ms (no activity optimization)");

                    return 'no_changes';
                }

                // Use incremental sync from last known deal time
                $incrementalFromTime = $latestDealTime;
                $incrementalFromDb = $incrementalFromTime->format('Y-m-d H:i:s');

                Log::info("DEBUG[{$account->code}]: Using INCREMENTAL sync from last deal time: {$incrementalFromDb} (instead of {$fromDateDb})");

                // Check for existing deals in the requested range
                $existingDealsQuery = Deal::where('account_id', $account->id)
                    ->whereBetween('time_done', [$fromDateDb, $toDateDb]);
                $existingDealsCount = $existingDealsQuery->count();

                if ($existingDealsCount > 0) {
                    Log::info("DEBUG[{$account->code}]: Found {$existingDealsCount} existing deals in requested range - USING DATABASE OPTIMIZATION!");

                    // Get all relevant deals and process them directly
                    $allDeals = $existingDealsQuery->get();
                    $timings['deals_fetch'] = round((microtime(true) - $phaseStart) * 1000, 2);

                    // Process deals directly into trades (skip all the MT5 order fetching)
                    $result = $this->processDealsBatch($account, $allDeals, $fromTime, $cacheService);

                    $timings['total_processing'] = round((microtime(true) - $accountStartTime) * 1000, 2);
                    // Log::info("PERFORMANCE[{$account->code}]: Completed in {$timings['total_processing']}ms using DATABASE deal optimization (requested range)");

                    return $result;
                }

                // Check if we need to do optimized MT5 API call for incremental sync (much smaller range)
                if ($daysSinceLastDeal <= 30) {
                    // Account is relatively active, use incremental approach for MT5 API
                    Log::info("DEBUG[{$account->code}]: Account last active {$daysSinceLastDeal} days ago. Using INCREMENTAL MT5 API from {$incrementalFromDb} (reduced range)...");

                    // Override the MT5 API from time to use incremental approach
                    $fromTime = $incrementalFromTime;
                    $fromDate = $incrementalFromTime->format('F d, Y'); // For MT5 API
                    $toDate = now()->addHours(4)->format('F d, Y'); // For MT5 API
                    $fromDateDb = $incrementalFromTime->format('Y-m-d H:i:s'); // For database queries
                    $toDateDb = now()->addHours(4)->format('Y-m-d H:i:s'); // For database queries

                    Log::info("DEBUG[{$account->code}]: OPTIMIZATION: Reduced MT5 API query range from full 7 days to incremental sync since last deal ({$daysSinceLastDeal} days)");
                } else {
                    // Account is inactive for too long, skip expensive API calls
                    // BUT NOT if there's a deal count mismatch - we need to sync the missing data
                    if ($hasDealCountMismatch) {
                        Log::info("DEBUG[{$account->code}]: Account inactive for {$daysSinceLastDeal} days BUT deal count mismatch detected - FORCING full MT5 sync to get missing {$dealDifference} deals");
                        // Continue with full MT5 API sync despite inactivity
                    } else {
                        Log::info("DEBUG[{$account->code}]: Account inactive for {$daysSinceLastDeal} days (last deal: {$incrementalFromDb}). Skipping expensive MT5 API calls.");

                        $this->updateSyncStatus($account, 'success');
                        $timings['total_processing'] = round((microtime(true) - $accountStartTime) * 1000, 2);
                        // Log::info("PERFORMANCE[{$account->code}]: Completed in {$timings['total_processing']}ms (inactive account optimization)");

                        return 'no_changes';
                    }
                }
            }

            $timings['deal_freshness_check'] = round((microtime(true) - $phaseStart) * 1000, 2);

            // FALLBACK: If no deals available, proceed with original MT5 API approach
            // Continue with MT5 API calls for historical data fetching

            // Phase 3: Cached Database Query for Existing Trades
            $phaseStart = microtime(true);
            $existingTrades = $cacheService->getAccountTrades($account);
            $timings['db_existing_trades'] = round((microtime(true) - $phaseStart) * 1000, 2);

            $login = $account->code;
            $fromDate = $fromTime->format('F d, Y'); // For MT5 API
            $toDate = now()->addHours(4)->format('F d, Y'); // For MT5 API
            $fromDateDb = $fromTime->format('Y-m-d H:i:s'); // For database queries
            $toDateDb = now()->addHours(4)->format('Y-m-d H:i:s'); // For database queries
            $total = 0;
            $orders = [];

            // Phase 3: MT5 HistoryGetTotal
            $phaseStart = microtime(true);
            $error_code = $this->executeWithRetries(function () use ($api, $login, $fromDate, $toDate, &$total) {
                return $api->HistoryGetTotal($login, $fromDate, $toDate, $total);
            });
            $timings['mt5_history_total'] = round((microtime(true) - $phaseStart) * 1000, 2);
            $apiCalls[] = ['HistoryGetTotal', $timings['mt5_history_total']];

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 HistoryGetTotal final error for login {$login}: " . MTRetCode::GetError($error_code));
                $this->updateSyncStatus($account, 'error');
                return 'error';
            }

            // Skip if no recent orders
            if ($total == 0) {
                $totalTime = round((microtime(true) - $accountStartTime) * 1000, 2);
                // Log::info("PERF[{$account->code}]: {$totalTime}ms total (no orders) - " . json_encode($timings));
                $this->updateSyncStatus($account, 'no_changes');
                return 'no_changes';
            }

            // TRADE COUNT FILTERING: Skip accounts based on trade count limits
            // Consider both MT5 total and existing database trades for intelligent filtering
            $existingTradesCount = $existingTrades->count();
            $newTradesToProcess = max(0, $total - $existingTradesCount); // Estimate new trades to process
            $totalTradesInSystem = $existingTradesCount; // Current database count

            Log::info("TRADE_ANALYSIS[{$account->code}]: MT5 total: {$total}, DB existing: {$existingTradesCount}, Estimated new: {$newTradesToProcess}");

            // Skip if too many total trades in database (regardless of new trades)
            if ($this->maxTradesLimit !== null && $totalTradesInSystem > $this->maxTradesLimit) {
                $totalTime = round((microtime(true) - $accountStartTime) * 1000, 2);
                Log::info("SKIP[{$account->code}]: {$totalTradesInSystem} existing trades in DB exceeds max limit of {$this->maxTradesLimit} - use high-volume sync");
                $this->updateSyncStatus($account, 'skipped_high_volume', 0, "Too many existing trades ({$totalTradesInSystem}) - use high-volume sync");
                return 'skipped_high_volume';
            }

            // Skip if too many new trades to process (performance consideration)
            if ($this->maxTradesLimit !== null && $newTradesToProcess > ($this->maxTradesLimit * 0.5)) {
                $maxNewTrades = intval($this->maxTradesLimit * 0.5);
                $totalTime = round((microtime(true) - $accountStartTime) * 1000, 2);
                Log::info("SKIP[{$account->code}]: {$newTradesToProcess} new trades exceeds processing limit of {$maxNewTrades} - use high-volume sync");
                $this->updateSyncStatus($account, 'skipped_high_volume', 0, "Too many new trades to process ({$newTradesToProcess}) - use high-volume sync");
                return 'skipped_high_volume';
            }

            if ($this->minTradesLimit !== null && $total < $this->minTradesLimit) {
                $totalTime = round((microtime(true) - $accountStartTime) * 1000, 2);
                Log::info("SKIP[{$account->code}]: {$total} trades below min limit of {$this->minTradesLimit} - use regular sync");
                $this->updateSyncStatus($account, 'skipped_low_volume', 0, "Too few trades ({$total}) - use regular sync");
                return 'skipped_low_volume';
            }

            Log::info("Account {$account->code} has {$total} MT5 orders, {$existingTradesCount} existing DB trades, ~{$newTradesToProcess} new to process" .
                ($this->maxTradesLimit ? " (limit: {$this->maxTradesLimit})" : "") .
                ($this->minTradesLimit ? " (min: {$this->minTradesLimit})" : ""));

            // Phase 4: MT5 HistoryGetPage with Adaptive Pagination
            $phaseStart = microtime(true);
            $orders = [];
            $requestedPageSize = 1000; // What we request
            $actualPageSize = 100; // MT5 server limit (will be detected)
            $totalHistoryTime = 0;
            $pageCount = 0;

            Log::info("DEBUG[{$account->code}]: Starting adaptive pagination for {$total} orders");

            while (count($orders) < $total) {
                $startIndex = count($orders);
                $remainingOrders = $total - $startIndex;
                $pageOrders = [];
                $currentPageSize = min($requestedPageSize, $remainingOrders);

                $pageStart = microtime(true);
                $error_code = $this->executeWithRetries(function () use ($api, $login, $fromDate, $toDate, $startIndex, $currentPageSize, &$pageOrders) {
                    return $api->HistoryGetPage($login, $fromDate, $toDate, $startIndex, $currentPageSize, $pageOrders);
                });
                $pageTime = round((microtime(true) - $pageStart) * 1000, 2);
                $totalHistoryTime += $pageTime;
                $pageCount++;

                if ($error_code != MTRetCode::MT_RET_OK) {
                    Log::error("MT5 HistoryGetPage error for login {$login} page {$pageCount}: " . MTRetCode::GetError($error_code));
                    $this->updateSyncStatus($account, 'error');
                    return 'error';
                }

                // Detect actual page size from first response
                if ($pageCount === 1 && count($pageOrders) < $requestedPageSize) {
                    $actualPageSize = count($pageOrders);
                    Log::info("DEBUG[{$account->code}]: Detected actual page size: {$actualPageSize} (requested: {$requestedPageSize})");
                }

                // Merge page results
                $orders = array_merge($orders, $pageOrders);
                Log::info("DEBUG[{$account->code}]: Page {$pageCount} fetched " . count($pageOrders) . " orders in {$pageTime}ms (total so far: " . count($orders) . "/{$total})");

                // ⚠️ PAGE LIMIT CHECK: Stop if we've reached max pages per sync (fair queue distribution)
                if ($pageCount >= $this->maxPagesPerSync && count($orders) < $total) {
                    $remainingOrders = $total - count($orders);
                    Log::warning("PAGE_LIMIT[{$account->code}]: Reached max pages ({$this->maxPagesPerSync}). Fetched {$pageCount} pages ({$totalHistoryTime}ms). Got " . count($orders) . " orders so far. {$remainingOrders} orders remaining - will be synced in next batch job.");
                    Log::info("FAIRNESS: Stopping pagination for {$account->code} to allow other accounts in batch to be processed. This account will be re-queued.");
                    $hitPageLimit = true; // Mark that we hit the limit
                    // Break early - process what we have and re-queue account for next sync
                    break;
                }

                // Safety check: if we got fewer orders than expected and it's not the last page
                if (count($pageOrders) === 0) {
                    Log::warning("DEBUG[{$account->code}]: Got 0 orders on page {$pageCount}, stopping pagination");
                    break;
                }

                // If we got fewer orders than requested, we might be at the end
                if (count($pageOrders) < $currentPageSize && count($orders) < $total) {
                    Log::warning("DEBUG[{$account->code}]: Got " . count($pageOrders) . " orders (expected {$currentPageSize}), may be at end of data");
                }

                // Small delay between pages to avoid overwhelming MT5
                if (count($orders) < $total) {
                    usleep(50000); // 0.05 second delay between pages
                }
            }

            $timings['mt5_history_page'] = $totalHistoryTime;
            $apiCalls[] = ['HistoryGetPage', $totalHistoryTime];

            Log::info("DEBUG[{$account->code}]: Successfully fetched " . count($orders) . " total orders in {$pageCount} pages ({$totalHistoryTime}ms)");
            // if ($login == 394402) {
            //     Log::info("Orders for account {$account->code}: " . json_encode($orders));
            // }

            // Phase 5: Data Processing - Group Orders into Positions
            $phaseStart = microtime(true);

            Log::info("DEBUG[{$account->code}]: Processing {$total} orders to identify positions");

            // CORRECT APPROACH: Group orders by position to identify complete trades
            // Each position consists of at least 2 orders: open and close

            // First, let's analyze what position identifiers are available
            $ordersWithExpertPositionID = collect($orders)->filter(fn($order) => !empty($order->ExpertPositionID) && $order->ExpertPositionID > 0)->count();
            $ordersWithPositionId = collect($orders)->filter(fn($order) => isset($order->PositionID) && $order->PositionID > 0)->count();
            $ordersWithPositionBy = collect($orders)->filter(fn($order) => isset($order->PositionBy) && $order->PositionBy > 0)->count();

            Log::info("DEBUG[{$account->code}]: Position Analysis - " .
                "ExpertPositionID > 0: {$ordersWithExpertPositionID}, " .
                "PositionID > 0: {$ordersWithPositionId}, " .
                "PositionBy > 0: {$ordersWithPositionBy}");

            // Determine the best position identifier to use
            $positionField = null;
            if ($ordersWithExpertPositionID > 0) {
                $positionField = 'ExpertPositionID';
            } elseif ($ordersWithPositionId > 0) {
                $positionField = 'PositionID';
            } elseif ($ordersWithPositionBy > 0) {
                $positionField = 'PositionBy';
            }

            if ($positionField) {
                // Group orders by position identifier
                $ordersByPosition = collect($orders)
                    ->filter(fn($order) => isset($order->$positionField) && $order->$positionField > 0)
                    ->groupBy(fn($order) => $order->$positionField);

                // Log::info("DEBUG[{$account->code}]: Using {$positionField} for grouping. Found {$ordersByPosition->count()} positions");
            } else {
                // Fallback: Create artificial positions by grouping similar orders
                // Group by Symbol + Volume + approximate time window
                $ordersByPosition = collect($orders)
                    ->filter(fn($order) => !empty($order->Symbol))
                    ->groupBy(function ($order) {
                        // Create artificial position ID based on symbol, volume, and time window
                        $timeWindow = floor($order->TimeDone / 3600) * 3600; // 1-hour windows
                        return $order->Symbol . '_' . $order->VolumeInitial . '_' . $timeWindow;
                    });

                Log::info("DEBUG[{$account->code}]: No position IDs found. Using fallback grouping. Created {$ordersByPosition->count()} artificial positions");
            }

            $tradesToUpsert = [];
            $savedCount = 0;
            $allDeals = []; // Initialize for fallback MT5 API path (no deals optimization here)
            $timings['orders_processing'] = round((microtime(true) - $phaseStart) * 1000, 2);

            // Phase 8: Trade Data Preparation - Process Positions (complete trades)
            $phaseStart = microtime(true);
            $batchProcessingTime = 0;
            $skippedTradesCount = 0; // Track skipped trades with invalid position_id

            foreach ($ordersByPosition as $positionId => $positionOrders) {
                // Ensure $positionOrders is a collection for method calls  
                if (!($positionOrders instanceof \Illuminate\Support\Collection)) {
                    $positionOrders = collect($positionOrders);
                }

                // Sort orders by time to identify open/close sequence
                $positionOrders = $positionOrders->sortBy('TimeDone');
                $existingTrade = $existingTrades->get($positionId);

                // Filter deals for this specific position - improved matching
                $filteredDeals = array_values(array_filter($allDeals, function ($deal) use ($positionOrders) {
                    // Match deals to any order in this position
                    foreach ($positionOrders as $order) {
                        if ($deal->Order == $order->Order) {
                            return true;
                        }
                    }
                    return false;
                }));

                // Calculate actual profit from deals if available
                $actualProfit = null; // Use null to indicate "no deals found"
                $actualSwap = null;
                $actualCommission = null;
                $rateProfit = 1;
                if (!empty($filteredDeals)) {
                    // Sum actual profit from all deals in this position
                    $actualProfit = array_sum(array_map(function ($deal) {
                        return $deal->profit ?? 0;
                    }, $filteredDeals));
                    $actualSwap = array_sum(array_map(function ($deal) {
                        return $deal->swap ?? 0;
                    }, $filteredDeals));
                    $actualCommission = array_sum(array_map(function ($deal) {
                        return $deal->commission ?? 0;
                    }, $filteredDeals));
                    $rateProfit = $filteredDeals[0]->rate_profit ?? 1;


                    // Log::info("DEBUG[{$account->code}]: Position {$positionId} found {count($filteredDeals)} deals, actual profit: {$actualProfit}");
                } else {
                    // No deals found through order matching - check database directly
                    $dbDeals = Deal::where('account_id', $account->id)
                        ->where('position_id', $positionId)
                        ->get();

                    if ($dbDeals->count() > 0) {
                        $actualProfit = round($dbDeals->sum('profit'), 2);
                        $rateProfit = $dbDeals->first()->rate_profit ?? 1;
                        $actualSwap = round($dbDeals->sum('swap'), 2);
                        $actualCommission = round($dbDeals->sum('commission'), 2);
                        // Log::info("DEBUG[{$account->code}]: Position {$positionId} found {$dbDeals->count()} deals via database lookup, actual profit: {$actualProfit}");
                    } else {
                        // Log::warning("DEBUG[{$account->code}]: Position {$positionId} has no deals - will use manual calculation");
                    }
                }

                // Log::info("DEBUG[{$account->code}]: Position {$positionId} has {$positionOrders->count()} orders, " . count($filteredDeals) . " deals" .
                //     (count($filteredDeals) > 0 ? ", actual profit: {$actualProfit}" : ""));

                if ($positionOrders->count() == 1) {
                    // Single order = Open position (no close yet)
                    if (!$existingTrade) {
                        $tradeData = $this->prepareOpenTrade($account, $positionId, $positionOrders->first(), $actualSwap, $actualCommission);
                        if ($tradeData !== null) {
                            $tradesToUpsert[] = $tradeData;
                            $savedCount++;
                        } else {
                            $skippedTradesCount++;
                        }
                    }
                } elseif ($positionOrders->count() >= 2) {
                    // Multiple orders = Closed position (open + close)
                    $openOrder = $positionOrders->first();  // First order = open
                    $closeOrder = $positionOrders->last();  // Last order = close

                    $closedTradeData = $this->prepareClosedTrade($account, $positionId, $openOrder, $closeOrder, $actualProfit, $rateProfit, $actualSwap, $actualCommission);
                    if ($closedTradeData !== null) {
                        if ($existingTrade) {
                            $closedTradeData['id'] = $existingTrade->id; // Update existing
                        }
                        $tradesToUpsert[] = $closedTradeData;
                        $savedCount++;
                    } else {
                        $skippedTradesCount++;
                    }
                }

                // Dynamic batch size based on account complexity
                $batchSize = count($this->accounts) > 5 ? 100 : 50; // Larger batches for bigger jobs

                if (count($tradesToUpsert) >= $batchSize) { // Process in dynamic batches
                    $batchStart = microtime(true);
                    $this->processBatch($tradesToUpsert);
                    $batchProcessingTime += round((microtime(true) - $batchStart) * 1000, 2);
                    $tradesToUpsert = [];
                }
            }
            $timings['trade_preparation'] = round((microtime(true) - $phaseStart) * 1000, 2);

            // Phase 9: Final Batch Processing
            $phaseStart = microtime(true);
            if (!empty($tradesToUpsert)) {
                $this->processBatch($tradesToUpsert);
            }
            $timings['final_batch'] = round((microtime(true) - $phaseStart) * 1000, 2);
            $timings['total_batch_processing'] = $batchProcessingTime + $timings['final_batch'];

            // Phase 10: Update Account Status & Cache Invalidation
            $phaseStart = microtime(true);

            // If we hit page limit, mark as partial sync instead of complete success
            if ($hitPageLimit) {
                Log::info("PARTIAL_SYNC[{$account->code}]: Sync incomplete due to page limit. {$savedCount} trades processed but more remain. Account will be re-queued automatically.");
                $this->updateSyncStatus($account, 'partial_sync', $savedCount, "Partial sync - page limit reached. Remaining data will be synced in next batch.");
            } else {
                $this->updateSyncStatus($account, 'success', $savedCount);
            }

            // Invalidate cache since we've updated trades
            if ($savedCount > 0) {
                $cacheService->invalidateAccount($account);
            }

            $timings['status_update'] = round((microtime(true) - $phaseStart) * 1000, 2);

            // Final Performance Summary
            $totalTime = round((microtime(true) - $accountStartTime) * 1000, 2);
            $totalApiTime = array_sum(array_column($apiCalls, 1));
            $totalDbTime = $timings['db_existing_trades'] + $timings['deal_freshness_check'] + $timings['total_batch_processing'] + $timings['status_update'];

            Log::info("PERF[{$account->code}]: {$totalTime}ms total | " .
                "API: {$totalApiTime}ms (" . count($apiCalls) . " calls) | " .
                "DB: {$totalDbTime}ms (deals: {$timings['deal_freshness_check']}ms) | " .
                "Processing: {$timings['trade_preparation']}ms | " .
                "Orders: {$total}, Positions: " . $ordersByPosition->count() . ", Trades: {$savedCount}" .
                ($skippedTradesCount > 0 ? ", Skipped: {$skippedTradesCount}" : "") . " | " .
                "Breakdown: " . json_encode($timings) . " | " .
                "API Calls: " . json_encode($apiCalls));

            return $hitPageLimit ? 'partial_sync' : 'success';
        } catch (\Exception $e) {
            $totalTime = round((microtime(true) - $accountStartTime) * 1000, 2);
            Log::error("SYNC ERROR for account {$account->code} after {$totalTime}ms", [
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'exception_code' => $e->getCode(),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
                'account_code' => $account->code,
                'account_id' => $account->id,
                'account_demo' => $account->demo,
                'total_time_ms' => $totalTime,
                'job_attempt' => $this->attempts(),
                'job_id' => $this->job->getJobId() ?? 'unknown',
                'stack_trace' => $e->getTraceAsString()
            ]);
            $this->updateSyncStatus($account, 'error');
            return 'error';
        }
    }

    protected function executeWithRetries($callback)
    {
        $maxAttempts = 3;
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxAttempts) {
            try {
                $attempt++;
                return $callback();
            } catch (\Exception $e) {
                $lastException = $e;
                
                // Check if it's a socket/connection error that should be retried
                $isSocketError = stripos($e->getMessage(), 'broken pipe') !== false ||
                                stripos($e->getMessage(), 'connection reset') !== false ||
                                stripos($e->getMessage(), 'connection refused') !== false ||
                                stripos($e->getMessage(), 'unable to write to socket') !== false ||
                                stripos($e->getMessage(), 'connection timed out') !== false ||
                                stripos($e->getMessage(), 'transport endpoint') !== false;
                
                if (!$isSocketError || $attempt >= $maxAttempts) {
                    throw $e;
                }

                // Log the retry attempt
                Log::warning("Socket/Connection error detected, retrying (attempt {$attempt}/{$maxAttempts})", [
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                ]);

                // Exponential backoff: 1s, 2s, 4s
                $sleepSeconds = 1 * (2 ** ($attempt - 1));
                sleep($sleepSeconds);
            }
        }

        if ($lastException) {
            throw $lastException;
        }
    }

    protected function prepareOpenTrade($account, $positionId, $order)
    {
        // CRITICAL: Validate position_id before creating trade data
        if (empty($positionId) || $positionId == 0 || $positionId === '0') {
            $this->logInvalidPositionId('open', $account, $positionId, $order, [
                'order_data' => $order,
                'account_id' => $account->id,
                'account_code' => $account->code
            ]);

            // Return null to skip this trade but continue with others
            Log::warning("Skipping open trade with invalid position_id: {$positionId} for account {$account->code}");
            return null;
        }

        // Log::info("account code  ".$account->code);
        // Log::info("order  ".json_encode($order));
        // Log::info("order id  ".$order->Order);
        // Log::info("trade type  ".$order->Type);
        return [
            'account_id' => $account->id,
            'close_price' => null,
            'close_time' => null,
            'code' => $account->code,
            'comment' => $order->Comment ?? '',
            'commission' => 0,
            'created_at' => now(),
            'open_price' => $order->PriceCurrent,
            'open_time' => date('Y-m-d H:i:s', $order->TimeDone),
            'order_id' => $order->Order,
            'position_id' => $positionId,
            'profit' => 0,
            'sl' => $order->PriceSL,
            'state' => $order->State,
            'status' => 'open',
            'swap' => 0,
            'symbol' => $order->Symbol,
            'tp' => $order->PriceTP,
            'type' => $order->Type ? 'sell' : 'buy',
            'updated_at' => now(),
            'volume' => $order->VolumeInitial / 10000,
            'volume_ext' => $order->VolumeInitialExt,
        ];
    }

    protected function prepareClosedTradeFromOrder($account, $positionId, $order, $deal)
    {
        // CRITICAL: Validate position_id before creating trade data
        if (empty($positionId) || $positionId == 0 || $positionId === '0') {
            $this->logInvalidPositionId('closed', $account, $positionId, $order, [
                'order_data' => $order,
                'deal_data' => $deal,
                'account_id' => $account->id,
                'account_code' => $account->code
            ]);

            // Return null to skip this trade but continue with others
            Log::warning("Skipping closed trade with invalid position_id: {$positionId} for account {$account->code}");
            return null;
        }

        // Calculate profit based on deal information
        $profit = $deal->profit ?? 0;

        return [
            'account_id' => $account->id,
            'position_id' => $positionId,
            'order_id' => $order->Order,
            'symbol' => $order->Symbol,
            'type' => $order->Type ? 'sell' : 'buy',
            'volume' => $order->VolumeInitial / 10000,
            'volume_ext' => $order->VolumeInitialExt,
            'open_price' => $order->PriceCurrent,
            'close_price' => $deal->Price ?? $order->PriceCurrent,
            'sl' => $order->PriceSL,
            'tp' => $order->PriceTP,
            'open_time' => date('Y-m-d H:i:s', $order->TimeDone),
            'close_time' => date('Y-m-d H:i:s', $deal->Time ?? $order->TimeDone),
            'state' => $order->State,
            'comment' => $order->Comment,
            'profit' => $profit,
            'status' => 'closed',
            'code' => $account->code,
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }

    protected function prepareClosedTrade($account, $positionId, $openOrder, $closeOrder, $actualProfit = null, $rateProfit = 1, $actualSwap = null, $actualCommission = null)
    {
        // CRITICAL: Validate position_id before creating trade data
        if (empty($positionId) || $positionId == 0 || $positionId === '0') {
            $this->logInvalidPositionId('closed', $account, $positionId, $closeOrder, [
                'open_order_data' => $openOrder,
                'close_order_data' => $closeOrder,
                'rate_profit' => $rateProfit,
                'account_id' => $account->id,
                'account_code' => $account->code
            ]);

            // Return null to skip this trade but continue with others
            Log::warning("Skipping closed trade with invalid position_id: {$positionId} for account {$account->code}");
            return null;
        }

        // Use actual profit from deals if available, otherwise calculate manually
        $profit = 0;
        if ($actualProfit !== null) {
            // Use real profit from MT5 deals (preferred method)
            $profit = round($actualProfit, 2);
            // Log::info("DEBUG[{$account->code}]: Position {$positionId} using actual profit: {$profit}");
        } else {
            // Fallback: Calculate profit manually (less accurate)
            $multiplier = $openOrder->Type ? -1 : 1;
            $priceDiff = $closeOrder->PriceCurrent - $openOrder->PriceCurrent;
            $volumeInLots = $openOrder->VolumeInitialExt / 100000000;
            $contractSize = $openOrder->ContractSize ?? 100000;

            $profit = round($priceDiff * $volumeInLots * $contractSize * $rateProfit * $multiplier, 2);
            Log::warning("DEBUG[{$account->code}]: Position {$positionId} using calculated profit: {$profit} (no deals available)");
            // Log calculation details for troubleshooting
            Log::warning("DEBUG[{$account->code}]: Manual calculation details line 956 - " .
                "Type: " . ($openOrder->Type ? 'sell' : 'buy') . ", " .
                "PriceDiff: {$priceDiff}, " .
                "VolumeLots: {$volumeInLots}, " .
                "ContractSize: {$contractSize}, " .
                "RateProfit: {$rateProfit}, " .
                "Multiplier: {$multiplier}");

            // Track accounts with frequent manual calculations for admin review
            // activity('trade_profit_calculation')
            //     ->withProperties([
            //         'account_code' => $account->code,
            //         'position_id' => $positionId,
            //         'manual_calculation' => true,
            //         'calculated_profit' => $profit,
            //         'price_diff' => $priceDiff,
            //         'volume_lots' => $volumeInLots,
            //         'contract_size' => $contractSize,
            //         'rate_profit' => $rateProfit
            //     ])
            //     ->log("Manual profit calculation used for position {$positionId} - deals not found");
            $profit = 0; // TEMPORARY OVERRIDE: Set to zero to avoid misleading data until deals are fixed . We will fetch such positions from positions api later like every 5 minutes
        }

        // Log::info("account code  ".$account->code);
        // Log::info("order  ".json_encode($closeOrder));
        // Log::info("order id  ".$closeOrder->Order);
        // Log::info("trade type  ".$closeOrder->Type);

        return [
            'account_id' => $account->id,
            'position_id' => $positionId,
            'order_id' => $openOrder->Order,
            'symbol' => $openOrder->Symbol,
            'type' => $openOrder->Type ? 'sell' : 'buy',
            'volume' => $openOrder->VolumeInitial / 10000,
            'volume_ext' => $openOrder->VolumeInitialExt,
            'open_price' => $openOrder->PriceCurrent,
            'close_price' => $closeOrder->PriceCurrent,
            'sl' => $openOrder->PriceSL,
            'tp' => $openOrder->PriceTP,
            'open_time' => date('Y-m-d H:i:s', $openOrder->TimeDone),
            'close_time' => date('Y-m-d H:i:s', $closeOrder->TimeDone),
            'state' => $closeOrder->State,
            'comment' => $openOrder->Comment,
            'profit' => $profit,
            'swap' => $actualSwap !== null ? round($actualSwap, 2) : 0,
            'commission' => $actualCommission !== null ? round($actualCommission, 2) : 0,
            'status' => 'closed',
            'code' => $account->code,
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }

    protected function processBatch(array $trades)
    {
        $batchStart = microtime(true);
        try {
            // FINAL VALIDATION: Check all trades in batch for invalid position_id
            $validTrades = [];
            $invalidCount = 0;

            foreach ($trades as $trade) {
                if (empty($trade['position_id']) || $trade['position_id'] == 0 || $trade['position_id'] === '0') {
                    $invalidCount++;
                    Log::critical("🚨 BATCH VALIDATION: Invalid position_id detected in batch", [
                        'position_id' => $trade['position_id'],
                        'account_id' => $trade['account_id'],
                        'account_code' => $trade['code'] ?? 'unknown',
                        'order_id' => $trade['order_id'] ?? 'unknown',
                        'symbol' => $trade['symbol'] ?? 'unknown',
                        'trade_data' => $trade,
                        'batch_size' => count($trades),
                        'issue_type' => 'INVALID_POSITION_ID_BATCH_LEVEL'
                    ]);

                    // Log to admin activity log
                    activity('trade_data_integrity')
                        ->withProperties([
                            'position_id' => $trade['position_id'],
                            'account_code' => $trade['code'] ?? 'unknown',
                            'trade_data' => $trade
                        ])
                        ->log("🚨 CRITICAL: Invalid position_id ({$trade['position_id']}) caught at batch level");
                } else {
                    $validTrades[] = $trade;
                }
            }

            if ($invalidCount > 0) {
                Log::critical("BATCH PROCESSING: Filtered out {$invalidCount} trades with invalid position_id from batch of " . count($trades));
            }

            // Only process valid trades
            if (!empty($validTrades)) {
                // Optimized upsert with composite unique key to prevent duplicates
                // Using account_id + position_id as the unique identifier prevents duplicate trades
                Trade::upsert(
                    $validTrades,
                    ['account_id', 'position_id'], // composite unique identifier
                    ['close_price', 'close_time', 'state', 'status', 'profit', 'volume', 'volume_ext', 'type', 'code', 'order_id', 'updated_at'] // essential columns including volume, type, code, and order_id
                );
                $batchTime = round((microtime(true) - $batchStart) * 1000, 2);
                Log::debug("DB Batch: " . count($validTrades) . " valid trades in {$batchTime}ms" .
                    ($invalidCount > 0 ? " ({$invalidCount} invalid filtered)" : ""));
            } else {
                $batchTime = round((microtime(true) - $batchStart) * 1000, 2);
                Log::warning("DB Batch: No valid trades to process ({$invalidCount} invalid filtered) in {$batchTime}ms");
            }
        } catch (\Exception $e) {
            $batchTime = round((microtime(true) - $batchStart) * 1000, 2);
            Log::error("CRITICAL ERROR processing trade batch: {$batchTime}ms", [
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'exception_code' => $e->getCode(),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
                'batch_size' => count($trades),
                'batch_time_ms' => $batchTime,
                'sample_trades' => array_slice($trades, 0, 3), // Log first 3 trades for context
                'stack_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function updateSyncStatus(Account $account, string $status, int $tradesCount = 0, string $customError = null): void
    {
        $syncStatus = match ($status) {
            'success', 'no_changes' => 'synced',
            'not_found' => 'error',
            'error' => 'error',
            'skipped_high_volume', 'skipped_low_volume' => 'skipped',
            default => 'pending'
        };

        $syncError = null;
        if ($status === 'error') {
            $syncError = $customError ?: 'Sync failed';
        } elseif ($status === 'not_found') {
            $syncError = 'MT5 account not found';
        } elseif (in_array($status, ['skipped_high_volume', 'skipped_low_volume'])) {
            $syncError = $customError;
        }

        $account->update([
            'last_balance_sync_at' => now(),
            'last_sync_attempt_at' => now(),
            'sync_status' => $syncStatus,
            'sync_error' => $syncError
        ]);

        // Log::info("Updated sync status for account {$account->code}: {$status} -> {$syncStatus} (trades: {$tradesCount})");
    }

    protected function updateLastTradeTime(Account $account, $orders): void
    {
        if (empty($orders)) {
            return;
        }

        // Find the most recent trade time
        $latestTime = 0;
        foreach ($orders as $order) {
            $latestTime = max($latestTime, $order->TimeDone, $order->TimeSetup);
        }

        if ($latestTime > 0) {
            $account->update([
                'last_trade_at' => Carbon::createFromTimestamp($latestTime)
            ]);
        }
    }

    protected function connectWithRetry(UniversalMT5Service $mt5Service, int $maxRetries = 3): void
    {
        // UniversalMT5Service handles connection pooling and retries
        if (!$mt5Service->connect()) {
            throw new \Exception("Failed to connect to MT5 after {$maxRetries} attempts (via pool)");
        }
    }

    /**
     * Log invalid position_id attempts with comprehensive details for admin investigation
     */
    protected function logInvalidPositionId(string $tradeType, $account, $positionId, $order, array $context = []): void
    {
        $logData = array_merge([
            'trade_type' => $tradeType,
            'position_id' => $positionId,
            'account_id' => $account->id,
            'account_code' => $account->code,
            'account_demo' => $account->demo,
            'order_id' => $order->Order ?? 'unknown',
            'order_symbol' => $order->Symbol ?? 'unknown',
            'order_type' => $order->Type ?? 'unknown',
            'order_volume' => $order->VolumeInitial ?? 'unknown',
            'order_price' => $order->PriceCurrent ?? 'unknown',
            'order_time_done' => $order->TimeDone ?? 'unknown',
            'order_comment' => $order->Comment ?? '',
            'timestamp' => now(),
            'job_id' => $this->job->getJobId() ?? 'unknown',
            'queue' => $this->job->getQueue() ?? 'unknown',
            'severity' => 'CRITICAL',
            'issue_type' => 'INVALID_POSITION_ID_JOB_LEVEL',
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15)
        ], $context);

        // Log critical data integrity issue with full context
        Log::critical("🚨 INVALID POSITION_ID in BatchSyncTradesJob: {$tradeType} trade with position_id = {$positionId}", $logData);

        // Log to admin activity log for dashboard visibility
        activity('trade_data_integrity')
            ->withProperties($logData)
            ->log("🚨 CRITICAL: Invalid position_id ({$positionId}) in {$tradeType} trade for account {$account->code}");

        // Send immediate admin notification if Slack is configured
        try {
            if (config('logging.channels.slack') !== null) {
                Log::channel('slack')->critical("Invalid position_id detected in trade sync", [
                    'account' => $account->code,
                    'position_id' => $positionId,
                    'trade_type' => $tradeType,
                    'order_id' => $order->Order ?? 'unknown'
                ]);
            }
        } catch (\Exception $e) {
            // Slack not configured or unavailable - only log as warning
            Log::warning("Slack notification skipped for invalid position_id (not configured or unavailable)");
        }
    }

    /**
     * Clear sync-in-progress cache for all accounts in this batch
     */
    protected function clearBatchSyncInProgressCache(): void
    {
        foreach ($this->accounts as $accountData) {
            // Clear both regular and demo cache markers
            Cache::forget("account_sync_in_progress:{$accountData['id']}");
            Cache::forget("demo_account_sync_in_progress:{$accountData['id']}");
        }

        $accountCodes = collect($this->accounts)->pluck('code')->join(', ');
        Log::debug("Cleared sync-in-progress cache for accounts: {$accountCodes}");
    }

    /**
     * Handle job failure - clear sync-in-progress cache and reset account status
     * This is called when the job hits max attempts
     */
    public function failed(\Throwable $exception)
    {
        $accountCodes = collect($this->accounts)->pluck('code')->toArray();
        $accountIds = collect($this->accounts)->pluck('id')->toArray();

        $failureDetails = [
            'reason' => 'Max attempts exceeded',
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
            'accounts_codes' => $accountCodes,
            'accounts_count' => count($this->accounts),
            'job_id' => $this->job->getJobId() ?? 'unknown',
            'job_attempts' => $this->attempts(),
            'job_max_tries' => $this->tries,
            'job_timeout' => $this->timeout,
            'queue_name' => $this->job->getQueue() ?? 'default',
            'timestamp' => now()->toIso8601String(),
            'stack_trace' => $exception->getTraceAsString(),
            'previous_exception' => $exception->getPrevious() ? [
                'class' => get_class($exception->getPrevious()),
                'message' => $exception->getPrevious()->getMessage(),
                'file' => $exception->getPrevious()->getFile(),
                'line' => $exception->getPrevious()->getLine()
            ] : null
        ];

        Log::error("🚨 CRITICAL: BatchSyncTradesJob permanently failed after {$this->attempts()} attempts", $failureDetails);

        // Log to activity log for admin visibility
        activity('batch_sync_job_failure')
            ->withProperties($failureDetails)
            ->log("BatchSyncTradesJob failed for accounts: " . implode(', ', $accountCodes));

        // Clear sync-in-progress cache so accounts can be retried
        $this->clearBatchSyncInProgressCache();

        // Reset account sync status so they can be retried by the next cycle
        Account::whereIn('id', $accountIds)
            ->update([
                'sync_status' => 'needs_retry',
                'sync_error' => 'Job failed after max attempts (' . $this->attempts() . '): ' . substr($exception->getMessage(), 0, 255)
            ]);
    }

    /**
     * Process deals directly from database instead of using MT5 API
     * This provides massive performance improvement when deal data is fresh
     *
     * CRITICAL: This method properly reconstructs POSITIONS from DEALS
     * - Groups deals by position_id
     * - Calculates if position is open/closed based on volume balance
     * - Properly determines opening/closing times and prices
     */
    protected function processDealsBatch(Account $account, $deals, Carbon $fromTime, TradeCacheService $cacheService): string
    {
        // Log::info("DEBUG[{$account->code}]: Processing {$deals->count()} deals from database (position reconstruction)");

        $processedTrades = 0;
        $newTrades = 0;
        $updatedTrades = 0;

        // Group deals by position_id to reconstruct actual positions
        $positionGroups = $deals->groupBy('position_id');

        foreach ($positionGroups as $positionId => $positionDeals) {
            if (empty($positionId)) {
                // Log::warning("DEBUG[{$account->code}]: Skipping deals with empty position_id");
                continue;
            }

            // Sort deals by time to get proper sequence
            $sortedDeals = $positionDeals->sortBy('time_done');

            // Calculate position state from deals
            $positionData = $this->reconstructPositionFromDeals($sortedDeals);

            if (!$positionData) {
                // Log::warning("DEBUG[{$account->code}]: Could not reconstruct position {$positionId}");
                continue;
            }

            // Prepare trade data for database
            $tradeData = [
                'account_id' => $account->id,
                'position_id' => $positionId,  // CORRECT: Use actual position_id from deal
                'code' => $account->code,  // Add missing code field
                'order_id' => $positionData['order_id'] ?? null,  // Add missing order_id field
                'symbol' => $positionData['symbol'],
                'type' => $positionData['type'],
                'volume' => $positionData['volume'],
                'open_price' => $positionData['price_open'],
                'open_time' => $positionData['time_open'],
                'profit' => $positionData['profit'],
                'comment' => $positionData['comment'],
                'state' => $positionData['is_closed'] ? 'closed' : 'open',
                'status' => $positionData['is_closed'] ? 'closed' : 'open',  // Add status field
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Add closing data only if position is actually closed
            if ($positionData['is_closed']) {
                $tradeData['close_time'] = $positionData['time_close'];
                $tradeData['close_price'] = $positionData['price_close'];
            }

            // Check if trade already exists
            $existingTrade = Trade::where('account_id', $account->id)
                ->where('position_id', $positionId)  // CORRECT: Use position_id
                ->first();

            if ($existingTrade) {
                // Update existing trade
                $existingTrade->update($tradeData);
                $updatedTrades++;
                // Log::debug("DEBUG[{$account->code}]: Updated position {$positionId} - State: {$tradeData['state']}, Volume: {$tradeData['volume']}, Profit: {$tradeData['profit']}");
            } else {
                // Create new trade
                Trade::create($tradeData);
                $newTrades++;
                // Log::debug("DEBUG[{$account->code}]: Created position {$positionId} - State: {$tradeData['state']}, Volume: {$tradeData['volume']}, Profit: {$tradeData['profit']}");
            }

            $processedTrades++;
        }

        // Update account sync status
        $this->updateSyncStatus($account, 'success');

        // Invalidate cache
        $cacheService->invalidateAccount($account);

        // Log::info("DEBUG[{$account->code}]: Position reconstruction completed: {$processedTrades} positions processed, {$newTrades} new, {$updatedTrades} updated from {$positionGroups->count()} position groups");

        return $newTrades > 0 ? 'success' : 'no_changes';
    }

    /**
     * Reconstruct a position's state from its constituent deals
     * This is the CORE LOGIC for converting deals back to position/trade data
     */
    protected function reconstructPositionFromDeals($deals): ?array
    {
        if ($deals->isEmpty()) {
            return null;
        }

        $firstDeal = $deals->first();
        $lastDeal = $deals->last();

        // Calculate volume balance to determine if position is open/closed
        $openVolume = $deals->where('action', 0)->sum('volume');   // Action 0 = OPEN
        $closeVolume = $deals->where('action', 1)->sum('volume'); // Action 1 = CLOSE

        $netVolume = $openVolume - $closeVolume;
        $isPositionClosed = abs($netVolume) < 0.0001; // Consider closed if open/close volumes match

        // Determine position type (buy/sell) based on first deal
        $positionType = $firstDeal->type == 0 ? 'buy' : 'sell';

        // If position involves both open and close actions, determine the net direction
        if ($openVolume > 0 && $closeVolume > 0) {
            // For closed positions, use the original deal type
            $positionType = $firstDeal->type == 0 ? 'buy' : 'sell';
        }

        // Calculate effective volume (net position size)
        $effectiveVolume = abs($netVolume);
        if ($isPositionClosed) {
            // For closed positions, use the volume that was actually traded
            $effectiveVolume = min($openVolume, $closeVolume);
        }

        // Calculate weighted average opening price
        $openingDeals = $deals->where('type', $firstDeal->type);
        $totalOpenVolume = $openingDeals->sum('volume');
        $weightedOpenPrice = $totalOpenVolume > 0
            ? $openingDeals->sum(function ($deal) {
                return $deal->price * $deal->volume;
            }) / $totalOpenVolume
            : $firstDeal->price;

        // Calculate closing price if position is closed
        $closingPrice = null;
        if ($isPositionClosed && $openVolume > 0 && $closeVolume > 0) {
            $closingDeals = $deals->where('action', 1); // Action 1 = CLOSE
            $totalCloseVolume = $closingDeals->sum('volume');
            $closingPrice = $totalCloseVolume > 0
                ? $closingDeals->sum(function ($deal) {
                    return $deal->price * $deal->volume;
                }) / $totalCloseVolume
                : $lastDeal->price;
        }

        return [
            'symbol' => $firstDeal->symbol,
            'type' => $positionType,
            'volume' => $effectiveVolume,
            'price_open' => $weightedOpenPrice,
            'time_open' => $firstDeal->time_done,
            'price_close' => $closingPrice,
            'time_close' => $isPositionClosed ? $lastDeal->time_done : null,
            'swap' => $deals->sum('swap'),
            'commission' => $deals->sum('commission'),
            'profit' => $deals->sum('profit'),
            'comment' => $firstDeal->comment ?? '',
            'magic' => $firstDeal->magic ?? 0,
            'order_id' => $firstDeal->order_id,  // Add order_id from first deal
            'is_closed' => $isPositionClosed,
            'deal_count' => $deals->count(),
            'buy_volume' => $openVolume,
            'sell_volume' => $closeVolume,
            'net_volume' => $netVolume
        ];
    }
}
