<?php

namespace App\Jobs;

use App\Events\TradeOpenedEvent;
use App\Models\Trade;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Services\UniversalMT5Service;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;

class SyncTrades implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $account;
    protected $batchSize = 1;

    public function __construct($account = null)
    {
        $this->account = $account;
    }

    public function handle()
    {
        // Guard: Ensure account was provided
        if (!$this->account) {
            Log::error("SyncTrades job dispatched without account parameter");
            return;
        }

        Log::info("Started SyncTrades job for account ID: {$this->account->code}");

        // Get existing trades to check their status
        $existingTrades = Trade::where('account_id', $this->account->id)
            ->get()
            ->keyBy('position_id');

        // Use universal MT5 service with connection pooling
        $mt5Service = app(UniversalMT5Service::class);

        try {
            $api = $mt5Service->getApi();
            $account = $this->account;

            if (!$account || !$account->code) {
                Log::error("Account not found");
                return;
            }

            // Verify account exists on MT5 server using optimized service
            $mt5_user = null;
            $error_code = $mt5Service->executeOperation(function ($api) use ($account, &$mt5_user) {
                return $api->UserGet($account->code, $mt5_user);
            });

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 user not found for account6 {$account->code}: " . MTRetCode::GetError($error_code));
                $mt5Service->reportError();
                return;
            }

            $login = $account->code;
            $from = 'March 01, 2016';
            $to = 'March 31, 2080';
            $total = 0;
            $orders = [];

            // Get total with retries
            $error_code = $mt5Service->executeOperation(function ($api) use ($login, $from, $to, &$total) {
                return $api->HistoryGetTotal($login, $from, $to, $total);
            });

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 HistoryGetTotal final error for login {$login}: " . MTRetCode::GetError($error_code));
                $this->updateSyncStatus($account, 'error');
                return;
            }

            // Skip if no recent orders
            if ($total == 0) {
                $this->updateSyncStatus($account, 'no_changes');
                Log::info("No recent orders found for account {$account->code}");
                return;
            }

            // Get history page with retries
            $error_code = $mt5Service->executeOperation(function ($api) use ($login, $from, $to, $total, &$orders) {
                return $api->HistoryGetPage($login, $from, $to, 0, $total, $orders);
            });

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 HistoryGetPage error for login {$login}: " . MTRetCode::GetError($error_code));
                $this->updateSyncStatus($account, 'error');
                return;
            }

            // // Debug the orders array with more detail
            // Log::info('Total orders: ' . count($orders));
            // Log::info('Orders structure: ' . print_r($orders, true));

            // // If you need to inspect a specific order
            // if (!empty($orders)) {
            //     Log::info('First order details: ' . print_r($orders[0], true));
            // }
            // Log::info("orders for close competition ".json_encode($orders));
            $ordersByPosition = collect($orders)->groupBy('ExpertPositionID');

            $tradesToUpsert = [];

            // Get ALL deals for this account ONCE (moved outside the loop for efficiency)
            $totalDeals = 0;
            $allDeals = [];
            $error_code = $mt5Service->executeOperation(function ($api) use ($account, $from, $to, &$totalDeals) {
                return $api->DealGetTotal($account->code, $from, $to, $totalDeals);
            });
            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("Failed to get total deals for account {$account->code}: " . MTRetCode::GetError($error_code));
                $this->updateSyncStatus($account, 'error');
                return;
            }

            if ($totalDeals > 0) {
                $error_code = $mt5Service->executeOperation(function ($api) use ($account, $from, $to, $totalDeals, &$allDeals) {
                    return $api->DealGetPage($account->code, $from, $to, 0, $totalDeals, $allDeals);
                });
                if ($error_code != MTRetCode::MT_RET_OK) {
                    Log::error("Failed to get deals for account {$account->code}: " . MTRetCode::GetError($error_code));
                    $this->updateSyncStatus($account, 'error');
                    return;
                }
            }

            foreach ($ordersByPosition as $positionId => $positionOrders) {
                $positionOrders = $positionOrders->sortBy('TimeDone');
                $existingTrade = $existingTrades->get($positionId);

                // Filter deals for this specific position from the already-fetched deals
                $filteredDeals = array_values(array_filter($allDeals, fn($deal) => $deal->Order == $positionId));

                $rateProfit = $filteredDeals[0]->RateProfit ?? 1;  // Default to 1 if no deal found

                if ($positionOrders->count() < 2) {
                    // OPEN TRADE: Insert if does not exist
                    if (!$existingTrade) {
                        $tradesToUpsert[] = $this->prepareOpenTrade($account, $positionId, $positionOrders->first());
                    }
                } else {
                    // CLOSED TRADE: Update if exists, otherwise insert new
                    if ($existingTrade) {
                        $closedTradeData = $this->prepareClosedTrade($account, $positionId, $positionOrders->first(), $positionOrders->last(), $rateProfit);
                        // Set ID to perform update on the correct row
                        $closedTradeData['id'] = $existingTrade->id;
                        $tradesToUpsert[] = $closedTradeData;
                    } else {
                        // No open trade exists but we have a closed trade - insert it
                        $tradesToUpsert[] = $this->prepareClosedTrade($account, $positionId, $positionOrders->first(), $positionOrders->last(), $rateProfit);
                    }
                }

                if (count($tradesToUpsert) >= $this->batchSize) {
                    $this->processBatch($tradesToUpsert);
                    $this->fireTradeOpenedEventsForBatch($account, $tradesToUpsert);
                    $tradesToUpsert = [];
                }
            }
            if (!empty($tradesToUpsert)) {
                $this->processBatch($tradesToUpsert);
                $this->fireTradeOpenedEventsForBatch($account, $tradesToUpsert);
            }

            $this->updateSyncStatus($account, 'success', count($tradesToUpsert));
            Log::info("Completed SyncTrades job for account ID: {$account->code}");
        } catch (\Exception $e) {
            $this->updateSyncStatus($account, 'error');
            Log::error("Error in SyncTrades job: " . $e->getMessage());
            throw $e;
        } finally {
            // Connection will be automatically returned to pool by UniversalMT5Service
        }
    }

    protected function prepareOpenTrade($account, $positionId, $order)
    {
        // CRITICAL: Validate position_id before creating trade data
        if (empty($positionId) || $positionId == 0 || $positionId === '0') {
            $this->logInvalidPositionId('open', $account, $positionId, $order, [
                'order_data' => $order,
                'account_id' => $account->id,
                'account_code' => $account->code
            ]);

            throw new \InvalidArgumentException("Invalid position_id for open trade: {$positionId}");
        }
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
            'type' => $order->Type,
            'updated_at' => now(),
            'volume' => $order->VolumeInitial / 10000,
            'volume_ext' => $order->VolumeInitialExt,
        ];
    }

    protected function prepareClosedTrade($account, $positionId, $openOrder, $closeOrder, $rateProfit)
    {
        // CRITICAL: Validate position_id before creating trade data
        if (empty($positionId) || $positionId == 0 || $positionId === '0') {
            $this->logInvalidPositionId('closed', $account, $positionId, $closeOrder, [
                'open_order_data' => $openOrder,
                'close_order_data' => $closeOrder,
                'rate_profit' => $rateProfit,
                'account_id' => $account->id,
                'account_code' => $account->code
            ]);

            throw new \InvalidArgumentException("Invalid position_id for closed trade: {$positionId}");
        }

        // log::info("account->code : {$account->code}");
        // log::info("closeOrder->PriceCurrent : {$closeOrder->PriceCurrent}");
        // log::info("openOrder->PriceCurrent : {$openOrder->PriceCurrent}");
        // log::info("openOrder->VolumeInitialExt : {$openOrder->VolumeInitialExt}");
        // log::info("openOrder->ContractSize : {$openOrder->ContractSize}");
        // log::info("rateProfit : {$rateProfit}");
        $multiplier = $openOrder->Type ? -1 : 1;
        return [
            'account_id' => $account->id,
            'position_id' => $positionId,
            'order_id' => $openOrder->Order,
            'symbol' => $openOrder->Symbol,
            'type' => $openOrder->Type,
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

        // Log::info("Processed trade batch for account ID: {$this->account->code}");
    }

    /**
     * Fire TradeOpenedEvent for each open trade in the batch (upsert does not fire model events).
     */
    protected function fireTradeOpenedEventsForBatch(Account $account, array $tradesToUpsert): void
    {
        $openPositionIds = collect($tradesToUpsert)
            ->filter(fn ($t) => ($t['status'] ?? '') === 'open')
            ->pluck('position_id')
            ->unique()
            ->values();

        if ($openPositionIds->isEmpty()) {
            return;
        }

        $user = $account->user;
        if (!$user || empty($user->email)) {
            return;
        }

        $trades = Trade::where('account_id', $account->id)
            ->whereIn('position_id', $openPositionIds->all())
            ->where('status', 'open')
            ->get();

        foreach ($trades as $trade) {
            event(new TradeOpenedEvent($user, $trade));
        }
    }

    protected function updateSyncStatus(Account $account, string $status, int $tradesCount = 0): void
    {
        $syncStatus = match ($status) {
            'success', 'no_changes' => 'synced',
            'error' => 'error',
            default => 'pending'
        };

        $account->update([
            'last_balance_sync_at' => now(),
            'last_sync_attempt_at' => now(),
            'sync_status' => $syncStatus,
            'sync_error' => $status === 'error' ? 'Sync failed' : null
        ]);

        // Log::info("Updated sync status for account {$account->code}: {$status} (trades: {$tradesCount})");
    }
    /**
     * Log invalid position_id attempts with comprehensive details for admin investigation
     */
    protected function logInvalidPositionId(string $tradeType, $account, $positionId, $order, array $context = []): void
    {
        $logData = array_merge([
            'trade_type' => $tradeType,
            'position_id' => $positionId,
            'account_id' => $account->id,
            'account_code' => $account->code,
            'account_demo' => $account->demo,
            'order_id' => $order->Order ?? 'unknown',
            'order_symbol' => $order->Symbol ?? 'unknown',
            'order_type' => $order->Type ?? 'unknown',
            'order_volume' => $order->VolumeInitial ?? 'unknown',
            'order_price' => $order->PriceCurrent ?? 'unknown',
            'order_time_done' => $order->TimeDone ?? 'unknown',
            'order_comment' => $order->Comment ?? '',
            'timestamp' => now(),
            'job_id' => $this->job->getJobId() ?? 'unknown',
            'queue' => $this->job->getQueue() ?? 'unknown',
            'severity' => 'CRITICAL',
            'issue_type' => 'INVALID_POSITION_ID_SYNC_TRADES_JOB',
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15)
        ], $context);

        // Log critical data integrity issue with full context
        Log::critical("🚨 INVALID POSITION_ID in SyncTrades: {$tradeType} trade with position_id = {$positionId}", $logData);

        // Log to admin activity log for dashboard visibility
        activity('trade_data_integrity')
            ->withProperties($logData)
            ->log("🚨 CRITICAL: Invalid position_id ({$positionId}) in {$tradeType} trade for account {$account->code}");

        // Send immediate admin notification if configured
        try {
            Log::channel('slack')->critical("Invalid position_id detected in legacy trade sync", [
                'account' => $account->code,
                'position_id' => $positionId,
                'trade_type' => $tradeType,
                'order_id' => $order->Order ?? 'unknown'
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to send admin notification for invalid position_id: " . $e->getMessage());
        }
    }
}
