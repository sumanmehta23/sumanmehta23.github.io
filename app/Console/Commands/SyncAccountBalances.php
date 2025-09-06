<?php

namespace App\Console\Commands;

use App\Services\BalanceSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAccountBalances extends Command
{
    protected $signature = 'app:sync-account-balances 
                            {--accounts= : Comma-separated list of account codes to sync}
                            {--force : Force sync even if recently synced}
                            {--daemon : Run continuously}
                            {--interval= : Interval in minutes for daemon mode (default from config)}';

    protected $description = 'Sync account balances and equity from MT5 for  accounts';

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
        $interval = (int) ($this->option('interval') ?: config('sync-all-trades.balance_sync.interval_minutes', 20));

        if ($isDaemon) {
            $this->runAsDaemon($interval, $specificAccounts, $forceSync);
        } else {
            $this->runSingleSync($specificAccounts, $forceSync);
        }

        return 0;
    }

    private function runSingleSync(?string $specificAccounts, bool $forceSync): void
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

        $this->newLine();

        try {
            $startTime = microtime(true);
            $results = $this->balanceSyncService->syncAccountBalances($accountCodes, $forceSync);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->displayResults($results, $duration);
        } catch (\Exception $e) {
            $this->error('❌ Balance sync failed: ' . $e->getMessage());
            Log::error('Balance sync command failed: ' . $e->getMessage());
        }
    }

    private function runAsDaemon(int $interval, ?string $specificAccounts, bool $forceSync): void
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

            try {
                $startTime = microtime(true);
                $results = $this->balanceSyncService->syncAccountBalances($accountCodes, $forceSync);
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

        $this->table(
            ['Metric', 'Count'],
            [
                ['Accounts Processed', $results['processed']],
                ['Balances Updated', $results['updated']],
                ['No Change', $results['no_change']],
                ['Errors', $results['errors']],
                ['Not Found', $results['not_found']],
                ['Duration', $duration . 'ms'],
                ['Avg per Account', $avgPerAccount . 'ms']
            ]
        );

        // Performance analysis
        if ($results['processed'] > 0) {
            $successRate = round((($results['processed'] - $results['errors']) / $results['processed']) * 100, 2);
            $updateRate = round(($results['updated'] / $results['processed']) * 100, 2);

            if ($successRate >= 95) {
                $this->info("✅ Success rate: {$successRate}%");
            } else {
                $this->warn("⚠️  Success rate: {$successRate}%");
            }

            $this->line("💰 Balance change rate: {$updateRate}%");

            if ($avgPerAccount > 200) {
                $this->warn("⚠️  Average time per account ({$avgPerAccount}ms) is high");
            }
        }

        if (!$isDaemon) {
            $this->newLine();
            if ($results['updated'] > 0) {
                $this->info("✅ Balance sync completed! {$results['updated']} accounts had balance changes.");
            } else {
                $this->info("✅ Balance sync completed! No balance changes detected.");
            }
        }
    }
}
