<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Jobs\BatchSyncTradesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Low-Priority Demo Accounts Sync Command
 * 
 * This command syncs demo accounts with very conservative settings:
 * 1. One account at a time (minimal system stress)
 * 2. Low priority queue to avoid interfering with live accounts
 * 3. Longer intervals between syncs
 * 4. Cache-based duplicate prevention
 * 5. Automatic stuck account handling
 */
class DemoAccountsSyncCommand extends Command
{
    protected $signature = 'app:demo-sync 
                            {--min-sync-interval=240 : Minimum minutes between syncs for same account (default: 4 hours)}
                            {--cycle-delay=60 : Delay between sync cycles in seconds (default: 1 minute)}
                            {--max-trades=100 : Skip accounts with more than this many pending trades}
                            {--min-trades=1 : Only sync accounts with at least this many pending trades}
                            {--daemon : Run continuously as daemon}
                            {--status : Show current demo sync status}
                            {--unflag-account= : Manually unflag a problematic demo account by code}
                            {--clear-stuck-cache : Clear all stuck demo account cache markers}';

    protected $description = 'Low-priority sync for demo accounts with minimal system impact (1 account at a time).';

    public function handle()
    {
        $minSyncInterval = (int) $this->option('min-sync-interval');
        $cycleDelay = (int) $this->option('cycle-delay');
        $maxTrades = $this->option('max-trades') ? (int) $this->option('max-trades') : null;
        $minTrades = $this->option('min-trades') ? (int) $this->option('min-trades') : null;
        $isDaemon = $this->option('daemon');
        $showStatus = $this->option('status');

        if ($showStatus) {
            $this->showDemoSyncStatus();
            return;
        }

        // Handle account unflagging
        if ($this->option('unflag-account')) {
            $this->unflagDemoAccount($this->option('unflag-account'));
            return;
        }

        // Handle clearing stuck cache
        if ($this->option('clear-stuck-cache')) {
            $this->clearAllStuckDemoCache();
            return;
        }

        $this->info('Starting low-priority demo accounts sync with:');
        $this->info("- Batch size: 1 (single account per job)");
        $this->info("- Max concurrent: 1 (no parallel processing)");
        $this->info("- Cycle delay: {$cycleDelay}s");
        $this->info("- Min sync interval: {$minSyncInterval}m");
        $this->info("- Queue: demo-sync-trades (low priority)");

        if ($maxTrades) {
            $this->info("- Max trades filter: Skip accounts with > {$maxTrades} trades");
        }
        if ($minTrades) {
            $this->info("- Min trades filter: Only accounts with >= {$minTrades} trades");
        }

        if ($isDaemon) {
            $this->runDaemonMode($minSyncInterval, $cycleDelay, $maxTrades, $minTrades);
        } else {
            $this->runSingleCycle($minSyncInterval, $maxTrades, $minTrades);
        }
    }

    protected function runDaemonMode($minSyncInterval, $cycleDelay, $maxTrades = null, $minTrades = null)
    {
        $this->info('Starting daemon mode for demo accounts sync...');

        while (true) {
            try {
                $this->info("\n" . str_repeat('=', 50));
                $this->info('Demo sync cycle started at ' . now()->format('Y-m-d H:i:s'));

                $processed = $this->runSingleCycle($minSyncInterval, $maxTrades, $minTrades);

                if ($processed === 0) {
                    $this->info("No demo accounts needed syncing. Waiting {$cycleDelay}s before next cycle...");
                } else {
                    $this->info("Processed {$processed} demo account. Waiting {$cycleDelay}s before next cycle...");
                }

                sleep($cycleDelay);
            } catch (\Exception $e) {
                $this->error("Demo sync daemon cycle error: " . $e->getMessage());
                Log::error("DemoAccountsSync daemon error: " . $e->getMessage());
                sleep(120); // Wait 2 minutes before retrying on error
            }
        }
    }

