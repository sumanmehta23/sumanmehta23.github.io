<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Jobs\BatchSyncTradesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Trade Count Range Sync Command
 * 
 * Sync accounts based on their pending trade count ranges
 * Useful for processing accounts in manageable chunks
 */
class TradeCountRangeSyncCommand extends Command
{
    protected $signature = 'app:sync-by-trade-count 
                            {min-trades : Minimum trade count}
                            {max-trades : Maximum trade count}
                            {--accounts= : Comma-separated account codes to filter}
                            {--batch-size=10 : Number of accounts per batch}
                            {--dry-run : Show what would be synced without actually dispatching}
                            {--estimate : Estimate trade counts for all accounts (slower)}';

    protected $description = 'Sync accounts with pending trades in a specific count range (e.g., 200-500 trades)';

    public function handle()
    {
        $minTrades = (int) $this->argument('min-trades');
        $maxTrades = (int) $this->argument('max-trades');
        $accountCodes = $this->option('accounts');
        $batchSize = (int) $this->option('batch-size');
        $dryRun = $this->option('dry-run');
        $estimate = $this->option('estimate');

        if ($minTrades >= $maxTrades) {
            $this->error("Min trades ({$minTrades}) must be less than max trades ({$maxTrades})");
            return 1;
        }

        $this->info("=== Trade Count Range Sync ===");
        $this->info("Range: {$minTrades} to {$maxTrades} pending trades");

        if ($estimate) {
            $this->warn("Estimate mode: This will connect to MT5 to count trades for each account (slower)");
            if (!$this->confirm('Continue with trade count estimation?')) {
                return 0;
            }

            return $this->estimateAndSync($minTrades, $maxTrades, $accountCodes, $batchSize, $dryRun);
        }

        // Quick mode: Use accounts that were previously skipped for high volume
        $query = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->where('sync_status', 'skipped_high_volume');

        if ($accountCodes) {
            $codes = array_map('trim', explode(',', $accountCodes));
            $query->whereIn('code', $codes);
        }

        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            $this->info("No accounts found with 'skipped_high_volume' status.");
            $this->info("Use --estimate to check all accounts (slower but more accurate).");
            return 0;
        }

        return $this->processAccounts($accounts, $minTrades, $maxTrades, $batchSize, $dryRun);
    }

    protected function estimateAndSync($minTrades, $maxTrades, $accountCodes, $batchSize, $dryRun)
    {
        // This would require MT5 connection to estimate trade counts
        // For now, show the concept
        $this->warn("Trade count estimation requires MT5 connection and is not yet implemented.");
        $this->info("Suggested workflow:");
        $this->info("1. Run regular sync with --max-trades={$minTrades} to skip high-volume accounts");
        $this->info("2. Check logs for skipped accounts with their trade counts");
        $this->info("3. Use specific account codes: --accounts=123,456,789");

        return 0;
    }

    protected function processAccounts($accounts, $minTrades, $maxTrades, $batchSize, $dryRun)
    {
        $this->info("Found " . $accounts->count() . " accounts for trade count range sync");

        if ($dryRun) {
            $this->warn("DRY RUN MODE - Jobs will not be dispatched");
            $this->table(
                ['Account', 'Last Sync', 'Status', 'Error'],
                $accounts->map(function ($account) {
                    return [
                        $account->code,
                        $account->last_sync_attempt_at ? $account->last_sync_attempt_at->format('Y-m-d H:i') : 'Never',
                        $account->sync_status ?: 'null',
                        substr($account->sync_error ?: '', 0, 50)
                    ];
                })->toArray()
            );
            return 0;
        }

        if (!$this->confirm("Dispatch sync jobs for " . $accounts->count() . " accounts in range {$minTrades}-{$maxTrades} trades?")) {
            return 0;
        }

        $accountBatches = $accounts->chunk($batchSize);
        $processedCount = 0;

        foreach ($accountBatches as $batchIndex => $accountBatch) {
            $batchAccounts = $accountBatch->values()->all();
            $batchSyncTimes = [];

            foreach ($accountBatch as $account) {
                $lastSync = $account->last_balance_sync_at;
                $batchSyncTimes[] = $lastSync ? Carbon::parse($lastSync) : now()->subDays(7);
            }

            $accountCodes = $accountBatch->pluck('code')->join(', ');
            $this->info("Dispatching range batch " . ($batchIndex + 1) . ": {$accountCodes}");

            Account::whereIn('id', $accountBatch->pluck('id'))
                ->update([
                    'last_sync_attempt_at' => now(),
                    'sync_status' => 'pending'
                ]);

            // Create job with specific trade count range
            $batchJob = new BatchSyncTradesJob($batchAccounts, $batchSyncTimes, $maxTrades, $minTrades);
            dispatch($batchJob)->onQueue('trade-range-sync');

            $processedCount += count($batchAccounts);

            if ($batchIndex < $accountBatches->count() - 1) {
                sleep(3);
            }
        }

        $this->info("✅ Dispatched {$processedCount} accounts for trade range {$minTrades}-{$maxTrades}");
        return 0;
    }
}
