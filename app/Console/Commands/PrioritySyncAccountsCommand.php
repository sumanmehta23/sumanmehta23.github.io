<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Jobs\BatchSyncTradesJob;
use App\Services\BalanceSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Priority-Based Account Sync Command with Balance Change Optimization
 *
 * This command continuously syncs accounts based on balance changes and sync priority:
 * 1. Only syncs accounts with balance activity (major optimization)
 * 2. Accounts that need retry (highest priority)
 * 3. Accounts with balance changes since last sync
 * 4. Accounts that haven't been synced for 6+ hours (fallback)
 * 5. Intelligent batching and queue limits for optimal performance
 * php artisan app:priority-sync --batch-size=100 --daemon
 */
class PrioritySyncAccountsCommand extends Command
{
    protected $signature = 'app:priority-sync
                            {--batch-size= : Number of accounts per batch (default from config)}
                            {--full-trade-sync : Force full trade sync for all eligible accounts from the beginning}
                            {--max-concurrent= : Maximum concurrent batches (default from config)}
                            {--cycle-delay= : Delay between sync cycles in seconds (default from config)}
                            {--min-sync-interval= : Minimum minutes between syncs for same account (default from config, improved: 20m)}
                            {--max-pending-jobs= : Maximum pending BatchSyncTradesJob jobs allowed (default from config)}
                            {--stale-threshold= : Sync accounts not synced for X hours (default: 3 hours, was 6-12)}
                            {--max-trades= : Skip accounts with more than this many pending trades (e.g., 200)}
                            {--min-trades= : Only sync accounts with at least this many pending trades}
                            {--trades-range= : Sync accounts with trades in range, format: min,max (e.g., 200,500)}
                            {--ignore-balance-filter : Sync all accounts regardless of balance changes}
                            {--sync-inactive : Include inactive accounts (dormant > 30 days) in sync}
                            {--oldest-first : Prioritize older accounts (created_at asc) over newer accounts (default: newest first)}
                            {--accounts= : Sync specific accounts only (comma-separated IDs or codes)}
                            {--single-trade-sync : Sync only one account per cycle (for targeted updates)}
                            {--distribute-commissions : Auto-dispatch commission distribution after sync (EXPERIMENTAL)}
                            {--daemon : Run continuously as daemon}
                            {--status : Show current sync status}
                            {--unflag-account= : Manually unflag a problematic account by code}
                            {--clear-stuck-cache : Clear all stuck account cache markers}';

    protected $description = 'Continuously sync accounts prioritizing those with balance changes and sync needs. Supports trade count filtering and specific account targeting.';