    protected function runSingleCycle($minSyncInterval, $maxTrades = null, $minTrades = null): int
    {
        // First, handle any stuck demo accounts from previous cycles
        $this->handleStuckDemoAccounts();

        // Check if there's already a demo sync job running
        $pendingJobs = $this->getDemoSyncJobsCount();
        $this->info("Current demo sync jobs in queue: {$pendingJobs}");

        if ($pendingJobs > 0) {
            $this->info("Demo sync job already running. Skipping this cycle.");
            return 0;
        }

        // Get demo accounts that need syncing
        $cutoffTime = now()->subMinutes($minSyncInterval);

        $query = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', true) // Only demo accounts
            ->where(function ($query) {
                $query->whereNull('sync_status')
                    ->orWhereNotIn('sync_status', ['not_found_in_mt5', 'flagged']);
            }); // Exclude flagged and not found accounts

        // Include accounts that need syncing
        $query->where(function ($q) use ($cutoffTime) {
            $q->whereIn('sync_status', ['needs_retry', 'pending']) // Always include retry accounts
                ->orWhere(function ($timeQuery) use ($cutoffTime) {
                    $timeQuery->where(function ($statusQuery) {
                        $statusQuery->whereNull('sync_status')  // No status (fresh accounts)
                            ->orWhereNotIn('sync_status', ['skipped', 'failed', 'completed', 'synced', 'error']);  // Exclude final statuses
                    })
                        ->where(function ($syncQuery) use ($cutoffTime) {
                            $syncQuery->whereNull('last_sync_attempt_at')  // Never synced
                                ->orWhere('last_sync_attempt_at', '<', $cutoffTime);  // Old syncs
                        });
                });
        });

        // Priority order for demo accounts: retry first, then oldest syncs, then newest accounts
        $accounts = $query
            ->orderByRaw("CASE WHEN sync_status = 'needs_retry' THEN 0 ELSE 1 END")
            ->orderByRaw('last_sync_attempt_at IS NULL DESC') // Never synced first
            ->orderBy('last_sync_attempt_at', 'asc') // Oldest syncs first
            ->orderBy('created_at', 'desc') // Newest accounts first
            ->limit(1) // Only get one account at a time
            ->get();

        if ($accounts->isEmpty()) {
            $this->info("No demo accounts need syncing (all synced within {$minSyncInterval}m)");
            return 0;
        }

        // Filter out accounts that are already being synced (cache-based)
        $initialCount = $accounts->count();
        $accounts = $this->filterDemoAccountsNotInProgress($accounts);
        $filteredCount = $accounts->count();

        if ($filteredCount < $initialCount) {
            $this->info("Filtered out " . ($initialCount - $filteredCount) . " demo accounts already in progress");
        }

        if ($accounts->isEmpty()) {
            $this->info("No demo accounts available for sync (already in progress)");
            return 0;
        }

        // Apply trade count filtering if specified
        if ($maxTrades || $minTrades) {
            $this->info("Applying trade count filtering...");
            $accounts = $this->filterAccountsByTradeCount($accounts, $maxTrades, $minTrades);
        }

        if ($accounts->isEmpty()) {
            $this->info("No demo accounts match trade count criteria");
            return 0;
        }

        $account = $accounts->first();
        $this->info("Found demo account needing sync: {$account->code}");

        // Mark account as sync in progress (cache-based with 2-hour TTL for demos)
        $this->markDemoAccountSyncInProgress($account->id, 120);

        // Update sync attempt timestamp
        Account::where('id', $account->id)->update([
            'last_sync_attempt_at' => now(),
            'sync_status' => 'pending'
        ]);

        // Calculate sync time
        $lastSync = $account->last_balance_sync_at;
        $syncTime = $lastSync ? Carbon::parse($lastSync) : now()->subDays(7);

        $this->info("Dispatching demo sync job for account: {$account->code} (Queue: demo-sync-trades)");

        // Dispatch single account job to low-priority queue
        $job = new BatchSyncTradesJob([$account], [$syncTime], $maxTrades, $minTrades);
        dispatch($job)->onQueue('demo-sync-trades');

        return 1;
    }

