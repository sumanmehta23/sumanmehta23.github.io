<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\BalanceSyncService;
use App\Jobs\BatchBalanceSyncJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;

class SyncAccountBalances extends Command
{
    protected $signature = 'app:sync-account-balances
                            {--accounts= : Comma-separated list of account codes to sync}
                            {--force : Force sync even if recently synced}
                            {--daemon : Run continuously}
                            {--interval= : Interval in minutes for daemon mode (default from config)}
                            {--batch : Use batch jobs for processing}
                            {--batch-size= : Number of accounts per batch job (default from config)}';

    protected $description = 'Sync account balances and equity from MT5 for accounts (supports batch jobs)';

    protected $balanceSyncService;

    public function __construct(BalanceSyncService $balanceSyncService)
    {
        parent::__construct();
        $this->balanceSyncService = $balanceSyncService;
    }

    public function handle()
    {
        $specificAccounts = $this->option('accounts');
        $forceSync = $this->option('force');
        $isDaemon = $this->option('daemon');
        $useBatch = $this->option('batch');
        $interval = (int) ($this->option('interval') ?: config('sync-all-trades.balance_sync.interval_minutes', 20));

        if ($isDaemon) {
            $this->runAsDaemon($interval, $specificAccounts, $forceSync, $useBatch);
        } else {
            $this->runSingleSync($specificAccounts, $forceSync, $useBatch, $interval);
        }

        return 0;
    }

    private function runSingleSync(?string $specificAccounts, bool $forceSync, bool $useBatch = false, int $interval = 20): void
    {
        $this->info('🔄 Starting Account Balance Sync');
        $this->line('================================');

        $accountCodes = null;
        if ($specificAccounts) {
            $accountCodes = array_map('trim', explode(',', $specificAccounts));
            $this->info('Syncing specific accounts: ' . implode(', ', $accountCodes));
        } else {
            $this->info('Syncing all eligible non-competition accounts');
        }

        if ($forceSync) {
            $this->warn('Force sync enabled - ignoring last sync time');
        }

        if ($useBatch) {
            $this->info('Using batch job processing');
        }

        // Show exclusion information
        $this->showExclusionInfo($accountCodes);

        $this->newLine();

        try {
            $startTime = microtime(true);
            $dynamicInterval = $this->calculateIntelligentSyncInterval($interval);

            if ($useBatch) {
                $results = $this->processBatchJobs($accountCodes, $forceSync, $interval);
            } else {
                $results = $this->balanceSyncService->syncAccountBalances($accountCodes, $forceSync, $dynamicInterval);
            }

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->displayResults($results, $duration);
        } catch (\Exception $e) {
            $this->error('❌ Balance sync failed: ' . $e->getMessage());
            Log::error('Balance sync command failed: ' . $e->getMessage());
        }
    }

    private function runAsDaemon(int $interval, ?string $specificAccounts, bool $forceSync, bool $useBatch = false): void
    {
        $this->info("🔄 Starting Balance Sync Daemon (every {$interval} minutes)");
        $this->line('===============================================');
        $this->info('Press Ctrl+C to stop');
        $this->newLine();

        $accountCodes = null;
        if ($specificAccounts) {
            $accountCodes = array_map('trim', explode(',', $specificAccounts));
            $this->info('Monitoring specific accounts: ' . implode(', ', $accountCodes));
        }

        $iteration = 0;

        while (true) {
            $iteration++;
            $this->info("[{$iteration}] " . now()->format('Y-m-d H:i:s') . " - Starting balance sync cycle");

            // Show exclusion info for daemon mode
            if ($iteration === 1) {
                $this->showExclusionInfo($accountCodes, true);
            }

            try {
                $startTime = microtime(true);
                $dynamicInterval = $this->calculateIntelligentSyncInterval($interval);

                if ($useBatch) {
                    $results = $this->processBatchJobs($accountCodes, $forceSync, $interval);
                } else {
                    $results = $this->balanceSyncService->syncAccountBalances($accountCodes, $forceSync, $dynamicInterval);
                }

                $duration = round((microtime(true) - $startTime) * 1000, 2);

                $this->displayResults($results, $duration, true);
            } catch (\Exception $e) {
                $this->error("[{$iteration}] ❌ Balance sync failed: " . $e->getMessage());
                Log::error("Balance sync daemon iteration {$iteration} failed: " . $e->getMessage());
            }

            $this->info("[{$iteration}] Waiting {$interval} minutes until next sync...");
            $this->newLine();

            // Sleep for the specified interval
            sleep($interval * 60);
        }
    }

