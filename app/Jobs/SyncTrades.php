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

class SyncTrades implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $account;

    public function __construct($account)
    {
        $this->account = $account;
    }

    public function handle(MT5Service $mt5Service)
    {
        Log::info("Starting SyncTrades job for account ID: {$this->account->code}");

        $mt5Service->connect();
        $api = $mt5Service->getApi();
        $settings = settings();
        $account = ($this->account);
        if (!$account || !$account->code) {
            Log::error("Account not found or no code available for account ID: {$this->account->code}");
            return;
        }

        $login = $account->code;

        $api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );

        $from = 'March 01, 2016';
        $to = 'March 31, 2080';
        $total = 0;
        $orders = [];


        $error_code = $api->HistoryGetTotal($login, $from, $to, $total);
        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::error("MT5 HistoryGetTotal error for login {$login}: " . MTRetCode::GetError($error_code));
            return;
        }
        // Log::info("Total trades found: {$total}");

        $error_code = $api->HistoryGetPage($login, $from, $to, 0, $total, $orders);
        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::error("MT5 HistoryGetPage error for login {$login}: " . MTRetCode::GetError($error_code));
            return;
        }
        // Log::info("Successfully fetched {$total} trades for login {$login}");

        $ordersByPosition = collect($orders)->groupBy('ExpertPositionID');

        foreach ($ordersByPosition as $positionId => $positionOrders) {
            // Log::info("Processing position ID: {$positionId} with " . $positionOrders->count() . " orders");
            $positionOrders = $positionOrders->sortBy('TimeDone');

            if ($positionOrders->count() < 2) {
                $order = $positionOrders->first();
                try {
                    $trade = Trade::updateOrCreate(
                        [
                            'account_id' => $account->id,
                            'position_id' => $positionId,
                            'order_id' => $order->Order
                        ],
                        [
                            'symbol' => $order->Symbol,
                            'type' => $order->Type,
                            'volume' => $order->VolumeInitial,
                            'volume_ext' => $order->VolumeInitialExt,
                            'open_price' => $order->PriceCurrent,
                            'close_price' => '',
                            'sl' => $order->PriceSL,
                            'tp' => $order->PriceTP,
                            'open_time' => date('Y-m-d H:i:s', $order->TimeDone),
                            'state' => $order->State,
                            'comment' => $order->Comment,
                            'status' => 'open',
                            'code' => $account->code,
                        ]
                    );
                    // Log::info("Stored open trade for position {$positionId}, order {$order->Order}");
                } catch (\Exception $e) {
                    Log::error("Error storing open trade for position {$positionId}: " . $e->getMessage());
                }
            } else {
                $openOrder = $positionOrders->first();
                $closeOrder = $positionOrders->last();
                try {
                    $trade = Trade::updateOrCreate(
                        [
                            'account_id' => $account->id,
                            'position_id' => $positionId,
                            'order_id' => $openOrder->Order
                        ],
                        [
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
                        ]
                    );
                    // Log::info("Stored closed trade for position {$positionId}, orders {$openOrder->Order}/{$closeOrder->Order}");
                } catch (\Exception $e) {
                    Log::error("Error storing closed trade for position {$positionId}: " . $e->getMessage());
                }
            }
        }

        Log::info("Completed SyncTrades job for account ID: {$this->account->code}");
    }
}
