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
                            {--from=September 01,2024 : Start date for trade history}
                            {--to=March 31,2080 : End date for trade history}
                            {--mark-not-found : Mark accounts not found in MT5 with not_found_in_mt5 flag}';

    protected $description = 'Sync live MT5 accounts trades with pagination position tracking. Processes max 500 trades per account per cycle.';

    protected $mt5Service;
    protected $api;
    protected const MAX_TRADES_PER_SYNC = 500;

    public function handle(): void
    {
        $this->info('Starting incremental MT5 accounts trades sync...');

        try {
            $this->mt5Service = app(QueueSafeMT5Service::class);

            $accountCode = $this->option('account-code');
            $limit = (int) $this->option('limit');
            $defaultFromDate = $this->option('from');
            $defaultToDate = $this->option('to');
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

                    // Use full date range for consistent pagination
                    $fromDate = $this->option('from');
                    $toDate = $this->option('to');

                    $this->line("Using date range: {$fromDate} to {$toDate}");

                    // Sync trades for this account
                    $syncResult = $this->syncAccountTrades($account, $fromDate, $toDate);

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
     * Get the start position for pagination based on previous sync progress
     * Always uses the full date range for consistent pagination
     */
    protected function getStartPosition(Account $account): int
    {
        // If this is the first sync or we've completed a full cycle, start from 0
        if (!$account->last_trade_sync_position) {
            return 0;
        }

        // Return where we left off
        return $account->last_trade_sync_position;
    }

    /**
     * Convert date string from "F d,Y" format to "Y-m-d" format for storage
     * Example: "January 01,2026" → "2026-01-01"
     */
    protected function formatDateForStorage(string $dateString): string
    {
        try {
            return \Carbon\Carbon::createFromFormat('F d,Y', $dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("Failed to parse date string: {$dateString}", ['error' => $e->getMessage()]);
            // Return the date string as-is if parsing fails (Eloquent will try to handle it)
            return $dateString;
        }
    }

    /**
     * Sync trades for a single account (max 500 trades per cycle, pagination position aware)
     * @return bool|string true on success, false on failure, 'not_found' if account not found in MT5, 'partial' if trade limit reached
     */
    protected function syncAccountTrades(Account $account, string $fromDate, string $toDate)
    {
        $maxTrades = self::MAX_TRADES_PER_SYNC; // Always 500 trades per cycle
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

        // Get the starting position for pagination
        $startPosition = $this->getStartPosition($account);
        $this->line("Total trades available: {$total}, starting from position: {$startPosition}");

        // Check if we've already synced all trades
        if ($startPosition >= $total) {
            $this->line("✓ All trades already synced for account {$login}");
            $account->update([
                'last_trade_sync_at' => now(),
                'trade_sync_status' => 'success',
                'last_trade_sync_position' => 0,  // Reset for next cycle
            ]);
            return true;
        }

        // Fetch trades using pagination with limit
        $orders = [];
        $pageSize = 100; // MT5 API returns max 100 records per page
        $pageCount = 0;
        $hitTradeLimit = false;
        $currentPosition = $startPosition;

        $this->line("Starting pagination from position {$startPosition}: total={$total}, pageSize={$pageSize}, maxTrades={$maxTrades}");

        while (count($orders) < $maxTrades && $currentPosition < $total) {
            $pageCount++;
            $pageOrders = [];  // Initialize as empty array
            $remainingTotal = $total - $currentPosition;
            $remainingToFetch = $maxTrades - count($orders);
            $currentPageSize = min($pageSize, $remainingTotal, $remainingToFetch);

            $this->line("  Page {$pageCount}: position={$currentPosition}, pageSize={$currentPageSize}, ordersCollected=" . count($orders));

            $error_code = $this->mt5Service->executeOperation(function ($api) use ($login, $fromDate, $toDate, $currentPosition, $currentPageSize, &$pageOrders) {
                return $api->HistoryGetPage($login, $fromDate, $toDate, $currentPosition, $currentPageSize, $pageOrders);
            });

            $this->line("    → error_code={$error_code}, recordsReturned=" . count($pageOrders ?? []));

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("Failed to get trades page for account {$login}: " . MTRetCode::GetError($error_code), [
                    'error_code' => $error_code,
                    'page_number' => $pageCount,
                    'position' => $currentPosition,
                    'account_id' => $account->id,
                ]);
                // Update status to error
                $account->update([
                    'trade_sync_status' => 'error',
                ]);
                break;
            }

            if (empty($pageOrders)) {
                $this->line("    → Empty response, stopping pagination");
                break;
            }

            $orders = array_merge($orders, $pageOrders);
            $currentPosition += count($pageOrders);
        }

        if (!empty($orders)) {
            $this->line("Fetched " . count($orders) . " orders from position {$startPosition}");
            $this->processTrades($account, $orders);
        } else {
            $this->warn("No orders fetched from API");
        }

        // Determine if this is a complete sync or partial
        $isSyncComplete = ($currentPosition >= $total);
        $statusToSet = $isSyncComplete ? 'success' : 'partial';

        // Update sync progress
        $account->update([
            'last_trade_sync_at' => now(),
            'last_trade_sync_from' => $this->formatDateForStorage($fromDate),
            'last_trade_sync_to' => $this->formatDateForStorage($toDate),
            'last_trade_sync_position' => $isSyncComplete ? 0 : $currentPosition,  // Reset to 0 when complete
            'trade_sync_status' => $statusToSet,
        ]);

        if (!$isSyncComplete) {
            $this->warn("Reached maximum trade limit. Synced position {$startPosition}-{$currentPosition} / {$total}. Will continue from position {$currentPosition} next run.");
        }

        return $isSyncComplete ? true : 'partial';
    }

    /**
     * Process and upsert trades into the database
     */
    protected function processTrades(Account $account, array $orders): void
    {
        $this->info("Processing " . count($orders) . " orders for account {$account->code}");

        // Show first order structure for debugging
        if (!empty($orders)) {
            $firstOrder = $orders[0];
            $this->line("First order object properties: " . implode(", ", array_keys((array)$firstOrder)));
            $this->line("First order as array: " . json_encode($firstOrder, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        // Analyze data quality
        $nullPositionIds = 0;
        $nullSymbols = 0;
        $validOrders = 0;

        foreach ($orders as $order) {
            if (empty($order->ExpertPositionID)) $nullPositionIds++;
            if (empty($order->Symbol)) $nullSymbols++;
            if (!empty($order->ExpertPositionID) && !empty($order->Symbol)) $validOrders++;
            $this->line("  - Valid orders: {$validOrders}");
        }

        $tradesToUpsert = [];
        $openTradeCount = 0;
        $closedTradeCount = 0;
        $skippedCount = 0;

        // Filter out invalid orders and group by ExpertPositionID
        $validOrders = collect($orders)->filter(function ($order) {
            return !empty($order->ExpertPositionID) && !empty($order->Symbol);
        });

        $ordersByPosition = $validOrders->groupBy('ExpertPositionID');
        $this->line("Grouped {$validOrders->count()} valid orders into {$ordersByPosition->count()} positions");

        foreach ($ordersByPosition as $positionId => $positionOrders) {
            $positionOrders = $positionOrders->sortBy('TimeDone');

            if ($positionOrders->count() < 2) {
                // Open trade
                $order = $positionOrders->first();
                $tradeData = $this->prepareOpenTrade($account, $positionId, $order);
                if ($tradeData !== null) {
                    $tradesToUpsert[] = $tradeData;
                    $openTradeCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                // Closed trade
                $openOrder = $positionOrders->first();
                $closeOrder = $positionOrders->last();
                $tradeData = $this->prepareClosedTrade($account, $positionId, $openOrder, $closeOrder);
                if ($tradeData !== null) {
                    $tradesToUpsert[] = $tradeData;
                    $closedTradeCount++;
                } else {
                    $skippedCount++;
                }
            }
        }

        $this->line("Trade breakdown: {$openTradeCount} open, {$closedTradeCount} closed, {$skippedCount} skipped");

        if (!empty($tradesToUpsert)) {
            try {
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
                $this->line("✓ Successfully upserted {$tradeCount} trades");
                Log::info("Upserted {$tradeCount} trades for account {$account->code}", [
                    'account_id' => $account->id,
                    'trades_count' => $tradeCount,
                    'open_trades' => $openTradeCount,
                    'closed_trades' => $closedTradeCount,
                    'skipped' => $skippedCount,
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to upsert trades: {$e->getMessage()}");
                Log::error("Failed to upsert trades for account {$account->code}", [
                    'error' => $e->getMessage(),
                    'account_id' => $account->id,
                    'trades_to_insert' => count($tradesToUpsert),
                ]);
            }
        } else {
            $this->warn("No valid trades to upsert after processing");
        }
    }

    /**
     * Prepare open trade data
     */
    protected function prepareOpenTrade($account, $positionId, $order): ?array
    {
        // Validate required fields
        if (empty($order->Symbol) || $positionId === null || $positionId === 0) {
            return null;
        }
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
    protected function prepareClosedTrade($account, $positionId, $openOrder, $closeOrder): ?array
    {
        // Validate required fields
        if (empty($openOrder->Symbol) || empty($closeOrder->Symbol) || $positionId === null || $positionId === 0) {
            return null;
        }
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
            'volume' => $openOrder->VolumeInitial,
            'volume_ext' => $openOrder->VolumeInitialExt,
            'sl' => $openOrder->PriceSL,
            'tp' => $openOrder->PriceTP,
            'comment' => $openOrder->Comment ?? '',
            'state' => $closeOrder->State,
            'status' => 'closed',
            'code' => $account->code,
            'profit' => ($closeOrder->PriceCurrent - $openOrder->PriceCurrent) * ($openOrder->VolumeInitial / 10000000) * $openOrder->ContractSize,
            'commission' => 0,
            'swap' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