    public function handle()
    {
        $batchSize = (int) ($this->option('batch-size') ?: config('sync-all-trades.priority_sync.batch_size', 10));
        $maxConcurrent = (int) ($this->option('max-concurrent') ?: config('sync-all-trades.priority_sync.max_concurrent', 5));
        $cycleDelay = (int) ($this->option('cycle-delay') ?: config('sync-all-trades.priority_sync.cycle_delay', 5));
        $minSyncInterval = (int) ($this->option('min-sync-interval') ?: config('sync-all-trades.priority_sync.min_sync_interval', 20)); // IMPROVED: 20m default
        $maxPendingJobs = (int) ($this->option('max-pending-jobs') ?: config('sync-all-trades.priority_sync.max_pending_jobs', 100));
        $staleThreshold = (int) ($this->option('stale-threshold') ?: 3); // IMPROVED: 3 hours instead of 6-12
        $ignoreBalanceFilter = $this->option('ignore-balance-filter');
        $syncInactive = $this->option('sync-inactive');
        $oldestFirst = $this->option('oldest-first'); // Default is newest first
        $isDaemon = $this->option('daemon');
        $showStatus = $this->option('status');
        $fullTradeSync = $this->option('full-trade-sync') ?? false;
        $singleTradeSync = $this->option('single-trade-sync') ?? false;
        $distributeCommissions = $this->option('distribute-commissions') ?? false;

        // Parse specific accounts to sync
        $specificAccounts = null;
        if ($this->option('accounts')) {
            $accountsInput = $this->option('accounts');
            $specificAccounts = array_map('trim', explode(',', $accountsInput));
            $this->info("Syncing specific accounts: " . implode(', ', $specificAccounts));
        }

        // Parse trade count filtering options
        $maxTrades = $this->option('max-trades') ? (int) $this->option('max-trades') : null;
        $minTrades = $this->option('min-trades') ? (int) $this->option('min-trades') : null;

        // Parse trades range (format: min,max)
        if ($this->option('trades-range')) {
            $range = explode(',', $this->option('trades-range'));
            if (count($range) === 2) {
                $minTrades = (int) trim($range[0]);
                $maxTrades = (int) trim($range[1]);
            } else {
                $this->error("Invalid trades-range format. Use: min,max (e.g., 200,500)");
                return 1;
            }
        }

        if ($showStatus) {
            $this->showSyncStatus();
            return;
        }

        // Handle account unflagging
        if ($this->option('unflag-account')) {
            $this->unflagAccount($this->option('unflag-account'));
            return;
        }

        // Handle clearing stuck cache
        if ($this->option('clear-stuck-cache')) {
            $this->clearAllStuckCache();
            return;
        }

        $this->info("Starting priority-based sync with:");
        $this->info("- Batch size: {$batchSize}" . ($this->option('batch-size') ? '' : ' (config)'));
        $this->info("- Max concurrent: {$maxConcurrent}" . ($this->option('max-concurrent') ? '' : ' (config)'));
        $this->info("- Cycle delay: {$cycleDelay}s" . ($this->option('cycle-delay') ? '' : ' (config)'));
        $this->info("- Min sync interval: {$minSyncInterval}m" . ($this->option('min-sync-interval') ? '' : ' (config - IMPROVED: 20m)'));
        $this->info("- Stale threshold: {$staleThreshold}h (IMPROVED: default 3h instead of 6-12h)");
        $this->info("- Max pending jobs: {$maxPendingJobs}" . ($this->option('max-pending-jobs') ? '' : ' (config)'));

        // Trade count filtering info
        if ($maxTrades || $minTrades) {
            if ($minTrades && $maxTrades) {
                $this->warn("- Trade count filter: {$minTrades} to {$maxTrades} trades only");
            } elseif ($maxTrades) {
                $this->warn("- Max trades filter: Skip accounts with > {$maxTrades} trades");
            } elseif ($minTrades) {
                $this->warn("- Min trades filter: Only accounts with >= {$minTrades} trades");
            }
        }

        // Account ordering info
        if ($oldestFirst) {
            $this->info("- Account order: Oldest first (created_at ASC)");
        } else {
            $this->info("- Account order: Newest first (created_at DESC) - default");
        }

        // Trade count filter status
        if (!$maxTrades && !$minTrades) {
            $this->info("- Trade count filter: DISABLED (sync all account sizes)");
        }

        if ($ignoreBalanceFilter) {
            $this->warn("- Balance filter: DISABLED (will sync all accounts)");
        } else {
            $this->info("- Balance filter: ENABLED (IMPROVED: remove to sync inactive accounts)");
        }

        if ($syncInactive) {
            $this->warn("- Inactive accounts: INCLUDED (dormant > 30 days)");
        }

        if ($singleTradeSync) {
            $this->warn("- Single trade sync mode: ONE account per cycle for targeted updates");
        }

        if ($distributeCommissions) {
            $this->warn("- Commission distribution: Will dispatch after successful trade syncs (EXPERIMENTAL)");
        }

        if ($isDaemon) {
            $this->runDaemonMode($batchSize, $maxConcurrent, $cycleDelay, $minSyncInterval, $maxPendingJobs, $ignoreBalanceFilter, $maxTrades, $minTrades, $oldestFirst, $fullTradeSync, $specificAccounts, $staleThreshold, $syncInactive, $singleTradeSync, $distributeCommissions);
        } else {
            $this->runSingleCycle($batchSize, $maxConcurrent, $minSyncInterval, $maxPendingJobs, $ignoreBalanceFilter, $maxTrades, $minTrades, $oldestFirst, $fullTradeSync, $specificAccounts, $staleThreshold, $syncInactive, $singleTradeSync, $distributeCommissions);
        }
    }

