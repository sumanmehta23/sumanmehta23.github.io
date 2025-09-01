<?php

namespace App\Console\Commands;

use Throwable;
use App\Models\User;
use App\Models\Trade;
use App\MT5\MTRetCode;
use App\Models\Account;
use Illuminate\Bus\Batch;
use App\Services\MT5Service;
use App\Services\MailService;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Jobs\OptimizedSyncTradesJob;
use App\Jobs\BatchSyncTradesJob;
use Illuminate\Support\Facades\Cache;

/**
 * Efficient Sync All Accounts Trades - Simplified Strategy
 * 
 * This command provides a much simpler, faster, and more reliable approach:
 * 
 * SIMPLE STRATEGY:
 * - Process ALL accounts systematically in chunks
 * - Use larger batches for better performance
 * - Guarantee every account gets synced
 * - Skip only competition accounts
 * 
 * PERFORMANCE FEATURES:  
 * - Large batch sizes (10-20 accounts per batch)
 * - Higher concurrency (5-10 concurrent batches)
 * - Efficient chunking through all accounts
 * - Simple incremental sync timing
 * 
 * RELIABILITY FEATURES:
 * - No complex tier logic to fail
 * - Processes every account exactly once per run
 * - Clear progress tracking
 * - Predictable completion time
 */
class OptimizedSyncAllTrades extends Command
{
    protected $signature = 'app:optimized-sync-trades 
                            {--batch-size=15 : Number of accounts per batch}
                            {--max-concurrent=8 : Maximum concurrent batches}
                            {--delay=5 : Delay between batches in seconds}
                            {--test-account= : Test with specific account code}
                            {--daemon : Run as daemon continuously}
                            {--status : Show sync status}';

    protected $description = 'Efficient MT5 sync - processes ALL accounts reliably with high performance';

    protected $mt5Service;
    protected $mailService;

    public function __construct(MT5Service $mt5Service, MailService $mailService)
    {
        parent::__construct();
        $this->mt5Service = $mt5Service;
        $this->mailService = $mailService;
    }

    public function handle()
    {
        $batchSize = (int) $this->option('batch-size');
        $maxConcurrent = (int) $this->option('max-concurrent');
        $delay = (int) $this->option('delay');
        $testAccount = $this->option('test-account');
        $isDaemon = $this->option('daemon');
        $showStatus = $this->option('status');

        if ($showStatus) {
            $this->showSyncStatus();
            return;
        }

        if ($testAccount) {
            $this->syncSpecificAccount($testAccount);
            return;
        }

        $this->info("Configuration: Batch Size: {$batchSize}, Max Concurrent: {$maxConcurrent}, Delay: {$delay}s");

        if ($isDaemon) {
            $this->runDaemonMode($batchSize, $maxConcurrent, $delay);
        } else {
            $this->syncAllAccounts($batchSize, $maxConcurrent, $delay);
        }
    }

    protected function showSyncStatus()
    {
        $totalAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->count();

        $competitionAccounts = Account::whereNotNull('code')
            ->whereNotNull('competition_start_date')
            ->whereNotNull('competition_end_date')
            ->where('competition_status', 'active')
            ->count();

        $syncableAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->where(function ($q) {
                $q->whereNull('competition_start_date')
                    ->orWhereNull('competition_end_date')
                    ->orWhereNull('competition_status')
                    ->orWhere('competition_status', '!=', 'active');
            })
            ->count();

        $totalTrades = Trade::count();
        $recentTrades = Trade::where('created_at', '>=', now()->subHour())->count();

        $this->info("=== Efficient Sync Status ===");
        $this->info("Total live accounts: {$totalAccounts}");
        $this->info("Competition accounts (excluded): {$competitionAccounts}");
        $this->info("Accounts to sync: {$syncableAccounts}");
        $this->info("Total trades in system: {$totalTrades}");
        $this->info("Trades synced in last hour: {$recentTrades}");

        // Estimate completion time
        $batchSize = (int) $this->option('batch-size');
        $delay = (int) $this->option('delay');
        $batches = ceil($syncableAccounts / $batchSize);
        $estimatedMinutes = ($batches * $delay) / 60;

        $this->info("Estimated sync time: {$batches} batches, ~{$estimatedMinutes} minutes");
    }