    private function displayResults(array $results, float $duration, bool $isDaemon = false): void
    {
        if (!$isDaemon) {
            $this->newLine();
            $this->info('📊 Balance Sync Results:');
        }

        $avgPerAccount = $results['processed'] > 0 ?
            round($duration / $results['processed'], 2) : 0;

        $tableData = [
            ['Accounts Processed', $results['processed']],
            ['Balances Updated', $results['updated']],
            ['No Change', $results['no_change']],
            ['Errors', $results['errors']],
            ['Not Found', $results['not_found']],
            ['Duration', $duration . 'ms'],
            ['Avg per Account', $avgPerAccount . 'ms']
        ];

        // Add batch-specific information if available
        if (isset($results['batch_id'])) {
            $tableData[] = ['Batch ID', $results['batch_id']];
            $tableData[] = ['Jobs Dispatched', $results['jobs_dispatched']];
        }

        $this->table(['Metric', 'Count'], $tableData);

        // Performance analysis
        if ($results['processed'] > 0) {
            $successRate = round((($results['processed'] - $results['errors']) / $results['processed']) * 100, 2);

            // For batch jobs, we can't calculate update rate immediately
            if (!isset($results['batch_id'])) {
                $updateRate = round(($results['updated'] / $results['processed']) * 100, 2);
                $this->line("💰 Balance change rate: {$updateRate}%");
            }

            if ($successRate >= 95) {
                $this->info("✅ Success rate: {$successRate}%");
            } else {
                $this->warn("⚠️  Success rate: {$successRate}%");
            }

            if ($avgPerAccount > 200 && !isset($results['batch_id'])) {
                $this->warn("⚠️  Average time per account ({$avgPerAccount}ms) is high");
            }
        }

        if (!$isDaemon) {
            $this->newLine();
            if (isset($results['batch_id'])) {
                $this->info("✅ Batch jobs dispatched! Monitor progress with: php artisan queue:batches");
            } elseif ($results['updated'] > 0) {
                $this->info("✅ Balance sync completed! {$results['updated']} accounts had balance changes.");
            } else {
                $this->info("✅ Balance sync completed! No balance changes detected.");
            }
        }
    }

    /**
     * Process balance sync using batch jobs
     */
    private function processBatchJobs(?array $accountCodes, bool $forceSync, int $interval = 20): array
    {
        $batchSize = (int) ($this->option('batch-size') ?: config('sync-all-trades.balance_sync.batch_size', 20));

        $this->info("Processing with batch size: {$batchSize}");

        // Get accounts to sync using similar logic as BalanceSyncService
        $query = Account::whereNotNull('code')
            ->where('demo', true)
            // ->whereRaw("( (competition_product_id IS NULL AND demo = 0)
                    // OR (competition_product_id IS NOT NULL AND demo = 1) )")
            ->whereNotIn('sync_status', ['not_found_in_mt5']);

        if ($accountCodes) {
            $query->whereIn('code', $accountCodes);
        }

        if (!$forceSync) {
            // INTELLIGENT SYNC INTERVAL: Use dynamic interval based on system configuration and activity
            $dynamicInterval = $this->calculateIntelligentSyncInterval($interval);

            $this->line("Using intelligent sync interval: {$dynamicInterval} minutes (daemon: {$interval}min)");

            // Only sync accounts that haven't been synced within the intelligent interval
            $query->where(function ($q) use ($dynamicInterval) {
                $q->whereNull('last_balance_sync_at')
                    ->orWhere('last_balance_sync_at', '<', now()->subMinutes($dynamicInterval));
            });
        }

        $accounts = $query->orderBy('last_balance_sync_at', 'asc')
            ->get();

        if ($accounts->isEmpty()) {
            $this->info("No accounts require balance sync");
            return [
                'processed' => 0,
                'updated' => 0,
                'no_change' => 0,
                'errors' => 0,
                'not_found' => 0
            ];
        }

        $this->info("Found {$accounts->count()} accounts for batch processing");

        // Split accounts into batches
        $accountBatches = $accounts->chunk($batchSize);
        $jobs = [];

        foreach ($accountBatches as $batchIndex => $accountBatch) {
            $accountIds = $accountBatch->pluck('id')->toArray();
            $jobs[] = new BatchBalanceSyncJob($accountIds, $forceSync);

            $this->line("  Batch " . ($batchIndex + 1) . ": " . count($accountIds) . " accounts");
        }

        if (empty($jobs)) {
            return [
                'processed' => 0,
                'updated' => 0,
                'no_change' => 0,
                'errors' => 0,
                'not_found' => 0
            ];
        }

        $this->info("Dispatching " . count($jobs) . " batch jobs to queue...");

        // Dispatch all jobs as a batch
        $batch = Bus::batch($jobs)
            ->then(function () {
                Log::info('BatchBalanceSyncJob: All balance sync batches completed successfully');
            })
            ->catch(function (\Throwable $e) {
                Log::error('BatchBalanceSyncJob: One or more balance sync batches failed', [
                    'error' => $e->getMessage()
                ]);
            })
            ->onQueue('account-sync')  // Explicitly set the queue for the batch
            ->dispatch();

        $this->info("✅ Dispatched batch with ID: {$batch->id}");
        $this->line("Monitor progress with: php artisan queue:batches");

        // Return summary stats for immediate feedback
        return [
            'processed' => $accounts->count(),
            'updated' => 0, // Will be updated by jobs
            'no_change' => 0,
            'errors' => 0,
            'not_found' => 0,
            'batch_id' => $batch->id,
            'jobs_dispatched' => count($jobs)
        ];
    }