    /**
     * Get the number of pending BatchSyncTradesJob jobs in the queue
     */
    protected function getPendingJobsCount(): int
    {
        try {
            // For Redis queue - check the queue size for the correct queue name
            $queueName = 'queues:priority-sync-trades';
            $pendingCount = Redis::llen($queueName);

            // Also check for any delayed/reserved jobs
            $delayedCount = Redis::zcard($queueName . ':delayed');
            $reservedCount = Redis::zcard($queueName . ':reserved');

            return $pendingCount + $delayedCount + $reservedCount;
        } catch (\Exception $e) {
            Log::warning("Could not get queue count: " . $e->getMessage());
            return 0; // Assume no jobs if we can't check
        }
    }

    protected function showSyncStatus()
    {
        $this->info("=== Account Sync Status Overview ===");

        // Show queue status first
        $pendingJobs = $this->getPendingJobsCount();
        $this->info("Current BatchSyncTradesJob queue: {$pendingJobs} pending jobs");
        $this->info("");

        // $totalAccounts = Account::whereNotNull('code')
        //     ->whereNull('deleted_at')
        //     ->where('demo', false)
        //     ->where('code', '<>', 'Rejected')
        //     ->count();

        $totalAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            // ->where('demo', false)
            ->whereRaw("( (competition_product_id IS NULL AND demo = 0)
                    OR (competition_product_id IS NOT NULL AND demo = 1) )")
            ->where('code', '<>', 'Rejected')
            ->count();

        Log::info("totalAccounts " . $totalAccounts);

        $neverSynced = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            // ->where('demo', false)
            ->whereRaw("( (competition_product_id IS NULL AND demo = 0)
                    OR (competition_product_id IS NOT NULL AND demo = 1) )")
            ->whereNull('last_sync_attempt_at')
            ->where('code', '<>', 'Rejected')
            ->count();

        Log::info("neverSynced " . $neverSynced);

        $syncedToday = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            // ->where('demo', false)
            ->whereRaw("( (competition_product_id IS NULL AND demo = 0)
                    OR (competition_product_id IS NOT NULL AND demo = 1) )")
            ->where('last_sync_attempt_at', '>=', now()->subDay())
            ->count();

        Log::info("syncedToday " . $syncedToday);

        $stale6h = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            // ->where('demo', false)
            ->whereRaw("( (competition_product_id IS NULL AND demo = 0)
                    OR (competition_product_id IS NOT NULL AND demo = 1) )")
            ->whereNotNull('last_sync_attempt_at')
            ->where('last_sync_attempt_at', '<', now()->subHours(6))
            ->count();

        $stale24h = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            // ->where('demo', false)
            ->whereRaw("( (competition_product_id IS NULL AND demo = 0)
                    OR (competition_product_id IS NOT NULL AND demo = 1) )")
            ->whereNotNull('last_sync_attempt_at')
            ->where('code', '<>', 'Rejected')
            ->where('last_sync_attempt_at', '<', now()->subDay())
            ->count();

        $retryAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            // ->where('demo', false)
            ->whereRaw("( (competition_product_id IS NULL AND demo = 0)
                    OR (competition_product_id IS NOT NULL AND demo = 1) )")
            ->where('sync_status', 'needs_retry')
            ->count();

        $flaggedAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            // ->where('demo', false)
            ->whereRaw("( (competition_product_id IS NULL AND demo = 0)
                    OR (competition_product_id IS NOT NULL AND demo = 1) )")
            ->where('sync_status', 'flagged')
            ->count();

        $this->table(['Status', 'Count'], [
            ['Current Queue Jobs (BatchSyncTradesJob)', $pendingJobs],
            ['Needs Retry (Queue Limit Hit)', $retryAccounts],
            ['Flagged Problematic Accounts', $flaggedAccounts],
            ['Total Eligible Accounts', $totalAccounts],
            ['Never Synced (Highest Priority)', $neverSynced],
            ['Synced Today', $syncedToday],
            ['Stale (>6 hours)', $stale6h],
            ['Very Stale (>24 hours)', $stale24h],
        ]);

        // Show flagged accounts details if any exist
        if ($flaggedAccounts > 0) {
            $this->info("\n=== Flagged Accounts Details ===");
            $flaggedDetails = Account::whereNotNull('code')
                ->whereNull('deleted_at')
                // ->where('demo', false)
                ->whereRaw("( (competition_product_id IS NULL AND demo = 0)
                    OR (competition_product_id IS NOT NULL AND demo = 1) )")
                ->where('sync_status', 'flagged')
                ->select('code', 'sync_flag_reason', 'sync_flagged_at', 'sync_stuck_count', 'sync_error')
                ->get();

            $flaggedTable = $flaggedDetails->map(function ($account) {
                return [
                    $account->code,
                    $account->sync_flag_reason,
                    $account->sync_stuck_count,
                    $account->sync_flagged_at ? $account->sync_flagged_at->format('Y-m-d H:i') : 'N/A',
                    substr($account->sync_error, 0, 50) . '...'
                ];
            })->toArray();

            $this->table(['Account', 'Flag Reason', 'Stuck Count', 'Flagged At', 'Error'], $flaggedTable);
            $this->info("Use --unflag-account=CODE to manually unflag an account");
        }

        // Show recent sync status distribution
        $statusCounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            // ->where('demo', false)
            ->whereRaw("( (competition_product_id IS NULL AND demo = 0)
                    OR (competition_product_id IS NOT NULL AND demo = 1) )")
            ->selectRaw('sync_status, COUNT(*) as count')
            ->groupBy('sync_status')
            ->get();

        if ($statusCounts->isNotEmpty()) {
            $this->info("\n=== Sync Status Distribution ===");
            $statusTable = $statusCounts->map(function ($item) {
                return [$item->sync_status ?: 'null', $item->count];
            })->toArray();
            $this->table(['Status', 'Count'], $statusTable);
        }
    }

