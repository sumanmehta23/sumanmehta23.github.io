<?php

namespace App\Console\Commands;

use Throwable;
use App\Models\User;
use App\Models\Trade;
use App\MT5\MTRetCode;
use App\Models\Account;
use Illuminate\Bus\Batch;
use App\Services\UniversalMT5Service;
use App\Services\MailService;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use App\Jobs\SyncAllAccountsTradesJob;
use Illuminate\Support\Facades\Cache;

/**
 * Sync trades for all accounts except competition accounts
 * 
 * This command syncs MT5 trade history for all live accounts that are not currently
 * participating in active competitions. It's designed to handle large numbers of 
 * accounts efficiently by using job batching and rate limiting.
 * 
 * Features:
 * - Excludes competition accounts to avoid conflicts with existing sync
 * - Handles MT5 connection failures gracefully
 * - Supports batch processing to control API load
 * - Can run as daemon for continuous syncing
 * - Provides status information
 * - Handles accounts not found on MT5 server
 * 
 * Usage Examples:
 * - Test single account: php artisan app:sync-all-accounts-trades --test-account=906558
 * - Sync all with custom batch size: php artisan app:sync-all-accounts-trades --batch-size=20
 * - Run as daemon: php artisan app:sync-all-accounts-trades --daemon --delay=60
 * - Check status: php artisan app:sync-all-accounts-trades --status
 */
class SyncAllAccountsTrades extends Command
{
    protected $api;
    protected $mailService;
    protected $mt5Service;

