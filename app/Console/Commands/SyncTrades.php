<?php

namespace App\Console\Commands;

use App\Models\Trade;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Services\MT5Service;
use App\Services\MailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncTrades extends Command
{
    protected $api;
    protected $mailService;
    protected $mt5Service;

    public function __construct(MT5Service $mt5Service, MailService $mailService)
    {
        parent::__construct();
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-trades';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync MT5 trade history for demo competition accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Account::where('demo', 1)
            ->whereNotNull('competition_month')
            ->whereNotNull('competition_year')
            ->whereNotNull('code')
            ->chunk(500, function ($accounts) {
                $settings = settings();

                foreach ($accounts as $account) {
                    $login = $account->code; // Assuming `login` column exists
                    // Log::info('sync accounts '.json_encode(($account)));
                    // Connect to MT5 server
                    $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
                    $this->api->Connect(
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

                    $error_code = $this->api->HistoryGetTotal($login, $from, $to, $total);

                    if ($error_code != MTRetCode::MT_RET_OK) {
                        Log::error("MT5 HistoryGetTotal error for login {$login}: " . MTRetCode::GetError($error_code));
                        continue;
                    }

                    $error_code = $this->api->HistoryGetPage($login, $from, $to, 0, $total, $orders);

                    if ($error_code != MTRetCode::MT_RET_OK) {
                        Log::error("MT5 HistoryGetPage error for login {$login}: " . MTRetCode::GetError($error_code));
                        continue;
                    }
                    // Process orders and save to trades table
                    $ordersByPosition = collect($orders)->groupBy('ExpertPositionID');

                    // Log::info('sync orders '.json_encode(($orders)));

                    foreach ($ordersByPosition as $positionId => $positionOrders) {
                        // Sort orders by TimeDone to identify open and close trades
                        $positionOrders = $positionOrders->sortBy('TimeDone');

                        if ($positionOrders->count() < 2) {
                            // Single order means trade is still open
                            $order = $positionOrders->first();
                            Trade::updateOrCreate(
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
                                    'status' => 'open'
                                ]
                            );
                        } else {
                            // First order is open, last order is close
                            $openOrder = $positionOrders->first();
                            $closeOrder = $positionOrders->last();

                            Trade::updateOrCreate(
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
                                    'profit' => ($closeOrder->PriceCurrent - $openOrder->PriceCurrent) * ($openOrder->VolumeInitialExt/100000000) * $openOrder->ContractSize, // You may need to calculate this based on your business logic
                                    'status' => 'closed'
                                ]
                            );
                        }
                    }
                }
            });
    }
}
