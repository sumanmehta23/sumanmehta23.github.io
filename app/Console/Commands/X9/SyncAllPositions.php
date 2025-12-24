<?php

namespace App\Console\Commands\X9;

use App\Services\X9Service;
use App\Models\Account;
use App\Models\Trade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncAllPositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'x9:sync-all-positions
                            {--save : Save positions to database (if positions table exists)}
                            {--log : Enable detailed logging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all open positions across all accounts from X9 API';

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
        $savePositions = $this->option('save');
        $enableLogging = $this->option('log');

        $this->info('🔄 Starting X9 All Positions Sync');
        $this->line('=====================================');

        // Fetch all positions from X9
        $result = $this->x9Service->getAllPositions();

        if (!$result['status']) {
            $this->error('❌ Failed to fetch all positions: ' . $result['message']);
            if ($enableLogging) {
                Log::error('X9 Sync All Positions Failed', [
                    'error' => $result['message']
                ]);
            }
            return 1;
        }

        $data = $result['data'];
        $totalAccounts = $data['total_accounts'] ?? 0;
        $accountsWithPositions = $data['accounts_with_positions'] ?? 0;
        $accounts = $data['accounts'] ?? [];

        $this->info("✅ Retrieved positions from {$accountsWithPositions}/{$totalAccounts} accounts");

        if (empty($accounts)) {
            $this->info('No positions found');
            return 0;
        }

        $totalPositions = 0;
        $accountsUpdated = 0;
        $positionsSaved = 0;
        $positionsCreated = 0;
        $positionsUpdated = 0;
        $errors = 0;

        foreach ($accounts as $accountData) {
            try {
                $accountNumber = $accountData['account_number'] ?? null;
                $positions = $accountData['positions'] ?? [];

                if (!$accountNumber || empty($positions)) {
                    continue;
                }

                $totalPositions += count($positions);

                // Find the account in local database
                $account = Account::where('platform', Account::PLATFORM_X9)
                    ->where('code', $accountNumber)
                    ->first();

                if ($account) {
                    // Update last position sync timestamp
                    $account->update([
                        'last_position_sync_at' => now(),
                    ]);
                    $accountsUpdated++;

                    if ($enableLogging) {
                        Log::info("X9 Positions synced for account: {$accountNumber}", [
                            'positions_count' => count($positions)
                        ]);
                    }

                    // Save positions to trades table
                    if ($savePositions) {
                        foreach ($positions as $position) {
                            try {
                                $positionId = $position['id'] ?? null;

                                if (!$positionId || $positionId == 0) {
                                    continue; // Skip invalid position IDs
                                }

                                // Check if trade already exists
                                $existingTrade = Trade::where('account_id', $account->id)
                                    ->where('position_id', $positionId)
                                    ->first();

                                $tradeData = [
                                    'account_id' => $account->id,
                                    'code' => $accountNumber,
                                    'order_id' => $position['ticket_number'] ?? $positionId,
                                    'symbol' => $position['symbol'] ?? null,
                                    'position_id' => $positionId,
                                    'type' => $this->mapPositionType($position['ticket_open_as'] ?? null),
                                    'volume' => $position['order_volume'] ?? 0,
                                    'volume_ext' => $position['remaining_volume'] ?? 0,
                                    'open_price' => $position['open_price'] ?? 0,
                                    'close_price' => null,
                                    'profit' => $position['profit_loss'] ?? 0,
                                    'sl' => $position['stop_loss'] ?? null,
                                    'tp' => $position['take_profit'] ?? null,
                                    'comment' => $position['comment'] ?? null,
                                    'status' => 'open',
                                    'open_time' => isset($position['date_time']) ? Carbon::parse($position['date_time']) : now(),
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

                                $positionsSaved++;
                            } catch (\Exception $e) {
                                if ($enableLogging) {
                                    Log::error("X9 Position save error for account {$accountNumber}", [
                                        'position_id' => $position['position_id'] ?? 'unknown',
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $errors++;
                if ($enableLogging) {
                    Log::error("X9 Position sync error", [
                        'account' => $accountNumber ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        $this->newLine();
        $this->info('✅ Sync Complete');
        $this->line('================');
        $this->info("Total Positions: {$totalPositions}");
        $this->info("Accounts Updated: {$accountsUpdated}");
        if ($savePositions) {
            $this->info("Positions Saved: {$positionsSaved} (Created: {$positionsCreated}, Updated: {$positionsUpdated})");
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
