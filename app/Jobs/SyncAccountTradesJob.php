<?php

namespace App\Jobs;

use App\Models\Trade;
use Exception;
use App\Models\Ib1;
use App\Models\Symbol;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Services\UniversalMT5Service;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use App\Services\MT5Service;
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
    protected  $account;
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
            $this->mt5Service = app(UniversalMT5Service::class);

            // Connect to MT5 server
            if (!$this->mt5Service->dealerConnect()) {
                Log::error("SyncAccountTradesJob: Failed to connect to MT5 server");
                throw new Exception("Failed to connect to MT5 server");
            }

            // Process each account
            foreach ($this->accountIds as $accountId) {
                $this->processAccount($accountId);
            }
        } catch (\Exception $e) {
            Log::error("SyncAccountTradesJob failed: " . $e->getMessage());
            throw $e; // Ensure the job registers as failed
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

            // Log::info('Syncing account trades for account: ' . $this->account->code);
            $login = $this->account->code;
            $from = 'September 01,2024';
            $to = 'March 31,2080';
            $total = 0;

            // Get total number of trades using universal service
            $error_code = $this->mt5Service->executeOperation(function ($api) use ($login, $from, $to, &$total) {
                return $api->HistoryGetTotal($login, $from, $to, $total);
            });

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error('MT5 ' . $login . ': Failed to get history total');
                return;
            }

            $closedOrderHistory = $total;
            if ($closedOrderHistory == 0) {
                return;
            }

            $offset = Ib1Commission::where('code', $login)->count();
            $total = $closedOrderHistory;

            $maxTries = 10;
            $attempts = 0;
            $processedOrders = [];
            $symbolMappings = Cache::remember('symbol_mappings', now()->addMinutes(30), function () {
                return Symbol::pluck('path', 'symbol')->toArray();
            });

            while ($offset < $total && $attempts < $maxTries) {
                // Get trade history using universal service
                $orders = $this->mt5Service->getTradeHistory($login, strtotime($from), strtotime($to));

                if (!$orders) {
                    Log::error('MT5 ' . $login . ': Failed to get trade history');
                    break;
                }

                if ($orders) {
                    $ibcommissions = [];
                    $orderIdsAndCodes = [];

                    foreach ($orders as $item) {
                        // if ($item->State != 4) {
                        //     continue;
                        // }
                        $symbolWithoutP = $item->Symbol;
                        if (!isset($symbolMappings[$symbolWithoutP])) {
                            try {
                                $symbol = Symbol::where('symbol', $symbolWithoutP)->first();
                                $symbolMappings[$symbolWithoutP] = $symbol ? $symbol->path : 'default/path';
                            } catch (Exception $e) {
                                Log::error('Error fetching symbol: ' . $e->getMessage());
                                $symbolMappings[$symbolWithoutP] = 'error/path';
                            }
                        }

                        $symbolpath = $symbolMappings[$symbolWithoutP];
                        $b = preg_match('/Energy|Indices|Cryptocurrencies/', $symbolpath) ? 0.00001 : 0.0001;
                        if (in_array($item->Order . '-' . $item->Login, $processedOrders)) {
                            continue;
                        }
                        //                    $lotSize = $this->calculateLotSize($item->VolumeInitial, $item->ContractSize);
                        $existingCommission = Ib1Commission::where('order_id', $item->Order)
                            ->where('code', $item->Login)
                            ->limit(1)
                            ->exists();

                        if ($existingCommission) {
                            continue;
                        }

                        $processedOrders[] = $item->Order . '-' . $item->Login;

                        $ibcommissions[] = [
                            'id' => (string)Str::orderedUuid(),
                            'user_id' => $this->account->user_id,
                            'account_id' => $this->account->id,
                            'order_id' => $item->Order,
                            'expert_position_id' => $item->ExpertPositionID,
                            'code' => $item->Login,
                            'init_volume' => $item->VolumeInitial,
                            'symbol' => $symbolWithoutP,
                            'orderstate' => $item->State,
                            'volume' => $item->VolumeInitial * $b,
                            'time_closed' => Carbon::createFromTimestamp($item->TimeDone),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        if (count($ibcommissions) == 500) {
                            try {
                                Ib1Commission::insert($ibcommissions);
                                $this->newTrades = true;
                            } catch (Exception $e) {
                                Log::error('Error inserting commission = : ' . $e->getMessage());
                            }
                            $ibcommissions = [];
                        }
                    }

                    if (count($ibcommissions) > 0) {
                        try {
                            Ib1Commission::insert($ibcommissions);
                            $this->newTrades = true;
                        } catch (Exception $e) {
                            Log::error('Error inserting commission: ' . $e->getMessage());
                        }
                    }
                    $this->processTradeBatch($orders, $this->account);
                    Log::info('Batch processed');
                }

                $offset += count($orders);

                $attempts++;
                if ($attempts >= $maxTries) {
                    Log::warning("Reached max tries for account: $login after $attempts attempts.");
                }
            }

            if ($attempts >= $maxTries) {
                Log::error("Reached maximum attempts for account: $login. Skipping.");
            }
            if ($this->newTrades) {
                Log::info('New Trades exists');
                // ($referral_code, $userId, $ib_acc_plans)
                // info('Dispatching DistributeIbCommissionJob for account: ' . json_encode([$this->referral_code, $this->ib_user_id, $this->ib_acc_plans, $this->account->id]));
                DistributeIbCommissionJob::dispatch($this->referral_code, $this->ib_user_id, $this->ib_acc_plans, $this->account->id);
            }
        } catch (\Exception $e) {
            Log::error("SyncAccountTradesJob failed for account {$accountId}: " . $e->getMessage());
            // Don't throw here since we're processing multiple accounts
        }
    }
    private function processTradeBatch(array $orders, $account)
    {
        $ordersByPosition = collect($orders)->groupBy('ExpertPositionID');
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
