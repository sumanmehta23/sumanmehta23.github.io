<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Jobs\BatchSyncTradesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
                            {--daemon : Run continuously as daemon}
                            {--status : Show current sync status}';

    protected $description = 'Continuously sync accounts prioritizing those not synced recently';

    public function handle()
    {
        $batchSize = (int) $this->option('batch-size');
        $maxConcurrent = (int) $this->option('max-concurrent');
        $cycleDelay = (int) $this->option('cycle-delay');
        $minSyncInterval = (int) $this->option('min-sync-interval');
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
        $this->info("- Min sync interval: {$minSyncInterval}h");

        if ($isDaemon) {
            $this->runDaemonMode($batchSize, $maxConcurrent, $cycleDelay, $minSyncInterval);
        } else {
            $this->runSingleCycle($batchSize, $maxConcurrent, $minSyncInterval);
        }
    }

    protected function showSyncStatus()
    {
        $this->info("=== Account Sync Status Overview ===");

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

        $this->table(['Status', 'Count'], [
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

    protected function runDaemonMode($batchSize, $maxConcurrent, $cycleDelay, $minSyncInterval)
    {
        $this->info("Running in daemon mode. Press Ctrl+C to stop.");

        $cycleCount = 0;
        while (true) {
            try {
                $cycleCount++;
                $this->info("=== Sync Cycle #{$cycleCount} at " . now()->format('Y-m-d H:i:s') . " ===");

                $processed = $this->runSingleCycle($batchSize, $maxConcurrent, $minSyncInterval);

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

    protected function runSingleCycle($batchSize, $maxConcurrent, $minSyncInterval): int
    {
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
                // Include accounts that have never been synced OR last sync was before cutoff
                $q->whereNull('last_sync_attempt_at')
                    ->orWhere('last_sync_attempt_at', '<', $cutoffTime);
            })
            // Priority order: NULL sync attempts first, then oldest attempts
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
        $neverSynced = $accounts->whereNull('last_sync_attempt_at')->count();
        $staleSynced = $accounts->whereNotNull('last_sync_attempt_at')->count();
        $this->info("Priority: {$neverSynced} never synced, {$staleSynced} stale");

        // Process in batches
        $accountBatches = $accounts->chunk($batchSize);
        $processedCount = 0;

        foreach ($accountBatches as $batchIndex => $accountBatch) {
            $batchAccounts = $accountBatch->values()->all();
            $batchSyncTimes = [];

            // Calculate sync times for each account
            foreach ($accountBatch as $account) {
                $lastSync = $account->last_balance_sync_at;
                $batchSyncTimes[] = $lastSync ? Carbon::parse($lastSync) : now()->subDays(7);
            }

            $accountCodes = $accountBatch->pluck('code')->join(', ');
            $this->info("Dispatching batch " . ($batchIndex + 1) . " with " . count($batchAccounts) . " accounts: {$accountCodes}");

            // Update sync attempt timestamp immediately to prevent duplicate processing
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
