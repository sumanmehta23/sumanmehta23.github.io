<?php

namespace App\Console\Commands\X9;

use App\Services\X9Service;
use App\Models\Account;
use App\Models\Trade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncAccountBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'x9:sync-account-balances
                            {--sync-positions : Also sync open positions/trades}
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
        $syncPositions = $this->option('sync-positions');
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
        if ($syncPositions) {
            $this->info("Will also sync open positions/trades");
        }
        $this->newLine();

        $updated = 0;
        $noChange = 0;
        $errors = 0;
        $notFound = 0;
        $positionsSynced = 0;
        $positionsCreated = 0;
        $positionsUpdated = 0;

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
                $newMargin = $balanceData['margin'] ?? null;
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
                if ($newMargin !== null) {
                    $updateData['margin'] = $newMargin;
                }
                if ($newFreeMargin !== null) {
                    $updateData['margin_free'] = $newFreeMargin;
                }

                if ($balanceChanged) {
                    $updateData['last_balance_changed_at'] = now();
                    $updateData['has_balance_activity'] = true;
                }

                // Sync positions/trades if option is enabled
                if ($syncPositions) {
                    $positions = $balanceData['positions'] ?? [];
                    if (!empty($positions)) {
                        foreach ($positions as $position) {
                            try {
                                $positionId = $position['position_id'] ?? null;

                                if (!$positionId || $positionId == 0) {
                                    continue; // Skip invalid position IDs
                                }

                                // Check if trade already exists
                                $existingTrade = Trade::where('account_id', $account->id)
                                    ->where('position_id', $positionId)
                                    ->first();

                                $tradeData = [
                                    'account_id' => $account->id,
                                    'code' => $accountCode,
                                    'order_id' => $position['order_id'] ?? $positionId,
                                    'symbol' => $position['symbol'] ?? null,
                                    'position_id' => $positionId,
                                    'type' => $this->mapPositionType($position['direction'] ?? null),
                                    'volume' => $position['volume'] ?? 0,
                                    'volume_ext' => $position['volume_ext'] ?? 0,
                                    'open_price' => $position['open_price'] ?? 0,
                                    'close_price' => null,
                                    'profit' => $position['profit'] ?? 0,
                                    'sl' => $position['sl'] ?? null,
                                    'tp' => $position['tp'] ?? null,
                                    'comment' => $position['comment'] ?? null,
                                    'status' => 'open',
                                    'open_time' => isset($position['open_time']) ? Carbon::parse($position['open_time']) : now(),
                                    'close_time' => null,
                                ];

                                if ($existingTrade) {
                                    // Update existing trade
                                    $existingTrade->update($tradeData);
                                    $positionsUpdated++;
                                } else {
                                    // Create new trade
                                    Trade::create($tradeData);
                                    $positionsCreated++;
                                }

                                $positionsSynced++;
                            } catch (\Exception $e) {
                                if ($enableLogging) {
                                    Log::error("X9 Position sync error for account {$accountCode}", [
                                        'position_id' => $position['position_id'] ?? 'unknown',
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                        }

                        $updateData['last_position_sync_at'] = now();
                    }
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
        if ($syncPositions) {
            $this->info("Positions Synced: {$positionsSynced} (Created: {$positionsCreated}, Updated: {$positionsUpdated})");
        }
        if ($errors > 0) {
            $this->error("Errors: {$errors}");
        }

        return 0;
    }

    /**
     * Map X9 position direction to trade type
     */
    private function mapPositionType($direction)
    {
        return strtolower($direction) === 'buy' ? 'buy' : 'sell';
    }
}