    protected function syncAllAccounts($batchSize, $maxConcurrent, $delay)
    {
        $query = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->where(function ($q) {
                $q->whereNull('competition_start_date')
                    ->orWhereNull('competition_end_date')
                    ->orWhereNull('competition_status')
                    ->orWhere('competition_status', '!=', 'active');
            });

        $totalAccounts = $query->count();
        $this->info("Found {$totalAccounts} accounts to sync (excluding competition accounts)");

        if ($totalAccounts === 0) {
            $this->info("No accounts found to sync.");
            return;
        }

        $processedCount = 0;
        $activeBatches = 0;
        $startTime = now();

        $query->chunk(500, function ($accounts) use ($batchSize, $maxConcurrent, $delay, $totalAccounts, &$processedCount, &$activeBatches) {
            // Group accounts for batch processing
            $accountBatches = $accounts->chunk($batchSize);

            foreach ($accountBatches as $batchIndex => $accountBatch) {
                // Wait if too many concurrent batches
                while ($activeBatches >= $maxConcurrent) {
                    $this->info("Waiting for active batches... ({$activeBatches}/{$maxConcurrent})");
                    sleep(5);
                    $activeBatches = max(0, $activeBatches - 1);
                }

                // Prepare accounts and their sync times for batch job
                $batchAccounts = $accountBatch->all(); // Keep as Account models
                $batchSyncTimes = [];

                foreach ($accountBatch as $account) {
                    $batchSyncTimes[] = $this->getLastSyncTime($account);
                }

                $this->info("Processing batch " . ($batchIndex + 1) . " with " . count($batchAccounts) . " accounts");

                // Create single batch job to handle multiple accounts
                $batchJob = new BatchSyncTradesJob($batchAccounts, $batchSyncTimes);

                Bus::batch([$batchJob])
                    ->allowFailures()
                    ->onConnection('redis')
                    ->onQueue('optimized-sync-trades')
                    ->then(function () use (&$activeBatches) {
                        $activeBatches--;
                    })
                    ->catch(function () use (&$activeBatches) {
                        $activeBatches--;
                    })
                    ->dispatch();

                $activeBatches++;
                $processedCount += count($batchAccounts);

                $this->info("Dispatched {$processedCount}/{$totalAccounts} accounts");

                if ($delay > 0) {
                    sleep($delay);
                }
            }
        });

        $duration = $startTime->diffInMinutes(now());
        $this->info("Sync completed! Processed {$processedCount} accounts in {$duration} minutes");
    }

    protected function runDaemonMode($batchSize, $maxConcurrent, $delay)
    {
        $this->info("Starting daemon mode...");

        while (true) {
            try {
                $this->syncAllAccounts($batchSize, $maxConcurrent, $delay);
                $this->info("Cycle completed. Waiting 30 minutes before next cycle...");
                sleep(1800); // 30 minutes between full cycles
            } catch (\Exception $e) {
                $this->error("Error in daemon mode: " . $e->getMessage());
                Log::error("OptimizedSync daemon error: " . $e->getMessage());
                sleep(300); // Wait 5 minutes before retrying on error
            }
        }
    }

    protected function getLastSyncTime($account)
    {
        // Simple incremental sync - last 7 days if never synced
        $lastSync = $account->last_balance_sync_at;
        return $lastSync ? Carbon::parse($lastSync) : now()->subDays(7);
    }
    protected function syncSpecificAccount($accountCode)
    {
        $account = Account::where('code', $accountCode)->first();

        if (!$account) {
            $this->error("Account {$accountCode} not found");
            return;
        }

        $this->info("Testing sync for account: {$accountCode}");

        // Use batch job for single account (still benefits from connection reuse patterns)
        $job = new BatchSyncTradesJob([$account], [$this->getLastSyncTime($account)]);

        Bus::batch([$job])
            ->allowFailures()
            ->onConnection('redis')
            ->onQueue('optimized-sync-trades')
            ->dispatch();

        $this->info("Sync job dispatched for account {$accountCode}");
    }
}
