<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Jobs\BatchSyncTradesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * High-Volume Account Sync Command
 * 
 * Specialized command for syncing accounts with large numbers of trades (>200)
 * Uses larger batch sizes and extended timeouts for optimal performance
 */
class HighVolumeSyncCommand extends Command
{
    protected $signature = 'app:high-volume-sync 
                            {--accounts= : Comma-separated account codes to sync}
                            {--batch-size=5 : Number of accounts per batch (smaller for high-volume)}
                            {--min-trades=200 : Minimum trade count to qualify as high-volume}
                            {--max-trades=1000 : Maximum trade count limit (skip if exceeded)}
                            {--timeout=1800 : Job timeout in seconds (30 minutes)}
                            {--dry-run : Show what would be synced without actually dispatching}
                            {--force : Skip confirmations}';

    protected $description = 'Sync high-volume accounts with large numbers of trades (>200)';

    public function handle()
    {
        $accountCodes = $this->option('accounts');
        $batchSize = (int) $this->option('batch-size');
        $minTrades = (int) $this->option('min-trades');
        $maxTrades = (int) $this->option('max-trades');
        $timeout = (int) $this->option('timeout');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info("=== High-Volume Account Sync ===");
        $this->info("Target: Accounts with {$minTrades}-{$maxTrades} pending trades");
        $this->info("Batch size: {$batchSize} accounts");
        $this->info("Timeout: {$timeout}s per job");

        // Get accounts to sync
        $query = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->where(function ($query) {
                $query->whereNull('sync_status')
                    ->orWhereNotIn('sync_status', ['not_found_in_mt5', 'flagged']);
            });

        if ($accountCodes) {
            $codes = array_map('trim', explode(',', $accountCodes));
            $query->whereIn('code', $codes);
            $this->info("Filtering to specific accounts: " . implode(', ', $codes));
        } else {
            // Auto-detect high-volume accounts that need sync
            $query->where(function ($q) {
                $q->whereNull('last_sync_attempt_at')
                    ->orWhere('last_sync_attempt_at', '<', now()->subHours(6))
                    ->orWhere('sync_status', 'needs_retry')
                    ->orWhere('sync_status', 'skipped_high_volume');
            });
        }

        $accounts = $query->orderBy('last_sync_attempt_at', 'asc')->get();

        if ($accounts->isEmpty()) {
            $this->info("No high-volume accounts found needing sync.");
            return 0;
        }

        $this->info("Found " . $accounts->count() . " accounts for high-volume sync");

        if ($dryRun) {
            $this->warn("DRY RUN MODE - Jobs will not be dispatched");
            $this->table(
                ['Account', 'Last Sync', 'Status'],
                $accounts->map(function ($account) {
                    return [
                        $account->code,
                        $account->last_sync_attempt_at ? $account->last_sync_attempt_at->format('Y-m-d H:i') : 'Never',
                        $account->sync_status ?: 'null'
                    ];
                })->toArray()
            );
            return 0;
        }

        if (!$force) {
            $this->warn("This will dispatch high-volume sync jobs for " . $accounts->count() . " accounts.");
            $this->warn("Each job may take up to " . ($timeout / 60) . " minutes to complete.");

            if (!$this->confirm('Continue with high-volume sync?')) {
                $this->info('High-volume sync cancelled.');
                return 0;
            }
        }

        // Process in smaller batches for high-volume accounts
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
            $this->info("Dispatching high-volume batch " . ($batchIndex + 1) . "/" . $accountBatches->count() . ": {$accountCodes}");

            // Update sync attempt timestamp
            Account::whereIn('id', $accountBatch->pluck('id'))
                ->update([
                    'last_sync_attempt_at' => now(),
                    'sync_status' => 'pending'
                ]);

            // Create high-volume job with trade limits and extended timeout
            $batchJob = new BatchSyncTradesJob($batchAccounts, $batchSyncTimes, $maxTrades, $minTrades);
            $batchJob->timeout = $timeout; // Extended timeout for high-volume

            dispatch($batchJob)->onQueue('high-volume-sync');

            $processedCount += count($batchAccounts);

            // Longer delay between high-volume batches
            if ($batchIndex < $accountBatches->count() - 1) {
                $this->info("Waiting 10 seconds before next batch...");
                sleep(10);
            }
        }

        $this->info("✅ Dispatched {$processedCount} high-volume accounts in " . $accountBatches->count() . " batches");
        $this->info("Monitor progress with: php artisan queue:work high-volume-sync --timeout={$timeout}");

        return 0;
    }
}