    protected function runDaemonMode($batchSize, $maxConcurrent, $cycleDelay, $minSyncInterval, $maxPendingJobs, $ignoreBalanceFilter, $maxTrades = null, $minTrades = null, $oldestFirst = false, $fullTradeSync = false, $specificAccounts = null, $staleThreshold = 3, $syncInactive = false, $singleTradeSync = false, $distributeCommissions = false)
    {
        $this->info("Running in daemon mode. Press Ctrl+C to stop.");

        $cycleCount = 0;
        while (true) {
            try {
                $cycleCount++;
                $this->info("=== Sync Cycle #{$cycleCount} at " . now()->format('Y-m-d H:i:s') . " ===");

                // Check pending jobs before processing
                $pendingJobs = $this->getPendingJobsCount();
                $this->info("Current pending BatchSyncTradesJob jobs: {$pendingJobs}");

                if ($pendingJobs >= $maxPendingJobs) {
                    $this->warn("Queue limit reached ({$pendingJobs}/{$maxPendingJobs}). Skipping dispatch cycle.");
                    sleep($cycleDelay);
                    continue;
                }

                $processed = $this->runSingleCycle($batchSize, $maxConcurrent, $minSyncInterval, $maxPendingJobs, $ignoreBalanceFilter, $maxTrades, $minTrades, $oldestFirst, $fullTradeSync, $specificAccounts, $staleThreshold, $syncInactive, $singleTradeSync, $distributeCommissions);

                if ($processed === 0) {
                    $this->info("No accounts needed syncing. Waiting {$cycleDelay}s before next cycle...");
                    sleep($cycleDelay);
                } else {
                    $this->info("Processed {$processed} accounts. Waiting {$cycleDelay}s before next cycle...");
                    sleep($cycleDelay);
                }
            } catch (\Exception $e) {
                $this->error("Daemon cycle error: " . $e->getMessage());
                Log::error("PrioritySyncAccounts daemon error: " . $e->getMessage());
                sleep(60); // Wait 1 minute before retrying on error
            }
        }
    }

