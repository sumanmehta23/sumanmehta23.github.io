<?php

namespace App\Jobs;

use Carbon\Carbon;
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

class SyncAllAccountsTradesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $account;
    protected $maxRetries = 3;
    protected $retryDelay = 2; // Increased retry delay
    protected $batchSize = 1;

    // Job configuration - using config values
    public $timeout;
    public $maxExceptions;
    public $backoff;
    public $tries;

    public function __construct($account)
    {
        $this->account = $account;

        // Set job configuration from config file
        $this->timeout = config('sync-all-trades.job_timeout', 300);
        $this->maxExceptions = config('sync-all-trades.max_exceptions', 3);
        $this->tries = config('sync-all-trades.max_retries', 3);

        $baseDelay = config('sync-all-trades.retry_delay_base', 10);
        $this->backoff = [$baseDelay, $baseDelay * 3, $baseDelay * 6];
    }

    public function handle(UniversalMT5Service $mt5Service)
    {
        try {
            // Add random delay to prevent simultaneous connections (configurable)
            $minDelay = config('sync-all-trades.random_delay_min', 2);
            $maxDelay = config('sync-all-trades.random_delay_max', 8);
            $randomDelay = rand($minDelay, $maxDelay);
            sleep($randomDelay);

            Log::info("Started SyncAllAccountsTradesJob for account ID: {$this->account->code} (delayed {$randomDelay}s)");

            // Get existing trades to check their status
            $existingTrades = Trade::where('account_id', $this->account->id)
                ->get()
                ->keyBy('position_id');

            // Use a dedicated connection with longer timeout for this job
            $this->connectWithRetry($mt5Service);
            $api = $mt5Service->getApi();
            $settings = settings();
            $account = $this->account;

            if (!$account || !$account->code) {
                Log::error("Account not found or missing code");
                return;
            }

            // Verify account exists on MT5 server
            $mt5_user = null;
            $error_code = $api->UserGet($account->code, $mt5_user);

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::warning("MT5 user not found for account5 {$account->code}: " . MTRetCode::GetError($error_code));

                // Log this as a warning, but don't flag the account since we can't modify the schema
                Log::warning("Account {$account->code} not found on MT5 server and will be skipped in future syncs");

                return;
            }

            $login = $account->code;
            $from = 'March 01, 2016';
            $to = 'March 31, 2080';
            $total = 0;
            $orders = [];

            // Get total with retries
            $error_code = $this->executeWithRetries(function () use ($api, $login, $from, $to, &$total) {
                return $api->HistoryGetTotal($login, $from, $to, $total);
            }, $mt5Service);

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 HistoryGetTotal final error for login {$login}: " . MTRetCode::GetError($error_code));
                return;
            }

            // Get history page with retries
            $error_code = $this->executeWithRetries(function () use ($api, $login, $from, $to, $total, &$orders) {
                return $api->HistoryGetPage($login, $from, $to, 0, $total, $orders);
            }, $mt5Service);

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 HistoryGetPage error for login {$login}: " . MTRetCode::GetError($error_code));
                return;
            }

            if (empty($orders)) {
                Log::info("No trade history found for account {$account->code}");
                return;
            }

            $ordersByPosition = collect($orders)->groupBy('ExpertPositionID');
            $tradesToUpsert = [];
            $tradesProcessed = 0;

            foreach ($ordersByPosition as $positionId => $positionOrders) {
                $positionOrders = $positionOrders->sortBy('TimeDone');
                $existingTrade = $existingTrades->get($positionId);

                // Get total number of deals first
                $totalDeals = 0;
                $error_code = $api->DealGetTotal($account->code, $from, $to, $totalDeals);
                if ($error_code != MTRetCode::MT_RET_OK) {
                    Log::error("Failed to get total deals: " . MTRetCode::GetError($error_code));
                    continue;
                }

                // Get the deals
                $deals = [];
                $error_code = $api->DealGetPage($account->code, $from, $to, 0, $totalDeals, $deals);
                if ($error_code != MTRetCode::MT_RET_OK) {
                    Log::error("Failed to get deals: " . MTRetCode::GetError($error_code));
                    continue;
                }

                $filteredDeals = array_values(array_filter($deals, fn($deal) => $deal->Order == $positionId));
                $rateProfit = $filteredDeals[0]->RateProfit ?? 1;

                if ($positionOrders->count() < 2) {
                    // OPEN TRADE: Insert if does not exist
                    if (!$existingTrade) {
                        $tradesToUpsert[] = $this->prepareOpenTrade($account, $positionId, $positionOrders->first());
                        $tradesProcessed++;
                    }
                } else {
                    // CLOSED TRADE: Update if exists, otherwise insert new
                    if ($existingTrade) {
                        $closedTradeData = $this->prepareClosedTrade($account, $positionId, $positionOrders->first(), $positionOrders->last(), $rateProfit);
                        $closedTradeData['id'] = $existingTrade->id;
                        $tradesToUpsert[] = $closedTradeData;
                        $tradesProcessed++;
                    } else {
                        $tradesToUpsert[] = $this->prepareClosedTrade($account, $positionId, $positionOrders->first(), $positionOrders->last(), $rateProfit);
                        $tradesProcessed++;
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

            Log::info("Completed SyncAllAccountsTradesJob for account {$account->code}. Processed {$tradesProcessed} trades.");
        } catch (\Exception $e) {
            Log::error("Error in SyncAllAccountsTradesJob for account {$this->account->code}: " . $e->getMessage());
            // Don't re-throw the exception to avoid marking the batch as failed due to single account issues
            return;
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
                Log::warning("MT5 operation attempt {$attempt} failed: " . MTRetCode::GetError($error_code) . ". Retrying...");
                sleep($this->retryDelay);
                // No direct reconnect, UniversalMT5Service handles pooling
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
            'commission' => 0,
            'created_at' => now(),
            'open_price' => $order->PriceCurrent,
            'open_time' => Carbon::createFromTimestamp($order->TimeDone)->toDateTimeString(),
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
            'volume' => $order->VolumeInitial / 10000,
            'volume_ext' => $order->VolumeInitialExt,
        ];
    }

    protected function prepareClosedTrade($account, $positionId, $openOrder, $closeOrder, $rateProfit)
    {
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
            'open_time' => Carbon::createFromTimestamp($openOrder->TimeDone)->toDateTimeString(),
            'close_time' => Carbon::createFromTimestamp($closeOrder->TimeDone)->toDateTimeString(),
            'state' => $closeOrder->State,
            'comment' => $openOrder->Comment,
            'commission' => 0,
            'swap' => 0,
            'profit' => round((($closeOrder->PriceCurrent - $openOrder->PriceCurrent) * ($openOrder->VolumeInitialExt / 100000000) * $openOrder->ContractSize) * $rateProfit, 2),
            'status' => 'closed',
            'code' => $account->code,
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }

    protected function processBatch(array $trades)
    {
        try {
            Trade::upsert(
                $trades,
                ['position_id'],
                ['id', 'close_price', 'close_time', 'state', 'status', 'profit', 'code', 'order_id', 'updated_at']
            );
        } catch (\Exception $e) {
            Log::error("Error processing trade batch: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Connect to MT5 with retry logic and rate limiting
     */
    protected function connectWithRetry(UniversalMT5Service $mt5Service)
    {
        // UniversalMT5Service handles connection pooling and retries
        if (!$mt5Service->connect()) {
            throw new \Exception("Failed to connect to MT5 after retries");
        }
    }
}