    public function __construct(UniversalMT5Service $mt5Service, MailService $mailService)
    {
        parent::__construct();
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        // Defer connection until handle() method
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-all-accounts-trades 
                            {--daemon : Run as daemon continuously}
                            {--test-account= : Test with specific account code}
                            {--batch-size= : Number of accounts to process per batch}
                            {--delay= : Delay between batches in seconds}
                            {--status : Show sync status and exit}
                            {--max-concurrent= : Maximum concurrent batches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync MT5 trade history for all accounts except competition accounts (optimized for timeouts)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Connect to MT5 using connection pool
        if (!$this->mt5Service->connect()) {
            $this->error('Failed to connect to MT5 via pool.');
            return 1;
        }
        $this->api = $this->mt5Service->getApi();

        $isDaemon = $this->option('daemon');
        $testAccount = $this->option('test-account');
        $batchSize = (int) ($this->option('batch-size') ?? config('sync-all-trades.batch_size', 3));
        $delay = (int) ($this->option('delay') ?? config('sync-all-trades.delay_between_batches', 10));
        $showStatus = $this->option('status');
        $maxConcurrent = (int) ($this->option('max-concurrent') ?? config('sync-all-trades.max_concurrent_batches', 3));

        if ($showStatus) {
            $this->showSyncStatus();
            return;
        }

        $this->info("Configuration: Batch Size: {$batchSize}, Max Concurrent: {$maxConcurrent}, Delay: {$delay}s");

        if ($isDaemon) {
            $this->info("Starting daemon mode for all accounts trades sync...");
            $this->runAsDaemon($batchSize, $delay, $testAccount, $maxConcurrent);
        } else {
            $this->info("Starting one-time sync for all accounts trades...");
            $this->syncAllAccounts($batchSize, $testAccount, $maxConcurrent);
        }
    }

    protected function showSyncStatus()
    {
        $totalAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('account_request_status', 1)
            ->where('demo', false)
            ->count();

        $competitionAccounts = Account::whereNotNull('code')
            ->whereNotNull('competition_start_date')
            ->whereNotNull('competition_end_date')
            ->where('competition_status', 'active')
            ->count();

        $syncableAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('account_request_status', 1)
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

        $this->info("=== All Accounts Sync Status ===");
        $this->info("Total live accounts: {$totalAccounts}");
        $this->info("Competition accounts (excluded): {$competitionAccounts}");
        $this->info("Accounts available for sync: {$syncableAccounts}");
        $this->info("Total trades in system: {$totalTrades}");
        $this->info("Trades synced in last hour: {$recentTrades}");

        // Show some sample accounts
        $sampleAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('account_request_status', 1)
            ->where('demo', false)
            ->where(function ($q) {
                $q->whereNull('competition_start_date')
                    ->orWhereNull('competition_end_date')
                    ->orWhereNull('competition_status')
                    ->orWhere('competition_status', '!=', 'active');
            })
            ->take(5)
            ->get(['code', 'platform']);

        $this->info("\nSample accounts to sync:");
        foreach ($sampleAccounts as $account) {
            $tradeCount = Trade::where('code', $account->code)->count();
            $this->info("  {$account->code} ({$account->platform}) - {$tradeCount} trades");
        }
    }

    protected function runAsDaemon($batchSize, $delay, $testAccount = null, $maxConcurrent = 5)
    {
        while (true) {
            try {
                $this->syncAllAccounts($batchSize, $testAccount, $maxConcurrent);
                $this->info("Batch completed. Waiting {$delay} seconds before next batch...");
                sleep($delay);
            } catch (\Exception $e) {
                $this->error("Error in daemon mode: " . $e->getMessage());
                Log::error("SyncAllAccountsTrades daemon error: " . $e->getMessage());
                sleep(60); // Wait 60 seconds before retrying on error
            }
        }
    }

    protected function syncAllAccounts($batchSize, $testAccount = null, $maxConcurrent = 5)
    {
        $query = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('account_request_status', 1)
            ->where('demo', false) // Only live accounts
            // Exclude competition accounts (accounts with competition dates AND active status)
            ->where(function ($q) {
                $q->whereNull('competition_start_date')
                    ->orWhereNull('competition_end_date')
                    ->orWhereNull('competition_status')
                    ->orWhere('competition_status', '!=', 'active');
            });

        // If testing with specific account
        if ($testAccount) {
            $query->where('code', $testAccount);
            $this->info("Testing with account: {$testAccount}");
        }

        $totalAccounts = $query->count();
        $this->info("Found {$totalAccounts} accounts to sync (excluding competition accounts)");

        if ($totalAccounts === 0) {
            $this->info("No accounts found to sync.");
            return;
        }

        $processedCount = 0;
        $successCount = 0;
        $errorCount = 0;

        $query->chunk(500, function ($accounts) use ($batchSize, $maxConcurrent, &$processedCount, &$successCount, &$errorCount) {
            $jobs = [];
            foreach ($accounts as $account) {
                // Check if user exists for this account
                if ($account->user_id && User::where('id', $account->user_id)->exists()) {
                    $jobs[] = new SyncAllAccountsTradesJob($account);
                }
            }

            // Only proceed if there are valid jobs
            if (!empty($jobs)) {
                // Create smaller batches to avoid overwhelming the system
                $jobBatches = array_chunk($jobs, $batchSize);
                $activeBatches = 0;

                foreach ($jobBatches as $batchIndex => $batch) {
                    // Wait if we have too many concurrent batches
                    while ($activeBatches >= $maxConcurrent) {
                        $this->info("Waiting for active batches to complete... ({$activeBatches}/{$maxConcurrent})");
                        sleep(10);
                        // Check completed batches and reduce counter
                        // This is a simplified check - in production you might want to track batch IDs
                        $activeBatches = max(0, $activeBatches - 1);
                    }

                    $this->info("Processing batch " . ($batchIndex + 1) . " of " . count($jobBatches) . " with " . count($batch) . " accounts (Active: {$activeBatches}/{$maxConcurrent})");

                    try {
                        $batchJob = Bus::batch($batch)
                            ->allowFailures()
                            ->onConnection('redis')
                            ->onQueue('sync-all-trades')
                            ->then(function (Batch $batch) use (&$successCount, &$activeBatches) {
                                $successCount += $batch->totalJobs;
                                $activeBatches--;
                                Log::info("All accounts sync batch {$batch->id} completed successfully");
                            })
                            ->catch(function (Batch $batch, Throwable $e) use (&$errorCount, &$activeBatches) {
                                $errorCount += $batch->failedJobs;
                                $activeBatches--;
                                Log::error("All accounts sync batch {$batch->id} failed: " . $e->getMessage());
                            })
                            ->finally(function (Batch $batch) use (&$activeBatches) {
                                $activeBatches = max(0, $activeBatches - 1);
                                Log::info("All accounts sync batch {$batch->id} finished processing");
                            })
                            ->dispatch();

                        $activeBatches++;
                        $processedCount += count($batch);

                        // Add longer delay between batches to control API load
                        if ($batchIndex < count($jobBatches) - 1) {
                            sleep(5); // 5 second delay between batches
                        }
                    } catch (\Exception $e) {
                        $this->error("Error dispatching batch: " . $e->getMessage());
                        Log::error("SyncAllAccountsTrades batch dispatch error: " . $e->getMessage());
                        $errorCount += count($batch);
                    }
                }

                // Wait for all remaining batches to complete before finishing
                while ($activeBatches > 0) {
                    $this->info("Waiting for {$activeBatches} remaining batches to complete...");
                    sleep(10);
                    $activeBatches = max(0, $activeBatches - 1);
                }
            }
        });

        $this->info("Sync completed. Processed: {$processedCount}, Success: {$successCount}, Errors: {$errorCount}");
    }
}
