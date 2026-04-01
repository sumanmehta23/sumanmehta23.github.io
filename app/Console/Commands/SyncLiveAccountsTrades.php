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
                            {--to= : End date for trade history (default: today)}
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
            $defaultToDate = $this->option('to') ?? now()->format('F d,Y');
            $markNotFound = $this->option('mark-not-found');

            // Fetch accounts to sync: never synced, incomplete, or failed (exclude 'success' and 'not_found')
            $query = Account::where('demo', false)
                ->where('account_request_status', 1)
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereNull('trade_sync_status')
                        ->orWhere('trade_sync_status', '')
                        ->orWhereIn('trade_sync_status', ['partial', 'error']);
                })
                ->limit($limit);

            if ($accountCode) {
                $query->where('code', $accountCode);
                $this->info("Syncing specific account: {$accountCode}");
            }

            $accounts = $query->get();

            if ($accounts->isEmpty()) {
                $this->warn('No accounts found to sync.');
                return;
            }

            $this->info("Found {$accounts->count()} account(s) to sync.");

            $totalSynced = 0;
            $totalFailed = 0;
            $totalNotFound = 0;

            foreach ($accounts as $account) {
                try {
                    $this->info("Syncing trades for account: {$account->code}");

                    // Calculate date range based on last trade timestamp
                    $dateRange = $this->getDateRangeForSync($account, $defaultFromDate, $defaultToDate);
                    $fromDate = $dateRange['from'];
                    $toDate = $dateRange['to'];

                    $this->line("📅 {$dateRange['reason']}");
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
     * Determine the date range for sync based on last trade timestamp
     * This enables true incremental syncing without worrying about pagination positions
     */
    protected function getDateRangeForSync(Account $account, string $defaultFromDate, string $defaultToDate): array
    {
        // Always use a wide date range for consistency
        // Pagination position is relative to the full range, not date-specific ranges
        // Timestamp filtering ensures we skip already-processed trades
        return [
            'from' => $defaultFromDate ?? 'September 01,2024',
            'to' => $defaultToDate,
            'reason' => 'Wide range for position-based pagination',
        ];
    }

    /**
     * Format date string for storage in database
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
     * Sync trades for a single account (max 500 trades per cycle, timestamp aware)
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

        $this->line("Total trades available in range: {$total}");

        // Fetch trades using pagination with limit
        $orders = [];
        $pageSize = 100; // MT5 API returns max 100 records per page
        $pageCount = 0;
        $hitTradeLimit = false;
        $position = $account->last_trade_sync_position ?? 0;

        if ($account->last_trade_sync_position) {
            $this->line("Resuming from last position: {$position}");
        }

        $this->line("Starting pagination: total={$total}, pageSize={$pageSize}, maxTrades={$maxTrades}, startPosition={$position}");

        while (count($orders) < $maxTrades && $position < $total) {
            $pageCount++;
            $pageOrders = [];  // Initialize as empty array
            $remainingTotal = $total - $position;
            $remainingToFetch = $maxTrades - count($orders);
            $currentPageSize = min($pageSize, $remainingTotal, $remainingToFetch);

            $this->line("  Page {$pageCount}: position={$position}, pageSize={$currentPageSize}, ordersCollected=" . count($orders));

            $error_code = $this->mt5Service->executeOperation(function ($api) use ($login, $fromDate, $toDate, $position, $currentPageSize, &$pageOrders) {
                return $api->HistoryGetPage($login, $fromDate, $toDate, $position, $currentPageSize, $pageOrders);
            });

            $this->line("    → error_code={$error_code}, recordsReturned=" . count($pageOrders ?? []));

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("Failed to get trades page for account {$login}: " . MTRetCode::GetError($error_code), [
                    'error_code' => $error_code,
                    'page_number' => $pageCount,
                    'position' => $position,
                    'account_id' => $account->id,
                ]);
                $account->update(['trade_sync_status' => 'error']);
                break;
            }

            if (empty($pageOrders)) {
                $this->line("    → Empty response, stopping pagination");
                break;
            }

            $orders = array_merge($orders, $pageOrders);
            $position += count($pageOrders);

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
        }

        $lastTradeTimestamp = $account->last_trade_sync_timestamp;  // Preserve existing timestamp
        if (!empty($orders)) {
            $this->line("Fetched " . count($orders) . " orders total from API");

            // Filter orders to only include those with timestamp GREATER than last synced
            // This ensures we never reprocess the same trades even if date ranges overlap
            $filteredOrders = $orders;
            if ($account->last_trade_sync_timestamp) {
                $filteredOrders = array_values(array_filter($orders, function ($order) use ($account) {
                    return $order->TimeDone > $account->last_trade_sync_timestamp;
                }));
                $this->line("Filtered from " . count($orders) . " to " . count($filteredOrders) . " orders (excluding timestamp <= " . $account->last_trade_sync_timestamp . ")");
            }

            if (!empty($filteredOrders)) {
                $lastTradeInfo = $this->processTrades($account, $filteredOrders);
                $lastTradeTimestamp = $lastTradeInfo['last_timestamp'];
            } else {
                $this->warn("No new orders after timestamp filtering");
            }
        } else {
            $this->warn("No orders fetched from API");
        }

        // Determine if this is a complete sync or partial
        $isSyncComplete = !$hitTradeLimit && ($position >= $total);
        $statusToSet = $isSyncComplete ? 'success' : 'partial';

        // Do not overwrite 'error' status if it was already set during pagination failure
        if ($account->trade_sync_status !== 'error') {
            // Update sync progress with position and timestamp
            $account->update([
                'last_trade_sync_at' => now(),
                'last_trade_sync_from' => $this->formatDateForStorage($fromDate),
                'last_trade_sync_to' => $this->formatDateForStorage($toDate),
                'last_trade_sync_timestamp' => $lastTradeTimestamp,
                'last_trade_sync_position' => $position,  // Track pagination position for resuming
                'trade_sync_status' => $statusToSet,
            ]);
        }

        if (!$isSyncComplete) {
            $lastDate = $lastTradeTimestamp ? \Carbon\Carbon::createFromTimestamp($lastTradeTimestamp)->format('Y-m-d H:i:s') : 'unknown';
            $this->warn("Partial sync complete. Last trade: {$lastDate}. Will continue from position {$position} next run.");
        }

        return $isSyncComplete ? true : 'partial';
    }

    /**
     * Process and upsert trades into the database
     * Returns array with trade count and last trade timestamp
     */
    protected function processTrades(Account $account, array $orders): array
    {
        $this->info("Processing " . count($orders) . " orders for account {$account->code}");

        // Show first order structure for debugging
        if (!empty($orders)) {
            $firstOrder = $orders[0];
            $this->line("First order object properties: " . implode(", ", array_keys((array)$firstOrder)));

            // Show timestamp distribution debug info
            $timestamps = array_map(fn($o) => $o->TimeDone, $orders);
            $minTs = min($timestamps);
            $maxTs = max($timestamps);
            $this->line("Order timestamp range: " . $minTs . " (" . \Carbon\Carbon::createFromTimestamp($minTs)->format('Y-m-d H:i:s') . ") to " . $maxTs . " (" . \Carbon\Carbon::createFromTimestamp($maxTs)->format('Y-m-d H:i:s') . ")");
        }

        // Filter out invalid orders and group by ExpertPositionID
        $validOrders = collect($orders)->filter(function ($order) {
            return !empty($order->ExpertPositionID) && !empty($order->Symbol);
        });

        $ordersByPosition = $validOrders->groupBy('ExpertPositionID');
        $this->line("Grouped {$validOrders->count()} valid orders into {$ordersByPosition->count()} positions");

        $tradesToUpsert = [];
        $openTradeCount = 0;
        $closedTradeCount = 0;
        $skippedCount = 0;
        $lastTradeTimestamp = 0;
        $maxTimestamp = 0;

        foreach ($ordersByPosition as $positionId => $positionOrders) {
            $positionOrders = $positionOrders->sortBy('TimeDone');

            if ($positionOrders->count() < 2) {
                // Check if this position already exists in database as open
                // If yes, skip it (close event may be in next sync batch due to position-based pagination)
                $existingTrade = Trade::where('account_id', $account->id)
                    ->where('position_id', $positionId)
                    ->where('status', 'open')
                    ->first();

                if ($existingTrade) {
                    // Position already recorded as open, likely close event in earlier batch
                    // Don't duplicate it, just skip
                    $this->line("  Skipping position {$positionId}: already marked as open (likely resumed scan)");
                    $skippedCount++;
                } else {
                    // New open trade
                    $order = $positionOrders->first();
                    $tradeData = $this->prepareOpenTrade($account, $positionId, $order);
                    if ($tradeData !== null) {
                        $tradesToUpsert[] = $tradeData;
                        $openTradeCount++;
                        //Track the maximum timestamp seen
                        $maxTimestamp = max($maxTimestamp, $order->TimeDone);
                    } else {
                        $skippedCount++;
                    }
                }
            } else {
                // Closed trade
                $openOrder = $positionOrders->first();
                $closeOrder = $positionOrders->last();
                $tradeData = $this->prepareClosedTrade($account, $positionId, $openOrder, $closeOrder);
                if ($tradeData !== null) {
                    $tradesToUpsert[] = $tradeData;
                    $closedTradeCount++;
                    // Track the maximum timestamp seen (use close order as it's more recent)
                    $maxTimestamp = max($maxTimestamp, $closeOrder->TimeDone);
                } else {
                    $skippedCount++;
                }
            }
        }

        $lastTradeTimestamp = $maxTimestamp;

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

        return [
            'count' => count($tradesToUpsert),
            'last_timestamp' => $lastTradeTimestamp,
        ];
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
