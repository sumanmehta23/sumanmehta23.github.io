<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Trade;
use App\MT5\MTRetCode;
use App\Services\QueueSafeMT5Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncLiveAccountsTrades extends Command
{
    protected $signature = 'app:sync-live-accounts-trades 
                            {--account-code= : Sync specific account by code}
                            {--limit=100 : Maximum accounts to sync (default: 100)}
                            {--max-trades=500 : Maximum trades to sync per account (default: 500)}
                            {--from=September 01,2024 : Start date for trade history}
                            {--to=March 31,2080 : End date for trade history}
                            {--mark-not-found : Mark accounts not found in MT5 with not_found_in_mt5 flag}';

    protected $description = 'Sync live MT5 accounts trades and update last_trade_sync_at timestamp. Max 500 trades per account to prevent timeouts.';

    protected $mt5Service;
    protected $api;
    protected const MAX_TRADES_PER_SYNC = 500;

    public function handle(): void
    {
        $this->info('Starting live MT5 accounts trades sync...');

        try {
            $this->mt5Service = app(QueueSafeMT5Service::class);

            $accountCode = $this->option('account-code');
            $limit = (int) $this->option('limit');
            $maxTrades = (int) $this->option('max-trades') ?: self::MAX_TRADES_PER_SYNC;
            $fromDate = $this->option('from');
            $toDate = $this->option('to');
            $markNotFound = $this->option('mark-not-found');

            // Fetch accounts to sync, excluding accounts not found in MT5
            $query = Account::where('demo', false)
                ->where('account_request_status', 1)
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereNull('trade_sync_status')
                        ->orWhere('trade_sync_status', '!=', 'not_found');
                })
                ->limit($limit);

            if ($accountCode) {
                $query->where('code', $accountCode);
                $this->info("Syncing specific account: {$accountCode}");
            }

            $accounts = $query->get();

            if ($accounts->isEmpty()) {
                $this->warning('No accounts found to sync.');
                return;
            }

            $this->info("Found {$accounts->count()} account(s) to sync.");

            $totalSynced = 0;
            $totalFailed = 0;
            $totalNotFound = 0;

            foreach ($accounts as $account) {
                try {
                    $this->info("Syncing trades for account: {$account->code}");

                    // Sync trades for this account
                    $syncResult = $this->syncAccountTrades($account, $fromDate, $toDate, $maxTrades);

                    if ($syncResult === false) {
                        $totalFailed++;
                    } elseif ($syncResult === 'not_found') {
                        $totalNotFound++;

                        if ($markNotFound) {
                            $account->update([
                                'not_found_in_mt5' => true,
                                'deletion_type' => 'not_found_in_mt5',
                            ]);
                            $this->warn("Account {$account->code} marked as not_found_in_mt5");
                        }
                    } elseif ($syncResult === 'partial') {
                        $totalSynced++;
                        $this->line("⏸ Partially synced account {$account->code} (reached 500 trade limit, will continue next run)");
                    } else {
                        $totalSynced++;
                        $this->line("✓ Successfully synced account: {$account->code}");
                    }
                } catch (\Exception $e) {
                    $totalFailed++;
                    $this->error("Error syncing account {$account->code}: {$e->getMessage()}");
                    $account->update([
                        'trade_sync_status' => 'error',
                    ]);
                    Log::error("Error syncing account {$account->code}", [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                }
            }

            // Print summary
            $this->line("\n" . str_repeat('=', 50));
            $this->info("Sync Summary:");
            $this->line("Successfully synced: {$totalSynced}");
            $this->line("Not found in MT5: {$totalNotFound}");
            $this->line("Failed: {$totalFailed}");
            $this->line(str_repeat('=', 50) . "\n");
        } catch (\Exception $e) {
            $this->error("Failed to execute sync command: {$e->getMessage()}");
            Log::error("SyncLiveAccountsTrades command failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Sync trades for a single account
     * @return bool|string true on success, false on failure, 'not_found' if account not found in MT5, 'partial' if trade limit reached
     */
    protected function syncAccountTrades(Account $account, string $fromDate, string $toDate, int $maxTrades = 500)
    {
        $login = $account->code;
        $total = 0;

        // Get total number of trades
        $error_code = $this->mt5Service->executeOperation(function ($api) use ($login, $fromDate, $toDate, &$total) {
            return $api->HistoryGetTotal($login, $fromDate, $toDate, $total);
        });

        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::error("Failed to get total trades for account {$login}: " . MTRetCode::GetError($error_code), [
                'error_code' => $error_code,
                'account_id' => $account->id,
            ]);

            // Handle account not found error
            if ($error_code == MTRetCode::MT_RET_ERR_NOTFOUND) {
                Log::warning("Account {$login} not found in MT5", [
                    'account_id' => $account->id,
                    'error_code' => $error_code,
                ]);
                $account->update([
                    'trade_sync_status' => 'not_found',
                ]);
                return 'not_found';
            }

            // Update status to error for other failures
            $account->update([
                'trade_sync_status' => 'error',
            ]);
            return false;
        }

        if ($total == 0) {
            $this->line("No trades found for account {$login}");

            // Update last_trade_sync_at and status even if no trades
            $account->update([
                'last_trade_sync_at' => now(),
                'trade_sync_status' => 'success',
            ]);

            return true;
        }

        $this->line("Found {$total} trades for account {$login}");

        // Fetch trades using pagination with limit
        $orders = [];
        $pageSize = 100; // MT5 API returns max 100 records per page
        $pageCount = 0;
        $position = $total;
        $hitTradeLimit = false;

        while ($position > 0) {
            // Check if we've hit the max trades limit
            if (count($orders) >= $maxTrades) {
                $hitTradeLimit = true;
                $this->warn("Reached maximum trade limit of {$maxTrades} for account {$login}. Will continue syncing next run.");
                Log::info("Trade limit reached for account {$login}", [
                    'account_id' => $account->id,
                    'trades_fetched' => count($orders),
                    'total_available' => $total,
                    'remaining' => $total - count($orders),
                ]);
                break;
            }

            $pageCount++;
            $pageOrders = [];
            $remainingToFetch = $maxTrades - count($orders);
            $currentPageSize = min($pageSize, $position, $remainingToFetch);

            $error_code = $this->mt5Service->executeOperation(function ($api) use ($login, $fromDate, $toDate, $position, $currentPageSize, &$pageOrders) {
                return $api->HistoryGetPage($login, $fromDate, $toDate, $position, $currentPageSize, $pageOrders);
            });

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("Failed to get trades page for account {$login}: " . MTRetCode::GetError($error_code), [
                    'error_code' => $error_code,
                    'page_number' => $pageCount,
                    'position' => $position,
                    'account_id' => $account->id,
                ]);
                // Update status to error
                $account->update([
                    'trade_sync_status' => 'error',
                ]);
                break;
            }

            if (empty($pageOrders)) {
                break;
            }

            $orders = array_merge($orders, $pageOrders);
            $position -= $currentPageSize;
        }

        if (!empty($orders)) {
            $this->processTrades($account, $orders);
        }

        // Update last_trade_sync_at timestamp and status after sync
        $statusToSet = $hitTradeLimit ? 'partial' : 'success';
        $account->update([
            'last_trade_sync_at' => now(),
            'trade_sync_status' => $statusToSet,
        ]);

        return $hitTradeLimit ? 'partial' : true;
    }

    /**
     * Process and upsert trades into the database
     */
    protected function processTrades(Account $account, array $orders): void
    {
        $tradesToUpsert = [];
        $ordersByPosition = collect($orders)->groupBy('PositionID');

        foreach ($ordersByPosition as $positionId => $positionOrders) {
            $positionOrders = $positionOrders->sortBy('TimeDone');

            if ($positionOrders->count() < 2) {
                // Open trade
                $order = $positionOrders->first();
                $tradesToUpsert[] = $this->prepareOpenTrade($account, $positionId, $order);
            } else {
                // Closed trade
                $openOrder = $positionOrders->first();
                $closeOrder = $positionOrders->last();
                $tradesToUpsert[] = $this->prepareClosedTrade($account, $positionId, $openOrder, $closeOrder);
            }
        }

        if (!empty($tradesToUpsert)) {
            Trade::upsert($tradesToUpsert, ['account_id', 'position_id'], [
                'order_id',
                'symbol',
                'open_price',
                'close_price',
                'open_time',
                'close_time',
                'volume',
                'volume_ext',
                'profit',
                'commission',
                'swap',
                'sl',
                'tp',
                'comment',
                'type',
                'status',
                'state',
                'code',
                'updated_at',
            ]);

            $tradeCount = count($tradesToUpsert);
            Log::info("Upserted {$tradeCount} trades for account {$account->code}", [
                'account_id' => $account->id,
                'trades_count' => $tradeCount,
            ]);
        }
    }

    /**
     * Prepare open trade data
     */
    protected function prepareOpenTrade($account, $positionId, $order): array
    {
        return [
            'account_id' => $account->id,
            'close_price' => null,
            'close_time' => null,
            'code' => $account->code,
            'comment' => $order->Comment ?? '',
            'commission' => 0,
            'created_at' => now(),
            'open_price' => $order->PriceCurrent,
            'open_time' => date('Y-m-d H:i:s', $order->TimeDone),
            'order_id' => $order->Order,
            'position_id' => $positionId,
            'profit' => 0,
            'sl' => $order->PriceSL,
            'state' => $order->State,
            'status' => 'open',
            'swap' => 0,
            'symbol' => $order->Symbol,
            'tp' => $order->PriceTP,
            'type' => $order->Type,
            'updated_at' => now(),
            'volume' => $order->VolumeInitial,
            'volume_ext' => $order->VolumeInitialExt,
        ];
    }

    /**
     * Prepare closed trade data
     */
    protected function prepareClosedTrade($account, $positionId, $openOrder, $closeOrder): array
    {
        return [
            'account_id' => $account->id,
            'position_id' => $positionId,
            'order_id' => $openOrder->Order,
            'symbol' => $openOrder->Symbol,
            'type' => $openOrder->Type,
            'open_price' => $openOrder->PriceCurrent,
            'close_price' => $closeOrder->PriceCurrent,
            'open_time' => date('Y-m-d H:i:s', $openOrder->TimeDone),
            'close_time' => date('Y-m-d H:i:s', $closeOrder->TimeDone),
            'volume' => $openOrder->Volume,
            'volume_ext' => $openOrder->Volume,
            'sl' => $openOrder->PriceSL,
            'tp' => $openOrder->PriceTP,
            'comment' => $openOrder->Comment ?? '',
            'state' => $closeOrder->State,
            'status' => 'closed',
            'code' => $account->code,
            'profit' => ($closeOrder->PriceCurrent - $openOrder->PriceCurrent) * ($openOrder->Volume / 10000000) * $openOrder->ContractSize,
            'commission' => 0,
            'swap' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
