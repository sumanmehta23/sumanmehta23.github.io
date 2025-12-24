<?php

namespace App\Console\Commands\X9;

use App\Services\X9Service;
use App\Models\Account;
use App\Models\Trade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncAccountsWithPositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'x9:sync-accounts-with-positions
                            {--update-balances : Update local account balances}
                            {--log : Enable detailed logging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync accounts that have open positions from X9 API';

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
        $updateBalances = $this->option('update-balances');
        $enableLogging = $this->option('log');

        $this->info('🔄 Starting X9 Accounts with Positions Sync');
        $this->line('================================================');

        // Fetch accounts with positions from X9
        $result = $this->x9Service->getAccountsWithPositions();

        if (!$result['status']) {
            $this->error('❌ Failed to fetch accounts with positions: ' . $result['message']);
            if ($enableLogging) {
                Log::error('X9 Sync Accounts With Positions Failed', [
                    'error' => $result['message']
                ]);
            }
            return 1;
        }

        $accounts = $result['data'];
        $totalAccounts = $result['total_accounts'];

        $this->info("✅ Retrieved {$totalAccounts} accounts with open positions");

        if (empty($accounts) || !is_array($accounts)) {
            $this->info('No accounts with positions found');
            return 0;
        }

        $updated = 0;
        $notFound = 0;
        $errors = 0;
        $positionsSynced = 0;
        $positionsCreated = 0;
        $positionsUpdated = 0;

        // Create a progress bar
        $bar = $this->output->createProgressBar(count($accounts));
        $bar->start();

        foreach ($accounts as $accountData) {
            try {

                $accountNumber = $accountData['account_number'] ?? null;
                $openPositions = $accountData['open_positions'] ?? 0;

                if (!$accountNumber) {
                    $errors++;
                    $bar->advance();
                    continue;
                }

                // Find the account in local database
                $account = Account::where('platform', Account::PLATFORM_X9)
                    ->where('code', $accountNumber)
                    ->first();

                if (!$account) {
                    $notFound++;
                    if ($enableLogging) {
                        Log::warning("X9 Account not found locally: {$accountNumber}");
                    }
                    $bar->advance();
                    continue;
                }

                // Update account information
                $updateData = [
                    'last_position_sync_at' => now(),
                ];

                if ($updateBalances) {
                    // Update balance, equity, margin if available
                    if (isset($accountData['balance'])) {
                        $updateData['balance'] = $accountData['balance'];
                    }
                    if (isset($accountData['equity'])) {
                        $updateData['equity'] = $accountData['equity'];
                    }
                    if (isset($accountData['margin'])) {
                        $updateData['margin'] = $accountData['margin'];
                    }
                    if (isset($accountData['free_margin'])) {
                        $updateData['margin_free'] = $accountData['free_margin'];
                    }
                }
                $this->info('positions data' . print_r($accountData, true));


                $account->update($updateData);

                if ($enableLogging) {
                    Log::info("X9 Account synced: {$accountNumber}", [
                        'open_positions' => $openPositions,
                        'balance' => $accountData['balance'] ?? null
                    ]);
                }

                $updated++;
            } catch (\Exception $e) {
                $errors++;
                if ($enableLogging) {
                    Log::error("X9 Account sync error: {$accountNumber}", [
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
        $this->info("Positions Synced: {$positionsSynced} (Created: {$positionsCreated}, Updated: {$positionsUpdated})");
        $this->warn("Not Found Locally: {$notFound}");
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