    protected function runSingleCycle($batchSize, $maxConcurrent, $minSyncInterval, $maxPendingJobs, $ignoreBalanceFilter = false, $maxTrades = null, $minTrades = null, $oldestFirst = false, $fullTradeSync = false, $specificAccounts = null, $staleThreshold = 3, $syncInactive = false, $singleTradeSync = false, $distributeCommissions = false): int
    {
        // First, handle any stuck accounts from previous cycles
        $this->handleStuckAccounts();

        // Check pending jobs first
        $pendingJobs = $this->getPendingJobsCount();
        $this->info("Current pending BatchSyncTradesJob jobs: {$pendingJobs}");

        if ($pendingJobs >= $maxPendingJobs) {
            $this->warn("Queue limit reached ({$pendingJobs}/{$maxPendingJobs}). Skipping sync cycle.");
            return 0;
        }

        // Get accounts that need syncing with balance change optimization
        $cutoffTime = now()->subMinutes($minSyncInterval);  // Don't sync accounts synced within min interval
        $staleTime = now()->subHours($staleThreshold);  // IMPROVED: Use configurable stale threshold (default 3h instead of 6h)
        $veryStaleTime = now()->subHours($staleThreshold * 2);  // Double the threshold for very stale (6h default instead of 12h)

        $query = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            // ->where('demo', false)
            ->whereRaw("( (competition_product_id IS NULL AND demo = 0)
                    OR (competition_product_id IS NOT NULL AND demo = 1) )");

        // When specific accounts are provided, bypass most restrictions to force sync
        if ($specificAccounts !== null) {
            $this->info("Forcing sync for specific accounts - bypassing status and timing restrictions");
            // Only exclude accounts that are deleted or not found in MT5 (safety check)
            $query->whereNotIn('sync_status', ['not_found_in_mt5']); // Only exclude accounts not found in MT5
        } else {
            // Apply normal restrictions when no specific accounts are provided
            // IMPORTANT: Don't sync accounts that were synced within the minimum interval (unless they need retry)
            $query->where(function ($q) use ($cutoffTime) {
                $q->whereIn('sync_status', ['needs_retry', 'pending']) // Always include retry accounts regardless of timing
                    ->orWhereNull('last_sync_attempt_at') // Never synced
                    ->orWhere('last_sync_attempt_at', '<', $cutoffTime); // Last sync was before cutoff time
            })
                ->whereNotIn('sync_status', ['flagged', 'not_found_in_mt5']); // Exclude flagged problematic accounts and accounts not found in MT5
        }

        // Skip balance and status filters when specific accounts are provided
        if ($specificAccounts === null) {
            if (!$ignoreBalanceFilter) {
                // IMPROVED: Balance filter is optional - can be disabled to sync inactive accounts
                // Filter includes: accounts with balance activity OR retry accounts OR inactive ages
                $query->where('balance', '<>', 0)->where(function ($q) use ($staleTime, $veryStaleTime, $syncInactive) {
                    $q->whereIn('sync_status', ['needs_retry', 'pending']) // Always include retry accounts
                        ->orWhere(function ($balanceQuery) use ($staleTime, $syncInactive) {
                            $balanceQuery->where('has_balance_activity', true)
                                ->where(function ($syncQuery) use ($staleTime) {
                                    $syncQuery->whereNull('last_sync_attempt_at') // Never synced trades
                                        ->orWhereColumn('last_balance_changed_at', '>', 'last_sync_attempt_at') // Balance changed since last TRADE sync
                                        ->orWhere('last_sync_attempt_at', '<', $staleTime); // Force sync at stale threshold
                                });
                        })
                        ->orWhere(function ($fallbackQuery) use ($veryStaleTime, $syncInactive) {
                            // Fallback: sync accounts without balance tracking that are very stale
                            $fallbackQuery->whereNull('has_balance_activity')
                                ->where(function ($staleQuery) use ($veryStaleTime) {
                                    $staleQuery->whereNull('last_sync_attempt_at')
                                        ->orWhere('last_sync_attempt_at', '<', $veryStaleTime);
                                });
                        });

                    // IMPROVED: Optionally include inactive accounts for minimal checks
                    if ($syncInactive) {
                        $q->orWhere(function ($inactiveQuery) use ($veryStaleTime) {
                            $inactiveQuery->where('last_trade_at', '<', now()->subDays(30))
                                ->where(function ($inactiveCheck) use ($veryStaleTime) {
                                    $inactiveCheck->whereNull('last_sync_attempt_at')
                                        ->orWhere('last_sync_attempt_at', '<', $veryStaleTime);
                                });
                        });
                    }
                });
            } else {
                // Original logic when balance filter is disabled
                // Only include accounts that need syncing or retry
                $query->where(function ($q) use ($syncInactive, $veryStaleTime) {
                    $q->whereIn('sync_status', ['pending', 'needs_retry'])  // Always include these
                        ->orWhere(function ($timeQuery) use ($syncInactive, $veryStaleTime) {
                            $timeQuery->where(function ($statusQuery) {
                                $statusQuery->whereNull('sync_status')  // No status (fresh accounts)
                                    ->orWhereNotIn('sync_status', ['skipped', 'failed', 'completed', 'synced', 'error', 'not_found_in_mt5']);  // Exclude final statuses and accounts not found in MT5
                            });
                            // Note: The timing check is already applied above in the main query
                        });

                    // IMPROVED: Include inactive accounts when requested
                    if ($syncInactive) {
                        $q->orWhere(function ($inactiveQuery) use ($veryStaleTime) {
                            $inactiveQuery->where('last_trade_at', '<', now()->subDays(30))
                                ->where(function ($inactiveCheck) use ($veryStaleTime) {
                                    $inactiveCheck->whereNull('last_sync_attempt_at')
                                        ->orWhere('last_sync_attempt_at', '<', $veryStaleTime);
                                });
                        });
                    }
                });
            }
        }

        // Filter for specific accounts if provided
        if ($specificAccounts !== null) {
            $query->where(function ($q) use ($specificAccounts) {
                $q->whereIn('code', $specificAccounts)
                    ->orWhereIn('id', array_filter($specificAccounts, 'is_numeric'));
            });
        }

        $accounts = $query
            // Priority order according to requirements:
            // 1. Retry accounts first (highest priority)
            ->orderByRaw("CASE WHEN sync_status = 'needs_retry' THEN 0 ELSE 1 END")
            // 2. Accounts with recent balance changes (most recent balance changes first)
            ->orderByRaw("CASE WHEN has_balance_activity = 1 AND last_balance_changed_at > COALESCE(last_sync_attempt_at, '1970-01-01') THEN 0 ELSE 1 END")
            ->orderBy('last_balance_changed_at', 'desc')
            // 3. Accounts that were synced longest ago (oldest sync attempts first)
            ->orderByRaw('last_sync_attempt_at IS NULL DESC') // Never synced first
            ->orderBy('last_sync_attempt_at', 'asc');

        // 4. Latest accounts first (newest accounts have priority by default)
        if ($oldestFirst) {
            $accounts = $accounts->orderBy('created_at', 'asc');  // Oldest first (when flag specified)
        } else {
            $accounts = $accounts->orderBy('created_at', 'desc'); // Newest first (default)
        }

        // IMPROVED: Support single-trade-sync mode for targeted updates
        $limitAccounts = $singleTradeSync ? $batchSize : ($batchSize * $maxConcurrent);

        $accounts = $accounts
            ->limit($limitAccounts) // Get enough for all concurrent batches, or single batch if single-trade-sync mode
            ->get();

        if ($accounts->isEmpty()) {
            if ($ignoreBalanceFilter) {
                $this->info("No accounts need syncing (all synced within {$minSyncInterval}m)");
            } else {
                $this->info("No accounts need syncing (no balance changes detected)");
            }
            return 0;
        }

        // Filter out accounts that are already being synced (cache-based)
        $initialCount = $accounts->count();
        $accounts = $this->filterAccountsNotInProgress($accounts);
        $filteredCount = $accounts->count();

        if ($filteredCount < $initialCount) {
            $this->info("Filtered out " . ($initialCount - $filteredCount) . " accounts already in progress");
        }

        if ($accounts->isEmpty()) {
            $this->info("No accounts available for sync (all currently in progress)");
            return 0;
        }

        $this->info("Found {$accounts->count()} accounts needing sync" .
            ($ignoreBalanceFilter ? " (balance filter disabled)" : " (balance-optimized)"));

        // Show priority breakdown
        $retryAccounts = $accounts->where('sync_status', 'needs_retry')->count();
        $balanceChanged = $accounts->where('has_balance_activity', true)
            ->filter(function ($account) {
                return $account->last_balance_changed_at &&
                    (!$account->last_sync_attempt_at ||
                        $account->last_balance_changed_at > $account->last_sync_attempt_at);
            })->count();
        $neverSynced = $accounts->whereNull('last_sync_attempt_at')
            ->where('sync_status', '!=', 'needs_retry')->count();
        $staleSynced = $accounts->count() - $retryAccounts - $balanceChanged - $neverSynced;

        $this->info("Priority: {$retryAccounts} retry, {$balanceChanged} balance changed, {$neverSynced} never synced, {$staleSynced} stale");

        // Process in batches
        $accountBatches = $accounts->chunk($batchSize);
        $processedCount = 0;

        foreach ($accountBatches as $batchIndex => $accountBatch) {
            // Check queue size before each batch dispatch
            $currentPendingJobs = $this->getPendingJobsCount();
            if ($currentPendingJobs >= $maxPendingJobs) {
                $this->warn("Queue limit reached ({$currentPendingJobs}/{$maxPendingJobs}). Stopping further dispatches.");
                // IMPORTANT: Reset the sync attempt timestamp for unprocessed accounts
                // so they can be retried in the next cycle
                $unprocessedAccountIds = $accountBatches->slice($batchIndex)->flatten()->pluck('id');
                if ($unprocessedAccountIds->isNotEmpty()) {
                    Account::whereIn('id', $unprocessedAccountIds)
                        ->update([
                            'sync_status' => 'needs_retry',
                            'sync_error' => 'Queue limit reached - will retry next cycle'
                        ]);

                    $this->info("Marked " . $unprocessedAccountIds->count() . " accounts for retry in next cycle");
                }
                break;
            }

            $batchAccounts = $accountBatch->values()->all();
            $batchSyncTimes = [];

            // Calculate sync times for each account
            foreach ($accountBatch as $account) {
                if ($fullTradeSync) {
                    $batchSyncTimes[] = $account->created_at;
                } else {
                    $lastSync = $account->last_balance_sync_at;
                    $batchSyncTimes[] = $lastSync ? Carbon::parse($lastSync) : now()->subDays(7);
                }
            }

            $accountCodes = $accountBatch->pluck('code')->join(', ');
            $this->info("Dispatching batch " . ($batchIndex + 1) . " with " . count($batchAccounts) . " accounts: {$accountCodes} (Queue: {$currentPendingJobs}/{$maxPendingJobs})");

            // Mark accounts as sync in progress (cache-based with 30-minute TTL)
            foreach ($accountBatch as $account) {
                $this->markAccountSyncInProgress($account->id, 1);
            }

            // Update sync attempt timestamp ONLY for accounts being dispatched
            Account::whereIn('id', $accountBatch->pluck('id'))
                ->update([
                    'last_sync_attempt_at' => now(),
                    'sync_status' => 'pending'
                ]);

            // Dispatch the batch job with trade count limits
            $batchJob = new BatchSyncTradesJob($batchAccounts, $batchSyncTimes, $maxTrades, $minTrades);
            dispatch($batchJob)->onQueue('priority-sync-trades');

            // IMPROVED: Optionally dispatch commission distribution immediately after trade sync
            // This integrates IB commission calculation into the sync pipeline
            if ($distributeCommissions) {
                foreach ($accountBatch as $account) {
                    // Dispatch commission distribution for this account's IB referrers
                    // The commission job will process trades synced by the BatchSyncTradesJob
                    $ib1 = $account->ib1_code ?? null; // Assuming account has IB relationship

                    if ($ib1) {
                        // Find all users who have this account's IB code in their referral chain
                        $ibUsers = DB::table('aspnetusers')
                            ->where('status', 1)
                            ->where(function ($q) use ($ib1) {
                                for ($i = 1; $i <= 15; $i++) {
                                    $q->orWhere("ib$i", $ib1);
                                }
                            })
                            ->pluck('id', 'email')
                            ->toArray();

                        foreach ($ibUsers as $email => $userId) {
                            $commissionJob = new \App\Jobs\DistributeIbCommissionJob($ib1, $userId, [], $account->id);
                            dispatch($commissionJob)->onQueue('distributeibcommission');
                            $this->info("Commission job queued for account {$account->code} (IB: {$ib1}, User: {$userId})");
                        }
                    }
                }
            }

            $processedCount += count($batchAccounts);

            // Small delay between batch dispatches to avoid overwhelming
            if ($batchIndex < $accountBatches->count() - 1) {
                sleep(2);
            }
        }

        $this->info("Dispatched {$processedCount} accounts in " . $accountBatches->count() . " batches" .
            ($distributeCommissions ? " (with commission distribution enabled)" : ""));
        return $processedCount;
    }

