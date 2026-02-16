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
use Illuminate\Support\Facades\Redis;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Batchable;

class SyncAccountTradesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * The number of seconds the job can run.
     * Set to 45 minutes to handle large trade volumes
     */
    public $timeout = 2700;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 2;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public $maxExceptions = 2;

    protected $mt5Service;
    protected $api;
    protected $account;
    protected $accountIds; // Changed from $accountId to support multiple accounts
    protected $newTrades = false;
    protected $referral_code;
    protected $ib_user_id;
    protected $ib_acc_plans = [];
    protected $batchSize = 500;
    protected $totalOrdersProcessed = 0;
    protected $startTime = null;
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
        $this->startTime = microtime(true);
        try {
            $this->mt5Service = app(QueueSafeMT5Service::class);

            // The QueueSafeMT5Service handles connection management internally
            Log::info("SyncAccountTradesJob: Starting trade sync for " . count($this->accountIds) . " accounts", [
                'account_ids' => $this->accountIds,
                'referral_code' => $this->referral_code,
            ]);

            // Process each account
            foreach ($this->accountIds as $accountId) {
                $accountStartTime = microtime(true);
                $this->processAccount($accountId);
                $accountDuration = microtime(true) - $accountStartTime;
                Log::debug("Completed processing account {$accountId}", [
                    'duration_seconds' => round($accountDuration, 2),
                    'total_orders_processed' => $this->totalOrdersProcessed,
                ]);
            }

            $totalDuration = microtime(true) - $this->startTime;
            Log::info("SyncAccountTradesJob: Completed successfully", [
                'duration_seconds' => round($totalDuration, 2),
                'total_orders_processed' => $this->totalOrdersProcessed,
                'accounts_processed' => count($this->accountIds),
            ]);
        } catch (\Exception $e) {
            Log::error("SyncAccountTradesJob failed: " . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'total_orders_processed' => $this->totalOrdersProcessed,
                'elapsed_seconds' => round(microtime(true) - $this->startTime, 2),
            ]);
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
                    Log::error('Error fetching symbol: ' . $e->getMessage(), [
                        'symbol' => $symbolWithoutP,
                        'error' => $e->getMessage(),
                    ]);
                    $symbolMappings[$symbolWithoutP] = 'error/path';
                }
            }

            // Check if commission already exists
            try {
                $exists = Ib1Commission::where('code', $this->account->code)
                    ->where('order_id', $order->Order)
                    ->exists();

                if ($exists) {
                    return null;
                }
            } catch (Exception $e) {
                Log::warning('Error checking existing commission: ' . $e->getMessage(), [
                    'order_id' => $order->Order,
                    'code' => $this->account->code ?? 'unknown',
                ]);
                // Continue anyway, duplicate constraint will catch this in insert
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
            Log::error("Error processing order for IB commission: " . $e->getMessage(), [
                'order_id' => $order->Order ?? 'unknown',
                'error' => $e->getMessage(),
                'account_code' => $this->account->code ?? 'unknown',
            ]);
            return null;
        }
    }

    protected function processAccount($accountId): void
    {
        $accountStartTime = microtime(true);
        if ($accountId == 'a0382ba7-7977-4914-865f-2b306e549c9e') {
            Log::debug('processing sync for 794195');
        }
        try {
            $this->account = Cache::remember("account:{$accountId}", now()->addMinutes(10), function () use ($accountId) {
                return Account::find($accountId);
            });

            if (!$this->account) {
                Log::error('Account not found for id: ' . $accountId);
                return;
            }

            Log::info('Syncing account trades for account: ' . $this->account->code, [
                'account_id' => $accountId,
                'account_code' => $this->account->code,
            ]);

            $login = $this->account->code;
            $from = 'September 01,2024';
            $to = 'March 31,2080';
            $total = 0;

            // Get total number of trades using universal service
            $getTotal_start = microtime(true);
            $error_code = $this->mt5Service->executeOperation(function ($api) use ($login, $from, $to, &$total) {
                return $api->HistoryGetTotal($login, $from, $to, $total);
            });
            $getTotal_duration = microtime(true) - $getTotal_start;

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("Failed to get total trades for account {$login}: " . MTRetCode::GetError($error_code), [
                    'error_code' => $error_code,
                    'account_id' => $accountId,
                ]);
                if ($error_code == MTRetCode::MT_RET_ERR_NOTFOUND) {
                    //soft delete account as not found on mt5
                    $this->account->update([
                        'deletion_type' => 'not_found_on_mt5',
                    ]);

                    $this->account->delete();
                }
                return;
            }

            Log::info("Retrieved total trade count for account {$login}", [
                'total_trades' => $total,
                'duration_seconds' => round($getTotal_duration, 2),
                'account_id' => $accountId,
            ]);

            if ($total == 0) {
                Log::info("No trades found for account {$login}", [
                    'account_id' => $accountId,
                ]);
                return;
            }

            // Use pagination to get all trades
            $orders = [];
            $pageSize = 100; // MT5 API returns max 100 records per page
            $totalPages = ceil($total / $pageSize);
            $pageCount = 0;
            $symbolMappings = $this->getSymbolMappings();
            $pagination_start = microtime(true);

            // Log::info("orders for account trades: " . json_encode($orders));
            while (count($orders) < $total) {
                $pageCount++;
                $currentPageSize = min($pageSize, $total - count($orders));
                $position = count($orders);

                $pageStart = microtime(true);
                $pageOrders = [];
                $error_code = $this->mt5Service->executeOperation(function ($api) use ($login, $from, $to, $position, $currentPageSize, &$pageOrders) {
                    return $api->HistoryGetPage($login, $from, $to, $position, $currentPageSize, $pageOrders);
                });
                $pageDuration = microtime(true) - $pageStart;

                if ($error_code != MTRetCode::MT_RET_OK) {
                    Log::error("Failed to get trades page for account {$login}: " . MTRetCode::GetError($error_code), [
                        'error_code' => $error_code,
                        'page_number' => $pageCount,
                        'position' => $position,
                        'account_id' => $accountId,
                    ]);
                    break;
                }

                if (count($pageOrders) === 0) {
                    Log::warning("Got 0 orders on page {$pageCount}, stopping pagination", [
                        'account_id' => $accountId,
                    ]);
                    break;
                }

                // Process orders in batches for better performance
                $ibCommissionBatch = [];
                $insertedCount = 0;
                foreach ($pageOrders as $order) {
                    try {
                        // Log::info("order for account trades: ".json_encode($order));
                        $ibCommission = $this->processOrderForIbCommission($order, $symbolMappings);
                        if ($ibCommission) {
                            $ibCommissionBatch[] = $ibCommission;
                        }

                        // Insert in batches of batchSize
                        if (count($ibCommissionBatch) == $this->batchSize) {
                            try {
                                Ib1Commission::insert($ibCommissionBatch);
                                $insertedCount += count($ibCommissionBatch);
                                // Log::info('Inserting IB commissions: ' . json_encode($ibCommissionBatch));
                                $this->newTrades = true;
                                $ibCommissionBatch = [];
                            } catch (Exception $e) {
                                $this->newTrades = false;
                                Log::error('Error logging IB commissions batch: ' . $e->getMessage(), [
                                    'batch_size' => count($ibCommissionBatch),
                                    'page_number' => $pageCount,
                                    'account_id' => $accountId,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                    } catch (Exception $e) {
                        Log::error('Error processing order: ' . $e->getMessage(), [
                            'order_id' => $order->Order ?? 'unknown',
                            'page_number' => $pageCount,
                            'account_id' => $accountId,
                        ]);
                        continue;
                    }
                }

                // Insert any remaining records
                if (!empty($ibCommissionBatch)) {
                    try {
                        Ib1Commission::insert($ibCommissionBatch);
                        $insertedCount += count($ibCommissionBatch);
                        // Log::info('Inserting IB commissions: ' . json_encode($ibCommissionBatch));
                    } catch (Exception $e) {
                        $this->newTrades = false;
                        Log::error('Error logging final IB commissions batch: ' . $e->getMessage(), [
                            'batch_size' => count($ibCommissionBatch),
                            'page_number' => $pageCount,
                            'account_id' => $accountId,
                        ]);
                    }
                }

                Log::debug("Completed page {$pageCount} of {$totalPages}", [
                    'page_number' => $pageCount,
                    'total_pages' => $totalPages,
                    'orders_on_page' => count($pageOrders),
                    'inserted_count' => $insertedCount,
                    'duration_seconds' => round($pageDuration, 2),
                    'account_id' => $accountId,
                ]);

                $orders = array_merge($orders, $pageOrders);
                $this->totalOrdersProcessed += count($pageOrders);

                // Small delay between pages to avoid overwhelming MT5
                if (count($orders) < $total) {
                    usleep(50000); // 0.05 second delay between pages
                }
            }

            $pagination_duration = microtime(true) - $pagination_start;
            Log::info("Successfully processed " . count($orders) . " orders for account {$login}", [
                'total_orders' => count($orders),
                'pagination_duration_seconds' => round($pagination_duration, 2),
                'account_id' => $accountId,
            ]);

            // Dispatch DistributeIbCommissionJob with duplicate check and queue limit
            $this->dispatchIbCommissionJobIfAllowed($this->referral_code, $this->ib_user_id, $this->ib_acc_plans, $this->account->id);
            // Dispatch the IB commission job if we had new trades
            // if ($this->newTrades) {
            //     // Log::info("Dispatching DistributeIbCommissionJob for account: {$this->account->id}");
            //     DistributeIbCommissionJob::dispatch($this->referral_code, $this->ib_user_id, $this->ib_acc_plans, $this->account->id);
            // }
        } catch (\Exception $e) {
            Log::error("Error processing account {$accountId}: " . $e->getMessage(), [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'elapsed_seconds' => round(microtime(true) - $accountStartTime, 2),
            ]);
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

    /**
     * Dispatch DistributeIbCommissionJob only if queue limit not exceeded and no duplicate exists
     * 
     * This prevents the distributeibcommission queue from backing up exponentially by:
     * 1. Limiting pending jobs to 50 maximum
     * 2. Checking for duplicate jobs for the same referral_code
     * 3. Only dispatching when conditions are met
     */
    private function dispatchIbCommissionJobIfAllowed($referral_code, $ib_user_id, $ib_acc_plans, $accountId): void
    {
        try {
            $queueName = 'distributeibcommission';
            $maxPendingJobs = 50;
            $redisPrefix = env('HORIZON_PREFIX', 'laravel_horizon:');

            // Count pending jobs in the distributeibcommission queue
            // Redis key format: {prefix}queues:{queue_name}
            $queueKey = $redisPrefix . "queues:{$queueName}";
            $pendingCount = Redis::zcard($queueKey);

            if ($pendingCount >= $maxPendingJobs) {
                Log::warning("DistributeIbCommissionJob queue limit reached", [
                    'pending_jobs' => $pendingCount,
                    'max_allowed' => $maxPendingJobs,
                    'referral_code' => $referral_code,
                    'account_id' => $accountId,
                ]);
                return;
            }

            // Check for duplicate job with same referral_code in the queue
            // We'll scan the queue to see if a job with the same parameters exists
            $jobsKey = $redisPrefix . "jobs";
            $reservedKey = $redisPrefix . "reserved";

            // Get all jobs from the queue (this gets payloads)
            $allJobs = Redis::zrevrange($queueKey, 0, -1);

            foreach ($allJobs as $jobPayload) {
                // Each job payload is JSON encoded, try to decode and check referral_code
                if (
                    strpos($jobPayload, "DistributeIbCommissionJob") !== false &&
                    strpos($jobPayload, $referral_code) !== false
                ) {
                    Log::debug("Duplicate DistributeIbCommissionJob found for referral_code, skipping dispatch", [
                        'referral_code' => $referral_code,
                        'account_id' => $accountId,
                    ]);
                    return;
                }
            }

            // All checks passed, dispatch the job
            DistributeIbCommissionJob::dispatch($referral_code, $ib_user_id, $ib_acc_plans, $accountId);
            Log::debug("Dispatched DistributeIbCommissionJob", [
                'referral_code' => $referral_code,
                'account_id' => $accountId,
                'pending_jobs' => $pendingCount,
            ]);
        } catch (\Exception $e) {
            Log::error("Error in dispatchIbCommissionJobIfAllowed: " . $e->getMessage(), [
                'referral_code' => $referral_code,
                'account_id' => $accountId,
                'trace' => $e->getTraceAsString(),
            ]);
            // Fall back to dispatching anyway if there's an error in the check
            DistributeIbCommissionJob::dispatch($referral_code, $ib_user_id, $ib_acc_plans, $accountId);
        }
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