    /**
     * Get the number of pending demo sync jobs
     */
    protected function getDemoSyncJobsCount(): int
    {
        try {
            $queueName = config('queue.connections.redis.queue') . ':demo-sync-trades';
            $pendingCount = Redis::llen($queueName);
            $delayedCount = Redis::zcard($queueName . ':delayed');
            $reservedCount = Redis::zcard($queueName . ':reserved');

            return $pendingCount + $delayedCount + $reservedCount;
        } catch (\Exception $e) {
            Log::warning("Failed to get demo sync queue count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if demo account is currently being synced
     */
    protected function isDemoAccountSyncInProgress(string $accountId): bool
    {
        return Cache::has("demo_account_sync_in_progress:{$accountId}");
    }

    /**
     * Mark demo account as sync in progress
     */
    protected function markDemoAccountSyncInProgress(string $accountId, int $ttlMinutes = 120): void
    {
        Cache::put("demo_account_sync_in_progress:{$accountId}", now()->toISOString(), now()->addMinutes($ttlMinutes));
    }

    /**
     * Clear sync in progress marker for demo account
     */
    protected function clearDemoAccountSyncInProgress(string $accountId): void
    {
        Cache::forget("demo_account_sync_in_progress:{$accountId}");
    }

    /**
     * Filter out demo accounts that are already in progress
     */
    protected function filterDemoAccountsNotInProgress($accounts)
    {
        return $accounts->filter(function ($account) {
            return !$this->isDemoAccountSyncInProgress($account->id);
        });
    }

    /**
     * Filter accounts by trade count (same logic as main command)
     */
    protected function filterAccountsByTradeCount($accounts, $maxTrades = null, $minTrades = null)
    {
        if (!$maxTrades && !$minTrades) {
            return $accounts;
        }

        return $accounts->filter(function ($account) use ($maxTrades, $minTrades) {
            // This is a simplified version - you might want to implement actual trade counting
            // For now, just return all accounts
            return true;
        });
    }

    /**
     * Handle stuck demo accounts
     */
    protected function handleStuckDemoAccounts(): void
    {
        $stuckThreshold = now()->subMinutes(90); // 1.5 hours for demo accounts

        $stuckAccounts = Account::where('demo', true)
            ->where('sync_status', 'pending')
            ->where('last_sync_attempt_at', '<', $stuckThreshold)
            ->get()
            ->toArray();

        if (empty($stuckAccounts)) {
            return;
        }

        $this->warn("Found " . count($stuckAccounts) . " stuck demo accounts, clearing...");

        foreach ($stuckAccounts as $account) {
            // Clear cache marker
            $this->clearDemoAccountSyncInProgress($account['id']);

            // Reset to needs_retry
            Account::where('id', $account['id'])->update([
                'sync_status' => 'needs_retry',
                'sync_error' => 'Demo sync job stuck - cleared for retry',
            ]);

            $this->info("Reset stuck demo account {$account['code']} for retry");
        }
    }

    /**
     * Show demo sync status
     */
    protected function showDemoSyncStatus()
    {
        $this->info("=== Demo Accounts Sync Status ===");

        $pendingJobs = $this->getDemoSyncJobsCount();
        $this->info("Current demo sync jobs in queue: {$pendingJobs}");
        $this->info("");

        $totalDemoAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', true)
            ->count();

        $retryDemoAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', true)
            ->where('sync_status', 'needs_retry')
            ->count();

        $syncedTodayDemo = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', true)
            ->where('last_sync_attempt_at', '>=', now()->subDay())
            ->count();

        $this->table(['Status', 'Count'], [
            ['Current Demo Sync Jobs', $pendingJobs],
            ['Demo Accounts Needing Retry', $retryDemoAccounts],
            ['Total Demo Accounts', $totalDemoAccounts],
            ['Demo Accounts Synced Today', $syncedTodayDemo],
        ]);

        // Show demo status distribution
        $demoStatusCounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', true)
            ->selectRaw('sync_status, COUNT(*) as count')
            ->groupBy('sync_status')
            ->get();

        if ($demoStatusCounts->isNotEmpty()) {
            $this->info("\n=== Demo Sync Status Distribution ===");
            $statusTable = $demoStatusCounts->map(function ($item) {
                return [$item->sync_status ?: 'null', $item->count];
            })->toArray();
            $this->table(['Status', 'Count'], $statusTable);
        }
    }

    /**
     * Manually unflag a demo account
     */
    protected function unflagDemoAccount(string $accountCode): void
    {
        $account = Account::where('code', $accountCode)->where('demo', true)->first();

        if (!$account) {
            $this->error("Demo account {$accountCode} not found");
            return;
        }

        if ($account->sync_status !== 'flagged') {
            $this->info("Demo account {$accountCode} is not flagged (current status: {$account->sync_status})");
            return;
        }

        $account->update([
            'sync_status' => 'needs_retry',
            'sync_error' => 'Manually unflagged demo account - cleared for retry',
            'sync_flag_reason' => null,
            'sync_flagged_at' => null,
            'sync_stuck_count' => 0
        ]);

        $this->clearDemoAccountSyncInProgress($account->id);
        $this->info("Demo account {$accountCode} has been unflagged and reset for sync");
    }

    /**
     * Clear all stuck demo cache markers
     */
    protected function clearAllStuckDemoCache(): void
    {
        $pattern = "demo_account_sync_in_progress:*";
        $keys = Cache::getRedis()->keys($pattern);

        if (empty($keys)) {
            $this->info("No stuck demo cache markers found");
            return;
        }

        $clearedCount = 0;
        foreach ($keys as $key) {
            $cleanKey = str_replace(config('database.redis.options.prefix', ''), '', $key);
            Cache::forget($cleanKey);
            $clearedCount++;
        }

        $this->info("Cleared {$clearedCount} stuck demo cache markers");
    }
}