    /**
     * Check if account is currently being synced (cache-based to prevent duplicates)
     */
    protected function isAccountSyncInProgress(string $accountId): bool
    {
        return Cache::has("account_sync_in_progress:{$accountId}");
    }

    /**
     * Mark account as sync in progress (cache-based with TTL)
     */
    protected function markAccountSyncInProgress(string $accountId, int $ttlMinutes = 30): void
    {
        Cache::put("account_sync_in_progress:{$accountId}", now()->toISOString(), now()->addMinutes($ttlMinutes));
    }

    /**
     * Clear sync in progress marker for account
     */
    protected function clearAccountSyncInProgress(string $accountId): void
    {
        Cache::forget("account_sync_in_progress:{$accountId}");
    }

    /**
     * Get accounts that have been stuck in pending/sync for too long
     */
    protected function getStuckAccounts(int $stuckThresholdMinutes = 45): array
    {
        $stuckThreshold = now()->subMinutes($stuckThresholdMinutes);

        return Account::where('sync_status', 'pending')
            ->where('last_sync_attempt_at', '<', $stuckThreshold)
            ->get()
            ->toArray();
    }

    /**
     * Handle stuck accounts - clear cache, update status, flag if recurring
     */
    protected function handleStuckAccounts(): void
    {
        $stuckAccounts = $this->getStuckAccounts();

        if (empty($stuckAccounts)) {
            return;
        }

        $this->warn("Found " . count($stuckAccounts) . " stuck accounts, clearing and flagging...");

        foreach ($stuckAccounts as $account) {
            // Clear cache marker
            $this->clearAccountSyncInProgress($account['id']);

            // Increment stuck count
            Account::where('id', $account['id'])->increment('sync_stuck_count');

            // Get updated count
            $updatedAccount = Account::find($account['id']);
            $stuckCount = $updatedAccount->sync_stuck_count;

            if ($stuckCount >= 3) {
                // Flag as problematic account
                Account::where('id', $account['id'])->update([
                    'sync_status' => 'flagged',
                    'sync_error' => "Account repeatedly gets stuck in sync (stuck {$stuckCount} times) - requires manual review",
                    'sync_flag_reason' => 'repeated_stuck_jobs',
                    'sync_flagged_at' => now()
                ]);

                $this->error("Flagged account {$account['code']} - stuck {$stuckCount} times in sync");
            } else {
                // Reset to needs_retry with stuck indicator
                Account::where('id', $account['id'])->update([
                    'sync_status' => 'needs_retry',
                    'sync_error' => "Job stuck #{$stuckCount} - cleared for retry",
                ]);

                $this->info("Reset stuck account {$account['code']} for retry (stuck count: {$stuckCount})");
            }
        }
    }

