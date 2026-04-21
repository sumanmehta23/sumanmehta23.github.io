<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Enums\PlatformEnum;
use App\Services\MT5RestAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;

class SyncMT5AccountStatusCommand extends Command
{
    protected $signature = 'app:sync-mt5-account-status 
                            {--batch-size=100 : Number of accounts to process per batch}
                            {--limit= : Limit the total number of accounts to process}
                            {--dry-run : Show what would be done without making changes}
                            {--show-details : Display detailed progress information}';

    protected $description = 'Sync MT5 account status by checking if accounts exist in MT5 server';

    private MT5RestAPIService $mt5Service;
    private int $totalProcessed = 0;
    private int $foundInMT5 = 0;
    private int $notFoundInMT5 = 0;
    private int $apiErrors = 0;
    private int $updated = 0;

    public function handle()
    {
        $this->mt5Service = app(MT5RestAPIService::class);
        $batchSize = (int) $this->option('batch-size');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $isDryRun = $this->option('dry-run');

        $this->info('🔄 Starting MT5 Account Status Sync');
        $this->info('=====================================');

        if ($isDryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be made');
        }

        $this->newLine();

        // Get all MetaTrader5 accounts
        $query = Account::where('platform', PlatformEnum::MT5->value)
            ->whereNotNull('code')
            ->where('account_request_status', 1)
            ->where('code', '!=', '')
            ->orderBy('id');

        if ($limit) {
            $query->limit($limit);
        }

        $totalAccounts = $query->count();
        $this->info("📊 Total MT5 accounts to check: {$totalAccounts}");
        $this->newLine();

        // Create progress bar
        $progressBar = $this->output->createProgressBar($totalAccounts);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Starting...');

        // Process accounts in batches
        $query->chunk($batchSize, function ($accounts) use ($progressBar, $isDryRun) {
            $this->processBatch($accounts, $progressBar, $isDryRun);
        });

        $progressBar->finish();
        $this->newLine(2);

        // Display summary
        $this->displaySummary($totalAccounts, $isDryRun);

        return 0;
    }

    private function processBatch($accounts, $progressBar, bool $isDryRun): void
    {
        $logins = $accounts->pluck('code')->toArray();

        if (empty($logins)) {
            return;
        }

        $progressBar->setMessage("Processing batch of " . count($logins) . " accounts...");

        try {
            // Use the getBatchBalancesViaRestAPI method through reflection to access it
            $reflection = new \ReflectionClass($this->mt5Service);
            $method = $reflection->getMethod('getBatchBalancesViaRestAPI');
            $method->setAccessible(true);

            $balances = $method->invoke($this->mt5Service, $logins);

            // Check if API call was successful (returned data)
            if ($balances === null || $balances === false) {
                // API call failed - don't update any accounts
                $this->apiErrors += count($logins);
                $this->totalProcessed += count($logins);

                if ($this->option('show-details')) {
                    $this->newLine();
                    $this->error("❌ API call failed for batch of " . count($logins) . " accounts - skipping updates");
                }

                $progressBar->advance(count($logins));
                return;
            }

            // API call succeeded - process results
            foreach ($accounts as $account) {
                $login = $account->code;
                $exists = isset($balances[$login]);

                if ($exists) {
                    // Account found in MT5
                    $this->foundInMT5++;

                    // Only update if currently marked as not_found_in_mt5
                    if ($account->deletion_type === 'not_found_in_mt5') {
                        if (!$isDryRun) {
                            $account->sync_status = 'pending';
                            $account->sync_flagged_at = null;
                            $account->sync_flag_reason = null;
                            $account->save();
                        }
                        $this->updated++;

                        if ($this->option('show-details')) {
                            $this->newLine();
                            $this->info("✅ Account {$login} found in MT5 - reset to pending");
                        }
                    }
                } else {
                    // Account NOT found in MT5 response
                    $this->notFoundInMT5++;

                    // Mark as not_found_in_mt5 if not already marked
                    if ($account->deletion_type !== 'not_found_in_mt5') {
                        if (!$isDryRun) {
                            $account->deletion_type = 'not_found_in_mt5';
                            $account->sync_flagged_at = now();
                            $account->sync_flag_reason = 'Account not found in MT5 server during status sync';
                            $account->save();
                        }
                        $this->updated++;

                        if ($this->option('show-details')) {
                            $this->newLine();
                            $this->warn("⚠️  Account {$login} not found in MT5 - marked as not_found_in_mt5");
                        }
                    }
                }

                $this->totalProcessed++;
                $progressBar->advance();
            }
        } catch (Exception $e) {
            // Exception during API call - don't update any accounts
            $this->apiErrors += count($logins);
            $this->totalProcessed += count($logins);

            Log::error('MT5 Account Status Sync: API exception', [
                'error' => $e->getMessage(),
                'batch_size' => count($logins)
            ]);

            if ($this->option('show-details')) {
                $this->newLine();
                $this->error("❌ Exception during API call: " . $e->getMessage());
            }

            $progressBar->advance(count($logins));
        }
    }

    private function displaySummary(int $totalAccounts, bool $isDryRun): void
    {
        $this->info('📋 Sync Summary');
        $this->info('=====================================');
        $this->line("Total accounts checked: {$totalAccounts}");
        $this->line("Successfully processed: {$this->totalProcessed}");
        $this->line("Found in MT5: {$this->foundInMT5}");
        $this->line("Not found in MT5: {$this->notFoundInMT5}");
        $this->line("API errors (skipped): {$this->apiErrors}");

        if ($isDryRun) {
            $this->line("Would update: {$this->updated}");
        } else {
            $this->line("Updated: {$this->updated}");
        }

        $this->newLine();

        if ($this->apiErrors > 0) {
            $this->warn("⚠️  {$this->apiErrors} accounts skipped due to API errors");
            $this->warn("   These accounts were NOT marked as not_found_in_mt5");
        }

        if ($this->notFoundInMT5 > 0 && !$isDryRun) {
            $this->newLine();
            $this->info("💡 Use 'php artisan app:manage-not-found-accounts --list' to view accounts not found in MT5");
        }

        if ($isDryRun) {
            $this->newLine();
            $this->info("🔍 Run without --dry-run to apply changes");
        }
    }
}