    private function showExclusionInfo(?array $accountCodes, bool $isDaemon = false): void
    {
        $excludedCount = Account::whereNotNull('code')
            ->where('demo', true)
            // ->whereRaw("( (competition_product_id IS NULL AND demo = 0)
                    // OR (competition_product_id IS NOT NULL AND demo = 1) )")
            ->whereIn('sync_status', ['not_found_in_mt5'])
            ->count();

        if ($excludedCount > 0) {
            if (!$isDaemon) {
                $this->warn("⚠️  {$excludedCount} accounts excluded (marked as not_found_in_mt5)");
                $this->line("💡 Use 'php artisan app:manage-not-found-accounts --stats' for details");
            } else {
                $this->line("[INFO] {$excludedCount} accounts excluded from sync (not_found_in_mt5)");
            }
        } elseif (!$isDaemon) {
            $this->info("✅ All eligible accounts included in sync");
        }
    }

    /**
     * Calculate intelligent sync interval based on system activity and configuration
     *
     * This dynamically determines the optimal interval for balance sync filtering,
     * ensuring accounts get updated as soon as possible while avoiding unnecessary work.
     */
    private function calculateIntelligentSyncInterval(int $daemonInterval): int
    {
        // Base interval: Use daemon interval with a small buffer to ensure we don't miss accounts
        $baseInterval = max(1, $daemonInterval - 2); // Minimum 1 minute, with 2-minute grace period

        // ADAPTIVE FACTORS:

        // 1. Time of day consideration (market hours = more frequent updates)
        $currentHour = now()->hour;
        $isMarketHours = ($currentHour >= 8 && $currentHour <= 18); // Approximate market hours

        if ($isMarketHours) {
            $baseInterval = max(1, intval($baseInterval * 0.7)); // 30% more frequent during market hours
        }

        // 2. Recent system activity (if many accounts had recent balance changes, sync more frequently)
        $recentActivityFactor = $this->calculateRecentActivityFactor();
        $baseInterval = max(1, intval($baseInterval * $recentActivityFactor));

        // 3. Queue load consideration (if queue is busy, be less aggressive)
        $queueLoadFactor = $this->calculateQueueLoadFactor();
        $baseInterval = max(1, intval($baseInterval * $queueLoadFactor));

        // 4. Ensure minimum responsiveness (never go below 2 minutes for safety)
        $intelligentInterval = max(2, min($baseInterval, $daemonInterval));

        Log::info("Balance sync intelligent interval calculated", [
            'daemon_interval' => $daemonInterval,
            'base_interval' => $baseInterval,
            'market_hours' => $isMarketHours,
            'activity_factor' => $recentActivityFactor,
            'queue_load_factor' => $queueLoadFactor,
            'final_interval' => $intelligentInterval
        ]);

        return $intelligentInterval;
    }

    /**
     * Calculate activity factor based on recent balance changes
     * Returns multiplier: < 1.0 = more frequent sync, > 1.0 = less frequent sync
     */
    private function calculateRecentActivityFactor(): float
    {
        try {
            // Count accounts with balance changes in the last hour
            $recentBalanceChanges = Account::where('last_balance_sync_at', '>', now()->subHour())
                ->where('updated_at', '>', now()->subHour()) // Indicates recent changes
                ->count();

            $totalActiveAccounts = Account::whereNotNull('code')
                ->where('demo', true)
                // ->whereRaw("( (competition_product_id IS NULL AND demo = 0)
                    // OR (competition_product_id IS NOT NULL AND demo = 1) )")
                ->whereNotIn('sync_status', ['not_found_in_mt5'])
                ->count();

            if ($totalActiveAccounts == 0) return 1.0;

            $activityRate = $recentBalanceChanges / $totalActiveAccounts;

            // High activity = sync more frequently
            if ($activityRate > 0.1) return 0.6; // 40% more frequent
            if ($activityRate > 0.05) return 0.8; // 20% more frequent
            if ($activityRate < 0.01) return 1.3; // 30% less frequent

            return 1.0; // Normal frequency
        } catch (\Exception $e) {
            Log::warning("Failed to calculate activity factor: " . $e->getMessage());
            return 1.0; // Safe fallback
        }
    }

    /**
     * Calculate queue load factor based on pending jobs
     * Returns multiplier: > 1.0 = less frequent sync when queue is busy
     */
    private function calculateQueueLoadFactor(): float
    {
        try {
            // Check if we can determine queue size (implementation varies by queue driver)
            // For now, use a simple time-based heuristic

            $currentMinute = now()->minute;

            // Assume higher load during "round" times when cron jobs typically run
            if ($currentMinute % 5 == 0) return 1.2; // 20% less frequent on 5-minute marks
            if ($currentMinute % 10 == 0) return 1.4; // 40% less frequent on 10-minute marks

            return 1.0; // Normal frequency
        } catch (\Exception $e) {
            Log::warning("Failed to calculate queue load factor: " . $e->getMessage());
            return 1.0; // Safe fallback
        }
    }
}