    /**
     * Filter out accounts that are already in progress (cache-based)
     */
    protected function filterAccountsNotInProgress($accounts)
    {
        return $accounts->filter(function ($account) {
            return !$this->isAccountSyncInProgress($account->id);
        });
    }

    /**
     * Manually unflag a problematic account
     */
    protected function unflagAccount(string $accountCode): void
    {
        $account = Account::where('code', $accountCode)->first();

        if (!$account) {
            $this->error("Account {$accountCode} not found");
            return;
        }

        if ($account->sync_status !== 'flagged') {
            $this->info("Account {$accountCode} is not flagged (current status: {$account->sync_status})");
            return;
        }

        $account->update([
            'sync_status' => 'needs_retry',
            'sync_error' => 'Manually unflagged - cleared for retry',
            'sync_flag_reason' => null,
            'sync_flagged_at' => null,
            'sync_stuck_count' => 0
        ]);

        // Also clear any cache marker
        $this->clearAccountSyncInProgress($account->id);

        $this->info("Account {$accountCode} has been unflagged and reset for sync");
    }

    /**
     * Clear all stuck account cache markers
     */
    protected function clearAllStuckCache(): void
    {
        // Get all cache keys matching the pattern
        $pattern = "account_sync_in_progress:*";
        $keys = Cache::getRedis()->keys($pattern);

        if (empty($keys)) {
            $this->info("No stuck cache markers found");
            return;
        }

        $clearedCount = 0;
        foreach ($keys as $key) {
            // Remove the Redis key prefix if present
            $cleanKey = str_replace(config('database.redis.options.prefix', ''), '', $key);
            Cache::forget($cleanKey);
            $clearedCount++;
        }

        $this->info("Cleared {$clearedCount} stuck cache markers");
    }
}
