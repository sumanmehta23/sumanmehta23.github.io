<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Jobs\BatchSyncTradesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;

/**
 * Priority-Based Account Sync Command
 * 
 * This command continuously syncs accounts based on when they were last synced:
 * 1. Accounts that have never been synced (highest priority)
 * 2. Accounts with oldest sync attempts
 * 3. Configurable sync intervals to avoid overwhelming the system
 * 4. Intelligent batching for optimal performance
 */
class PrioritySyncAccountsCommand extends Command
{
    protected $signature = 'app:priority-sync 
                            {--batch-size=10 : Number of accounts per batch}
                            {--max-concurrent=5 : Maximum concurrent batches}
                            {--cycle-delay=30 : Delay between sync cycles in seconds}
                            {--min-sync-interval=60 : Minimum minutes between syncs for same account}
                            {--max-pending-jobs=100 : Maximum pending BatchSyncTradesJob jobs allowed}
                            {--daemon : Run continuously as daemon}
                            {--status : Show current sync status}';

    protected $description = 'Continuously sync accounts prioritizing those not synced recently';

    public function handle()
    {
        $batchSize = (int) $this->option('batch-size');
        $maxConcurrent = (int) $this->option('max-concurrent');
        $cycleDelay = (int) $this->option('cycle-delay');
        $minSyncInterval = (int) $this->option('min-sync-interval');
        $maxPendingJobs = (int) $this->option('max-pending-jobs');
        $isDaemon = $this->option('daemon');
        $showStatus = $this->option('status');

        if ($showStatus) {
            $this->showSyncStatus();
            return;
        }

        $this->info("Starting priority-based sync with:");
        $this->info("- Batch size: {$batchSize}");
        $this->info("- Max concurrent: {$maxConcurrent}");
        $this->info("- Cycle delay: {$cycleDelay}s");
        $this->info("- Min sync interval: {$minSyncInterval}m");
        $this->info("- Max pending jobs: {$maxPendingJobs}");

        if ($isDaemon) {
            $this->runDaemonMode($batchSize, $maxConcurrent, $cycleDelay, $minSyncInterval, $maxPendingJobs);
        } else {
            $this->runSingleCycle($batchSize, $maxConcurrent, $minSyncInterval, $maxPendingJobs);
        }
    }

    /**
     * Get the number of pending BatchSyncTradesJob jobs in the queue
     */
    protected function getPendingJobsCount(): int
    {
        try {
            // For Redis queue - check the queue size
            $queueName = 'queues:optimized-sync-trades';
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

        $totalAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->where(function ($q) {
                $q->whereNull('competition_start_date')
                    ->orWhereNull('competition_end_date')
                    ->orWhereNull('competition_status')
                    ->orWhere('competition_status', '!=', 'active');
            })
            ->count();

        $neverSynced = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->whereNull('last_sync_attempt_at')
            ->count();

        $syncedToday = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->where('last_sync_attempt_at', '>=', now()->subDay())
            ->count();

        $stale6h = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->whereNotNull('last_sync_attempt_at')
            ->where('last_sync_attempt_at', '<', now()->subHours(6))
            ->count();

        $stale24h = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->whereNotNull('last_sync_attempt_at')
            ->where('last_sync_attempt_at', '<', now()->subDay())
            ->count();

        $retryAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->where('sync_status', 'needs_retry')
            ->count();

        $this->table(['Status', 'Count'], [
            ['Current Queue Jobs (BatchSyncTradesJob)', $pendingJobs],
            ['Needs Retry (Queue Limit Hit)', $retryAccounts],
            ['Total Eligible Accounts', $totalAccounts],
            ['Never Synced (Highest Priority)', $neverSynced],
            ['Synced Today', $syncedToday],
            ['Stale (>6 hours)', $stale6h],
            ['Very Stale (>24 hours)', $stale24h],
        ]);

        // Show recent sync status distribution
        $statusCounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
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

    protected function runDaemonMode($batchSize, $maxConcurrent, $cycleDelay, $minSyncInterval, $maxPendingJobs)
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

                $processed = $this->runSingleCycle($batchSize, $maxConcurrent, $minSyncInterval, $maxPendingJobs);

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

    protected function runSingleCycle($batchSize, $maxConcurrent, $minSyncInterval, $maxPendingJobs): int
    {
        // Check pending jobs first
        $pendingJobs = $this->getPendingJobsCount();
        $this->info("Current pending BatchSyncTradesJob jobs: {$pendingJobs}");

        if ($pendingJobs >= $maxPendingJobs) {
            $this->warn("Queue limit reached ({$pendingJobs}/{$maxPendingJobs}). Skipping sync cycle.");
            return 0;
        }

        // Get accounts that need syncing, prioritized by last sync attempt
        $cutoffTime = now()->subMinutes($minSyncInterval);

        $accounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->where(function ($q) {
                $q->whereNull('competition_start_date')
                    ->orWhereNull('competition_end_date')
                    ->orWhereNull('competition_status')
                    ->orWhere('competition_status', '!=', 'active');
            })
            ->where(function ($q) use ($cutoffTime) {
                // Include accounts that need syncing:
                $q->whereNull('last_sync_attempt_at')  // Never synced
                    ->orWhere('last_sync_attempt_at', '<', $cutoffTime)  // Old syncs
                    ->orWhere('sync_status', 'needs_retry');  // Failed due to queue limits
            })
            // Priority order: retry accounts first, then never synced, then oldest attempts
            ->orderByRaw("CASE WHEN sync_status = 'needs_retry' THEN 0 ELSE 1 END")
            ->orderByRaw('last_sync_attempt_at IS NULL DESC')
            ->orderBy('last_sync_attempt_at', 'asc')
            ->limit($batchSize * $maxConcurrent) // Get enough for all concurrent batches
            ->get();

        if ($accounts->isEmpty()) {
            $this->info("No accounts need syncing (all synced within {$minSyncInterval}h)");
            return 0;
        }

        $this->info("Found {$accounts->count()} accounts needing sync");

        // Show priority breakdown
        $retryAccounts = $accounts->where('sync_status', 'needs_retry')->count();
        $neverSynced = $accounts->whereNull('last_sync_attempt_at')->count();
        $staleSynced = $accounts->whereNotNull('last_sync_attempt_at')
            ->where('sync_status', '!=', 'needs_retry')->count();

        $this->info("Priority: {$retryAccounts} retry, {$neverSynced} never synced, {$staleSynced} stale");

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
                $lastSync = $account->last_balance_sync_at;
                $batchSyncTimes[] = $lastSync ? Carbon::parse($lastSync) : now()->subDays(7);
            }

            $accountCodes = $accountBatch->pluck('code')->join(', ');
            $this->info("Dispatching batch " . ($batchIndex + 1) . " with " . count($batchAccounts) . " accounts: {$accountCodes} (Queue: {$currentPendingJobs}/{$maxPendingJobs})");

            // Update sync attempt timestamp ONLY for accounts being dispatched
            Account::whereIn('id', $accountBatch->pluck('id'))
                ->update([
                    'last_sync_attempt_at' => now(),
                    'sync_status' => 'pending'
                ]);

            // Dispatch the batch job
            $batchJob = new BatchSyncTradesJob($batchAccounts, $batchSyncTimes);
            dispatch($batchJob)->onQueue('optimized-sync-trades');

            $processedCount += count($batchAccounts);

            // Small delay between batch dispatches to avoid overwhelming
            if ($batchIndex < $accountBatches->count() - 1) {
                sleep(2);
            }
        }

        $this->info("Dispatched {$processedCount} accounts in " . $accountBatches->count() . " batches");
        return $processedCount;
    }
}
