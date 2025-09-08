<?php

namespace App\Jobs;

use App\Models\Trade;
use Exception;
use App\Models\Ib1;
use App\Models\Symbol;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Services\QueueSafeMT5Service;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use App\Services\UniversalMT5Service;
use App\Models\Ib1Commission;
use App\Models\IbPlanDetails;
use App\Jobs\DistributeIbCommissionJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Batchable;

class SyncAccountTradesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;
    protected $mt5Service;
    protected $api;
    protected $account;
    protected $accountIds; // Changed from $accountId to support multiple accounts
    protected $newTrades = false;
    protected $referral_code;
    protected $ib_user_id;
    protected $ib_acc_plans = [];
    protected $batchSize = 500;
    /**
     * Create a new job instance.
     */
    public function __construct($accountIds, $referral_code, $ib_user_id, $ib_acc_plans)
    {
        // Support both single account ID (backward compatibility) and array of IDs
        $this->accountIds = is_array($accountIds) ? $accountIds : [$accountIds];
        $this->referral_code = $referral_code;
        $this->ib_user_id = $ib_user_id;
        $this->ib_acc_plans = $ib_acc_plans;
        $this->onQueue('syncaccountstrades');
    }
    protected function calculateLotSize($volumeInitialExt, $contractSize)
    {
        if ($contractSize == 0) {
            throw new Exception("Contract size cannot be zero");
        }

        return $volumeInitialExt / (100_000_000 / $contractSize);
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->mt5Service = app(QueueSafeMT5Service::class);

            // The QueueSafeMT5Service handles connection management internally
            Log::info("SyncAccountTradesJob: Starting trade sync for " . count($this->accountIds) . " accounts");

            // Process each account
            foreach ($this->accountIds as $accountId) {
                $this->processAccount($accountId);
            }
        } catch (\Exception $e) {
            Log::error("SyncAccountTradesJob failed: " . $e->getMessage());
            throw $e; // Ensure the job registers as failed
        }
    }

    protected function getSymbolMappings()
    {
        return Cache::remember('symbol_mappings', now()->addMinutes(30), function () {
            return Symbol::pluck('path', 'symbol')->toArray();
        });
    }

    protected function processOrderForIbCommission($order, $symbolMappings)
    {
        //  Log::info("message".json_encode($order));
        try {
            if ($order->State != 4) { // Only process completed orders
                return null;
            }

            $symbolWithoutP = $order->Symbol;
            if (!isset($symbolMappings[$symbolWithoutP])) {
                try {
                    $symbol = Symbol::where('symbol', $symbolWithoutP)->first();
                    $symbolMappings[$symbolWithoutP] = $symbol ? $symbol->path : 'default/path';
                } catch (Exception $e) {
                    Log::error('Error fetching symbol: ' . $e->getMessage());
                    $symbolMappings[$symbolWithoutP] = 'error/path';
                }
            }

            // Check if commission already exists
            $exists = Ib1Commission::where('code', $this->account->code)
                ->where('expert_position_id', $order->ExpertPositionID)
                ->exists();

            if ($exists) {
                return null;
            }

            $symbolpath = $symbolMappings[$symbolWithoutP];
            $b = preg_match('/Energy|Indices|Cryptocurrencies/', $symbolpath) ? 0.00001 : 0.0001;
            // Calculate lot size
            $lotSize = $this->calculateLotSize($order->VolumeInitialExt, $order->ContractSize ?? 100000);

            // Prepare IB commission data
            return [
                'id' => (string)Str::orderedUuid(),
                'user_id' => $this->account->user_id,
                'account_id' => $this->account->id,
                'order_id' => $order->Order,
                'expert_position_id' => $order->ExpertPositionID,
                'code' => $order->Login,
                'init_volume' => $order->VolumeInitial,
                'symbol' => $symbolWithoutP,
                'orderstate' => $order->State,
                'volume' => $order->VolumeInitial * $b,
                'time_closed' => Carbon::createFromTimestamp($order->TimeDone),
                'created_at' => now(),
                'updated_at' => now()
            ];

        } catch (\Exception $e) {
            Log::error("Error processing order for IB commission: " . $e->getMessage());
            return null;
        }
    }

   protected function processAccount($accountId): void
    {
        try {
            $this->account = Cache::remember("account:{$accountId}", now()->addMinutes(10), function () use ($accountId) {
                return Account::find($accountId);
            });

            if (!$this->account) {
                Log::error('Account not found for id: ' . $accountId);
                return;
            }

            Log::info('Syncing account trades for account: ' . $this->account->code);
            $login = $this->account->code;
            $from = 'September 01,2024';
            $to = 'March 31,2080';
            $total = 0;

            // Get total number of trades using universal service
            $error_code = $this->mt5Service->executeOperation(function ($api) use ($login, $from, $to, &$total) {
                return $api->HistoryGetTotal($login, $from, $to, $total);
            });

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("Failed to get total trades for account {$login}: " . MTRetCode::GetError($error_code));
                return;
            }

            if ($total == 0) {
                Log::info("No trades found for account {$login}");
                return;
            }

            // Use pagination to get all trades
            $orders = [];
            $pageSize = 1000; // Fetch 1000 orders at a time
            $totalPages = ceil($total / $pageSize);
            $pageCount = 0;
            $symbolMappings = $this->getSymbolMappings();

            while (count($orders) < $total) {
                $pageCount++;
                $currentPageSize = min($pageSize, $total - count($orders));
                $position = count($orders);

                $pageOrders = [];
                $error_code = $this->mt5Service->executeOperation(function ($api) use ($login, $from, $to, $position, $currentPageSize, &$pageOrders) {
                    return $api->HistoryGetPage($login, $from, $to, $position, $currentPageSize, $pageOrders);
                });

                if ($error_code != MTRetCode::MT_RET_OK) {
                    Log::error("Failed to get trades page for account {$login}: " . MTRetCode::GetError($error_code));
                    break;
                }

                if (count($pageOrders) === 0) {
                    Log::warning("Got 0 orders on page {$pageCount}, stopping pagination");
                    break;
                }

                // Process orders in batches for better performance
                $ibCommissionBatch = [];
                foreach ($pageOrders as $order) {
                    $ibCommission = $this->processOrderForIbCommission($order, $symbolMappings);
                    if ($ibCommission) {
                        $ibCommissionBatch[] = $ibCommission;
                    }

                    // Insert in batches of batchSize
                    if (count($ibCommissionBatch) >= $this->batchSize) {
                        Ib1Commission::insert($ibCommissionBatch);
                        $ibCommissionBatch = [];
                    }
                }

                // Insert any remaining records
                if (!empty($ibCommissionBatch)) {
                    Log::info('Processing remaining IB commissions batch');
                    Ib1Commission::insert($ibCommissionBatch);
                    $this->newTrades = true;
                }

                $orders = array_merge($orders, $pageOrders);

                // Small delay between pages to avoid overwhelming MT5
                if (count($orders) < $total) {
                    usleep(50000); // 0.05 second delay between pages
                }
            }

            Log::info("Successfully processed " . count($orders) . " orders for account {$login}");

            // Dispatch the IB commission job if we had new trades
            if ($this->newTrades) {
                Log::info("Dispatching DistributeIbCommissionJob for account: {$this->account->id}");
                DistributeIbCommissionJob::dispatch($this->referral_code, $this->ib_user_id, $this->ib_acc_plans, $this->account->id);
            }
        } catch (\Exception $e) {
            Log::error("Error processing account {$accountId}: " . $e->getMessage());
            throw $e;
        }
    }
    private function processTradeBatch(array $orders, $account)
    {
        // $ordersByPosition = collect($orders)->groupBy('ExpertPositionID');
        $ordersByPosition = collect($orders)->groupBy('PositionID');
        $tradesToUpsert = [];

        foreach ($ordersByPosition as $positionId => $positionOrders) {
            $positionOrders = $positionOrders->sortBy('TimeDone');

            if ($positionOrders->count() < 2) {
                $order = $positionOrders->first();
                $tradesToUpsert[] = $this->prepareOpenTrade($account, $positionId, $order);
            } else {
                $openOrder = $positionOrders->first();
                $closeOrder = $positionOrders->last();
                $tradesToUpsert[] = $this->prepareClosedTrade($account, $positionId, $openOrder, $closeOrder);
            }

            if (count($tradesToUpsert) >= $this->batchSize) {
                $this->processBatch($tradesToUpsert);
                $tradesToUpsert = [];
            }
        }

        if (!empty($tradesToUpsert)) {
            $this->processBatch($tradesToUpsert);
        }
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
            'volume' => $openOrder->Volume,
            'volume_ext' => $openOrder->Volume,
            'open_price' => $openOrder->PriceCurrent,
            'close_price' => $closeOrder->PriceCurrent,
            'sl' => $openOrder->PriceSL,
            'tp' => $openOrder->PriceTP,
            'open_time' => date('Y-m-d H:i:s', $openOrder->TimeDone),
            'close_time' => date('Y-m-d H:i:s', $closeOrder->TimeDone),
            'state' => $closeOrder->State,
            'comment' => $openOrder->Comment,
            'profit' => ($closeOrder->PriceCurrent - $openOrder->PriceCurrent) * ($openOrder->Volume / 10000000) * $openOrder->ContractSize,
            'status' => 'closed',
            'code' => $account->code,
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }
    protected function processBatch(array $trades)
    {
        try {
            // Use bulk upsert instead of individual updates
            $uniqueKeys = ['account_id', 'position_id', 'order_id'];
            Trade::upsert($trades, $uniqueKeys);
        } catch (\Exception $e) {
            Log::error("Error processing trade batch: " . $e->getMessage());
            throw $e;
        }

        // Log::info("Completed SyncTrades job for account ID: {$this->account->code}");
    }

}
