<?php
namespace App\Jobs;

use App\Models\Trade;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Services\MT5Service;
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
    protected $maxRetries = 3;
    protected $retryDelay = 1; // reduced from 2 to 1 second
    protected $batchSize = 500;

    public function __construct($account)
    {
        $this->account = $account;
    }

    public function handle(MT5Service $mt5Service)
    {
        Log::info("Started SyncTrades job for account ID: {$this->account->code}");

        // Get existing trades to check their status
        $existingTrades = Trade::where('account_id', $this->account->id)
            ->get()
            ->keyBy('position_id');

        $mt5Service->connect();
        $api = $mt5Service->getApi();
        $settings = settings();
        $account = ($this->account);

        // Initialize connection
        $api = $mt5Service->getApi();
        $api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));

        // Verify and establish connection
        if (!$api->IsConnected()) {
            $error_code = $api->Connect(
                $settings['mt5_server_ip'],
                $settings['mt5_server_port'],
                300,
                $settings['mt5_server_web_login'],
                $settings['mt5_server_web_password']
            );

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 connection failed for account {$account->code}: " . MTRetCode::GetError($error_code));
                return;
            }
        }

        try {
            if (!$account || !$account->code) {
                Log::error("Account not found");
                return;
            }

            // Verify account exists on MT5 server
            $mt5_user = null;
            $error_code = $api->UserGet($account->code, $mt5_user);
            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 user not found for account {$account->code}: " . MTRetCode::GetError($error_code));
                return;
            }

            $login = $account->code;
            $from = 'March 01, 2016';
            $to = 'March 31, 2080';
            $total = 0;
            $orders = [];

            // Get total with retries
            $error_code = $this->executeWithRetries(function() use ($api, $login, $from, $to, &$total) {
                return $api->HistoryGetTotal($login, $from, $to, $total);
            }, $mt5Service);

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 HistoryGetTotal final error for login {$login}: " . MTRetCode::GetError($error_code));
                return;
            }

            // Get history page with retries
            $error_code = $this->executeWithRetries(function() use ($api, $login, $from, $to, $total, &$orders) {
                return $api->HistoryGetPage($login, $from, $to, 0, $total, $orders);
            }, $mt5Service);

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 HistoryGetPage error for login {$login}: " . MTRetCode::GetError($error_code));
                return;
            }

            $ordersByPosition = collect($orders)->groupBy('ExpertPositionID');
            $tradesToUpsert = [];

            foreach ($ordersByPosition as $positionId => $positionOrders) {
                $positionOrders = $positionOrders->sortBy('TimeDone');
                $existingTrade = $existingTrades->get($positionId);

                // // If trade exists and is closed (has close_time), skip it
                // if ($existingTrade && $existingTrade->close_time !== null) {
                //     continue;
                // }

                // if ($positionOrders->count() < 2) {
                //     $order = $positionOrders->first();
                //     $tradesToUpsert[] = $this->prepareOpenTrade($account, $positionId, $order);
                // } else {
                //     $openOrder = $positionOrders->first();
                //     $closeOrder = $positionOrders->last();
                //     $tradesToUpsert[] = $this->prepareClosedTrade($account, $positionId, $openOrder, $closeOrder);
                // }

                // if (count($tradesToUpsert) >= $this->batchSize) {
                //     $this->processBatch($tradesToUpsert);
                //     $tradesToUpsert = [];
                // }

                Log::warning("position order {$positionOrders->count()} ");
                Log::warning("existing trade {$existingTrade} ");

                if ($positionOrders->count() < 2) {
                    // OPEN TRADE: Insert if does not exist
                    if (!$existingTrade) {
                        $tradesToUpsert[] = $this->prepareOpenTrade($account, $positionId, $positionOrders->first());
                    }
                } else {
                    // CLOSED TRADE: Update existing trade only
                    if ($existingTrade) {
                        $closedTradeData = $this->prepareClosedTrade($account, $positionId, $positionOrders->first(), $positionOrders->last());
                        // Set ID to perform update on the correct row
                        $closedTradeData['id'] = $existingTrade->id;
                        $tradesToUpsert[] = $closedTradeData;
                    } else {
                        // No open trade exists; you can choose to skip or insert fresh closed trade.
                        // To strictly follow your requirement, we will SKIP it:
                        Log::warning("Closed trade found for position_id {$positionId} but no matching open trade exists in DB. Skipping.");
                    }
                }

                if (count($tradesToUpsert) >= $this->batchSize) {
                    $this->processBatch($tradesToUpsert);
                    $tradesToUpsert = [];
                }
            }

            if (!empty($tradesToUpsert)) {
                $this->processBatch($tradesToUpsert);
            }

            Log::info("Completed SyncTrades job for account ID: {$account->code}");
        } catch (\Exception $e) {
            Log::error("Error in SyncTrades job: " . $e->getMessage());
            throw $e;
        }
    }

    protected function executeWithRetries($callback, $mt5Service)
    {
        $attempt = 0;
        do {
            $error_code = $callback();
            if ($error_code == MTRetCode::MT_RET_OK) {
                break;
            }
            $attempt++;
            if ($attempt < $this->maxRetries) {
                // Log::warning("MT5 operation attempt {$attempt} failed: " . MTRetCode::GetError($error_code) . ". Retrying...");
                sleep($this->retryDelay);

                // Reconnect if needed
                if (!$mt5Service->getApi()->IsConnected()) {
                    $mt5Service->connect();
                }
            }
        } while ($attempt < $this->maxRetries);

        return $error_code;
    }

    protected function prepareOpenTrade($account, $positionId, $order)
    {
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
            'volume' => $order->VolumeInitial,
            'volume_ext' => $order->VolumeInitialExt,
        ];
    }

    protected function prepareClosedTrade($account, $positionId, $openOrder, $closeOrder)
    {
        return [
            'account_id' => $account->id,
            'position_id' => $positionId,
            'order_id' => $openOrder->Order,
            'symbol' => $openOrder->Symbol,
            'type' => $openOrder->Type,
            'volume' => $openOrder->VolumeInitial,
            'volume_ext' => $openOrder->VolumeInitialExt,
            'open_price' => $openOrder->PriceCurrent,
            'close_price' => $closeOrder->PriceCurrent,
            'sl' => $openOrder->PriceSL,
            'tp' => $openOrder->PriceTP,
            'open_time' => date('Y-m-d H:i:s', $openOrder->TimeDone),
            'close_time' => date('Y-m-d H:i:s', $closeOrder->TimeDone),
            'state' => $closeOrder->State,
            'comment' => $openOrder->Comment,
            'profit' => ($closeOrder->PriceCurrent - $openOrder->PriceCurrent) * ($openOrder->VolumeInitialExt / 10000000) * $openOrder->ContractSize,
            'status' => 'closed',
            'code' => $account->code,
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }

    protected function processBatch(array $trades)
    {
        // try {
        //     // Use only position_id as unique key to prevent duplicate trades
        //     $uniqueKeys = ['position_id'];
        //     Trade::upsert($trades, $uniqueKeys);
        // } catch (\Exception $e) {
        //     Log::error("Error processing trade batch: " . $e->getMessage());
        //     throw $e;
        // }

        // Log::info("Completed SyncTrades job for account ID: {$this->account->code}");

        try {
            // Use 'id' as the unique key for upsert to update specific rows
            Trade::upsert($trades, ['id']);
        } catch (\Exception $e) {
            Log::error("Error processing trade batch: " . $e->getMessage());
            throw $e;
        }

        Log::info("Processed trade batch for account ID: {$this->account->code}");
    }
}
