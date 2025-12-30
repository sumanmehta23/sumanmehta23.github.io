<?php

namespace App\Console\Commands\X9;

use App\Services\X9Service;
use App\Models\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAccountBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'x9:sync-account-balances
                            {--accounts= : Comma-separated list of account codes to sync}
                            {--force : Force sync even if recently synced}
                            {--log : Enable detailed logging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync account balances from X9 API';

    protected $x9Service;

    public function __construct(X9Service $x9Service)
    {
        parent::__construct();
        $this->x9Service = $x9Service;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountCodes = $this->option('accounts');
        $force = $this->option('force');
        $enableLogging = $this->option('log');

        $this->info('🔄 Starting X9 Account Balance Sync');
        $this->line('======================================');

        // Fetch all accounts with positions (which includes balance data) in one API call
        $result = $this->x9Service->getAccountsWithPositions();

        if (!$result['status']) {
            $this->error('❌ Failed to fetch account data from X9: ' . $result['message']);
            if ($enableLogging) {
                Log::error('X9 Sync Account Balances Failed', [
                    'error' => $result['message']
                ]);
            }
            return 1;
        }

        $x9Accounts = $result['data'];
        $this->info("✅ Retrieved data for " . count($x9Accounts) . " accounts from X9");

        // Build a map of X9 account data by account number for quick lookup
        $x9AccountMap = [];
        foreach ($x9Accounts as $x9Account) {
            $accountNumber = $x9Account['account_number'] ?? null;
            if ($accountNumber) {
                $x9AccountMap[$accountNumber] = $x9Account;
            }
        }

        // Build query for local accounts to sync
        $query = Account::where('platform', Account::PLATFORM_X9)
            ->whereNotNull('code');

        if ($accountCodes) {
            $codes = array_map('trim', explode(',', $accountCodes));
            $query->whereIn('code', $codes);
            $this->info('Syncing specific accounts: ' . implode(', ', $codes));
        }

        if (!$force) {
            // Only sync accounts that haven't been synced in the last 10 minutes
            $query->where(function ($q) {
                $q->whereNull('last_balance_sync_at')
                    ->orWhere('last_balance_sync_at', '<', now()->subMinutes(10));
            });
        }

        $accounts = $query->get();
        $totalAccounts = $accounts->count();

        if ($totalAccounts === 0) {
            $this->info('No accounts need balance sync at this time');
            return 0;
        }

        $this->info("Found {$totalAccounts} local accounts to sync");
        $this->newLine();

        $updated = 0;
        $noChange = 0;
        $errors = 0;
        $notFound = 0;

        // Create progress bar
        $bar = $this->output->createProgressBar($totalAccounts);
        $bar->start();

        foreach ($accounts as $account) {
            try {
                $accountCode = $account->code;

                // Look up the account in X9 data
                if (!isset($x9AccountMap[$accountCode])) {
                    $notFound++;
                    $account->update([
                        'sync_status' => 'not_found_in_x9',
                        'sync_error' => 'Account not found in X9 system',
                        'last_balance_sync_at' => now()
                    ]);
                    if ($enableLogging) {
                        Log::warning("X9 Account not found: {$accountCode}");
                    }
                    $bar->advance();
                    continue;
                }

                $balanceData = $x9AccountMap[$accountCode];

                // Extract balance information
                $newBalance = $balanceData['balance'] ?? null;
                $newEquity = $balanceData['equity'] ?? null;
                $newFreeMargin = $balanceData['free_margin'] ?? null;

                // Check if balance changed
                $balanceChanged = false;
                if ($newBalance !== null && $account->balance != $newBalance) {
                    $balanceChanged = true;
                }

                // Prepare update data
                $updateData = [
                    'last_balance_sync_at' => now(),
                    'sync_status' => 'synced',
                    'sync_error' => null
                ];

                if ($newBalance !== null) {
                    $updateData['balance'] = $newBalance;
                }
                if ($newEquity !== null) {
                    $updateData['equity'] = $newEquity;
                }
                if ($newFreeMargin !== null) {
                    $updateData['margin_free'] = $newFreeMargin;
                }

                if ($balanceChanged) {
                    $updateData['last_balance_changed_at'] = now();
                    $updateData['has_balance_activity'] = true;
                }

                $account->update($updateData);

                if ($balanceChanged) {
                    $updated++;
                    if ($enableLogging) {
                        Log::info("X9 Balance updated for account {$accountCode}", [
                            'old_balance' => $account->balance,
                            'new_balance' => $newBalance
                        ]);
                    }
                } else {
                    $noChange++;
                }
            } catch (\Exception $e) {
                $errors++;
                if ($enableLogging) {
                    Log::error("X9 Balance sync error for account {$account->code}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Display results
        $this->info('✅ Sync Complete');
        $this->line('================');
        $this->info("Updated: {$updated}");
        $this->info("No Change: {$noChange}");
        $this->warn("Not Found in X9: {$notFound}");
        if ($errors > 0) {
            $this->error("Errors: {$errors}");
        }

        return 0;
    }
}
