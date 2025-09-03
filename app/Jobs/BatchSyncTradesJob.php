<?php

namespace App\Jobs;

use App\Models\Trade;
use App\Models\Account;
use App\Services\OptimizedMT5Service;
use App\MT5\MTRetCode;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Carbon\Carbon;

/**
 * Batch Sync Trades Job - Multiple Accounts per Connection
 *
 * This job processes multiple accounts in a single job to:
 * 1. Reuse MT5 connection across accounts (major performance gain)
 * 2. Reduce job overhead (fewer queue items)
 * 3. Better resource utilization
 * 4. Maintain reliability with proper error handling per account
 */
class BatchSyncTradesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $accounts;
    protected $fromTimes;
    public $timeout = 300; // 5 minutes for batch
    public $tries = 2;

    public function __construct(array $accounts, array $fromTimes = [])
    {
        // Convert Account models to serializable array format
        $this->accounts = collect($accounts)->map(function ($account) {
            return [
                'id' => $account->id,
                'code' => $account->code,
                'demo' => $account->demo,
                'last_balance_sync_at' => $account->last_balance_sync_at,
                'last_trade_at' => $account->last_trade_at,
            ];
        })->toArray();

        $this->fromTimes = $fromTimes;

        // Set timeout based on number of accounts with more generous timing
        // Base: 10 minutes, then 90 seconds per account + 5 minute buffer
        $this->timeout = max(600, count($accounts) * 90 + 300);
    }

    public function handle(OptimizedMT5Service $mt5Service)
    {
        $accountCodes = collect($this->accounts)->pluck('code')->join(', ');
        $accountCount = count($this->accounts);

        Log::info("Starting BatchSyncTradesJob for {$accountCount} accounts: {$accountCodes}");

        $startTime = now();
        $results = [
            'processed' => 0,
            'success' => 0,
            'errors' => 0,
            'no_changes' => 0,
            'not_found' => 0
        ];

        try {
            // Single optimized MT5 connection for all accounts
            if (!$mt5Service->connect()) {
                throw new \Exception("Failed to establish MT5 connection");
            }
            $api = $mt5Service->getApi();

            foreach ($this->accounts as $index => $accountData) {
                try {
                    // Convert array back to Account model for processing
                    $account = Account::find($accountData['id']);
                    if (!$account) {
                        Log::warning("Account {$accountData['code']} not found in database");
                        $results['not_found']++;
                        $results['processed']++;
                        continue;
                    }

                    $fromTime = $this->fromTimes[$index] ?? now()->subDays(7);
                    $result = $this->syncSingleAccount($api, $account, $fromTime);
                    $results[$result]++;
                    $results['processed']++;

                    Log::info("Account {$account->code}: {$result}");
                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['processed']++;
                    Log::error("Error syncing account {$accountData['code']}: " . $e->getMessage());

                    // Report error to connection pool for adaptive management
                    $mt5Service->reportError();
                }

                // Small delay between accounts to avoid overwhelming MT5
                if ($index < count($this->accounts) - 1) {
                    usleep(250000); // 0.25 second - reduced since we share connection
                }
            }
        } catch (\Exception $e) {
            Log::error("BatchSyncTradesJob failed: " . $e->getMessage());
            throw $e;
        }

        $duration = $startTime->diffInSeconds(now());
        $avgPerAccount = round($duration / $accountCount, 2);

        Log::info("BatchSyncTradesJob completed: {$results['processed']} accounts in {$duration}s (avg: {$avgPerAccount}s/account). " .
            "Success: {$results['success']}, No changes: {$results['no_changes']}, Errors: {$results['errors']}, Not found: {$results['not_found']} accounts: {$accountCodes}");
    }

    protected function syncSingleAccount($api, Account $account, Carbon $fromTime): string
    {
        if (!$account->code) {
            return 'error';
        }

        // Quick user check
        $mt5_user = null;
        $error_code = $api->UserGet($account->code, $mt5_user);

        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::warning("MT5 user not found for account {$account->code}");
            $this->updateSyncStatus($account, 'not_found');
            return 'not_found';
        }

        try {
            // Get existing trades to check their status
            $existingTrades = Trade::where('account_id', $account->id)
                ->get()
                ->keyBy('position_id');

            $login = $account->code;
            $fromDate = $fromTime->format('F d, Y');
            $toDate = now()->addHours(4)->format('F d, Y');
            $total = 0;
            $orders = [];

            // Get total with retries
            $error_code = $this->executeWithRetries(function () use ($api, $login, $fromDate, $toDate, &$total) {
                return $api->HistoryGetTotal($login, $fromDate, $toDate, $total);
            });

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 HistoryGetTotal final error for login {$login}: " . MTRetCode::GetError($error_code));
                $this->updateSyncStatus($account, 'error');
                return 'error';
            }

            // Skip if no recent orders
            if ($total == 0) {
                $this->updateSyncStatus($account, 'no_changes');
                return 'no_changes';
            }

            // Get history page with retries
            $error_code = $this->executeWithRetries(function () use ($api, $login, $fromDate, $toDate, $total, &$orders) {
                return $api->HistoryGetPage($login, $fromDate, $toDate, 0, $total, $orders);
            });
            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 HistoryGetPage error for login {$login}: " . MTRetCode::GetError($error_code));
                $this->updateSyncStatus($account, 'error');
                return 'error';
            }

            // Debug logging for account 253538
            // if ($account->code == 253538) {
            //     Log::info("=== DEBUG: Account 253538 - Orders Data ===");
            //     Log::info("Total orders found: " . count($orders));
            //     Log::info("Orders raw data: " . print_r($orders, true));
            //     foreach ($orders as $index => $order) {
            //         Log::info("order  ".json_encode($order));
            //         if (is_object($order)) {
            //             Log::info("Order {$index}: " . json_encode([
            //                 'Order' => $order->Order ?? 'NULL',
            //                 'ExpertPositionID' => $order->ExpertPositionID ?? 'NULL',
            //                 'Type' => $order->Type ?? 'NULL',
            //                 'Symbol' => $order->Symbol ?? 'NULL',
            //                 'VolumeInitial' => $order->VolumeInitial ?? 'NULL',
            //                 'PriceCurrent' => $order->PriceCurrent ?? 'NULL',
            //                 'TimeDone' => $order->TimeDone ?? 'NULL',
            //                 'State' => $order->State ?? 'NULL',
            //                 'Comment' => $order->Comment ?? 'NULL',
            //             ]));
            //         } else {
            //             Log::info("Order {$index}: " . print_r($order, true));
            //         }
            //     }
            // }

            $ordersByPosition = collect($orders)->groupBy('ExpertPositionID');
            $tradesToUpsert = [];
            $savedCount = 0;

            // Get ALL deals for this account ONCE (moved outside the loop for efficiency)
            $totalDeals = 0;
            $allDeals = [];
            $error_code = $api->DealGetTotal($account->code, $fromDate, $toDate, $totalDeals);
            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("Failed to get total deals for account {$account->code}: " . MTRetCode::GetError($error_code));
                $this->updateSyncStatus($account, 'error');
                return 'error';
            }

            if ($totalDeals > 0) {
                $error_code = $api->DealGetPage($account->code, $fromDate, $toDate, 0, $totalDeals, $allDeals);
                if ($error_code != MTRetCode::MT_RET_OK) {
                    Log::error("Failed to get deals for account {$account->code}: " . MTRetCode::GetError($error_code));
                    $this->updateSyncStatus($account, 'error');
                    return 'error';
                }
            }

            foreach ($ordersByPosition as $positionId => $positionOrders) {
                $positionOrders = $positionOrders->sortBy('TimeDone');
                $existingTrade = $existingTrades->get($positionId);

                // Filter deals for this specific position from the already-fetched deals
                $filteredDeals = array_values(array_filter($allDeals, fn($deal) => $deal->Order == $positionId));
                $rateProfit = $filteredDeals[0]->RateProfit ?? 1;  // Default to 1 if no deal found

                // Debug logging for account 135405 deals
                if ($account->code == 135405) {
                    Log::info("=== DEBUG: Account 135405 - Deals for Position {$positionId} ===");
                    Log::info("Total deals found for account: " . count($allDeals));
                    Log::info("Filtered deals for position {$positionId}: " . count($filteredDeals));
                    foreach ($filteredDeals as $dealIndex => $deal) {
                        if (is_object($deal)) {
                            Log::info("Deal {$dealIndex}: " . json_encode([
                                'Deal' => $deal->Deal ?? 'NULL',
                                'Order' => $deal->Order ?? 'NULL',
                                'Action' => $deal->Action ?? 'NULL',
                                'Entry' => $deal->Entry ?? 'NULL',
                                'Symbol' => $deal->Symbol ?? 'NULL',
                                'Volume' => $deal->Volume ?? 'NULL',
                                'Price' => $deal->Price ?? 'NULL',
                                'Profit' => $deal->Profit ?? 'NULL',
                                'RateProfit' => $deal->RateProfit ?? 'NULL',
                                'Time' => $deal->Time ?? 'NULL',
                            ]));
                        } else {
                            Log::info("Deal {$dealIndex}: " . print_r($deal, true));
                        }
                    }
                    Log::info("Using RateProfit: {$rateProfit}");
                }

                if ($positionOrders->count() < 2) {
                    // OPEN TRADE: Insert if does not exist
                    if (!$existingTrade) {
                        $tradeData = $this->prepareOpenTrade($account, $positionId, $positionOrders->first());
                        $tradesToUpsert[] = $tradeData;
                        $savedCount++;

                        // Debug logging for account 135405
                        if ($account->code == 135405) {
                            Log::info("=== DEBUG: Account 135405 - Prepared OPEN Trade Data ===");
                            Log::info("Position {$positionId}: " . json_encode($tradeData));
                        }
                    }
                } else {
                    // CLOSED TRADE: Update if exists, otherwise insert new
                    if ($existingTrade) {
                        $closedTradeData = $this->prepareClosedTrade($account, $positionId, $positionOrders->first(), $positionOrders->last(), $rateProfit);
                        // Set ID to perform update on the correct row
                        $closedTradeData['id'] = $existingTrade->id;
                        $tradesToUpsert[] = $closedTradeData;
                        $savedCount++;

                        // Debug logging for account 135405
                        if ($account->code == 135405) {
                            Log::info("=== DEBUG: Account 135405 - Prepared CLOSED Trade Data (UPDATE) ===");
                            Log::info("Position {$positionId}: " . json_encode($closedTradeData));
                        }
                    } else {
                        // No open trade exists but we have a closed trade - insert it
                        $closedTradeData = $this->prepareClosedTrade($account, $positionId, $positionOrders->first(), $positionOrders->last(), $rateProfit);
                        $tradesToUpsert[] = $closedTradeData;
                        $savedCount++;

                        // Debug logging for account 135405
                        if ($account->code == 135405) {
                            Log::info("=== DEBUG: Account 135405 - Prepared CLOSED Trade Data (INSERT) ===");
                            Log::info("Position {$positionId}: " . json_encode($closedTradeData));
                        }
                    }
                }

                if (count($tradesToUpsert) >= 50) { // Process in batches
                    $this->processBatch($tradesToUpsert);
                    $tradesToUpsert = [];
                }
            }

            if (!empty($tradesToUpsert)) {
                $this->processBatch($tradesToUpsert);
            }

            $this->updateSyncStatus($account, 'success', $savedCount);
            return 'success';
        } catch (\Exception $e) {
            Log::error("Error syncing account {$account->code}: " . $e->getMessage());
            $this->updateSyncStatus($account, 'error');
            return 'error';
        }
    }

    protected function executeWithRetries($callback)
    {
        // Note: This method is now simplified since OptimizedMT5Service 
        // handles retries internally. Keep for backward compatibility.
        return $callback();
    }

    protected function prepareOpenTrade($account, $positionId, $order)
    {
        // Log::info("account code  ".$account->code);
        // Log::info("order  ".json_encode($order));
        // Log::info("order id  ".$order->Order);
        // Log::info("trade type  ".$order->Type);
        return [
            'account_id' => $account->id,
            'close_price' => null,
            'close_time' => null,
            'code' => $account->code,
            'comment' => $order->Comment ?? '',
            'created_at' => now(),
            'open_price' => $order->PriceCurrent,
            'open_time' => date('Y-m-d H:i:s', $order->TimeDone),
            'order_id' => $order->Order,
            'position_id' => $positionId,
            'profit' => 0,
            'sl' => $order->PriceSL,
            'state' => $order->State,
            'status' => 'open',
            'symbol' => $order->Symbol,
            'tp' => $order->PriceTP,
            'type' => $order->Type ? 'sell' : 'buy',
            'updated_at' => now(),
            'volume' => $order->VolumeInitial / 10000,
            'volume_ext' => $order->VolumeInitialExt,
        ];
    }

    protected function prepareClosedTrade($account, $positionId, $openOrder, $closeOrder, $rateProfit)
    {
        $multiplier = $openOrder->Type ? -1 : 1;

        // Log::info("account code  ".$account->code);
        // Log::info("order  ".json_encode($closeOrder));
        // Log::info("order id  ".$closeOrder->Order);
        // Log::info("trade type  ".$closeOrder->Type);

        return [
            'account_id' => $account->id,
            'position_id' => $positionId,
            'order_id' => $openOrder->Order,
            'symbol' => $openOrder->Symbol,
            'type' => $openOrder->Type ? 'sell' : 'buy',
            'volume' => $openOrder->VolumeInitial / 10000,
            'volume_ext' => $openOrder->VolumeInitialExt,
            'open_price' => $openOrder->PriceCurrent,
            'close_price' => $closeOrder->PriceCurrent,
            'sl' => $openOrder->PriceSL,
            'tp' => $openOrder->PriceTP,
            'open_time' => date('Y-m-d H:i:s', $openOrder->TimeDone),
            'close_time' => date('Y-m-d H:i:s', $closeOrder->TimeDone),
            'state' => $closeOrder->State,
            'comment' => $openOrder->Comment,
            'profit' => round((($closeOrder->PriceCurrent - $openOrder->PriceCurrent) * ($openOrder->VolumeInitialExt / 100000000) * $openOrder->ContractSize) * $rateProfit * $multiplier, 2),
            'status' => 'closed',
            'code' => $account->code,
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }

    protected function processBatch(array $trades)
    {
        try {
            // Use both position_id and id for upsert
            // This ensures new trades are inserted by position_id
            // and existing trades are updated by id
            Trade::upsert(
                $trades,
                ['position_id'], // unique identifier
                ['id', 'close_price', 'close_time', 'state', 'status', 'profit', 'updated_at'] // columns to update
            );
        } catch (\Exception $e) {
            Log::error("Error processing trade batch: " . $e->getMessage());
            throw $e;
        }
    }

    protected function updateSyncStatus(Account $account, string $status, int $tradesCount = 0): void
    {
        $syncStatus = match ($status) {
            'success', 'no_changes' => 'synced',
            'not_found' => 'error',
            'error' => 'error',
            default => 'pending'
        };

        $syncError = null;
        if ($status === 'error') {
            $syncError = 'Sync failed';
        } elseif ($status === 'not_found') {
            $syncError = 'MT5 account not found';
        }

        $account->update([
            'last_balance_sync_at' => now(),
            'last_sync_attempt_at' => now(),
            'sync_status' => $syncStatus,
            'sync_error' => $syncError
        ]);

        Log::info("Updated sync status for account {$account->code}: {$status} -> {$syncStatus} (trades: {$tradesCount})");
    }

    protected function updateLastTradeTime(Account $account, $orders): void
    {
        if (empty($orders)) {
            return;
        }

        // Find the most recent trade time
        $latestTime = 0;
        foreach ($orders as $order) {
            $latestTime = max($latestTime, $order->TimeDone, $order->TimeSetup);
        }

        if ($latestTime > 0) {
            $account->update([
                'last_trade_at' => Carbon::createFromTimestamp($latestTime)
            ]);
        }
    }

    protected function connectWithRetry(OptimizedMT5Service $mt5Service, int $maxRetries = 3): void
    {
        // This method is now deprecated - OptimizedMT5Service handles retries internally
        if (!$mt5Service->connect()) {
            throw new \Exception("Failed to connect to MT5 after {$maxRetries} attempts");
        }
    }
}
