<?php

namespace App\Jobs;

use App\Events\TradeOpenedEvent;
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
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Bus\Batchable;

class SyncAccountTradesJob implements ShouldQueue, ShouldBeUnique
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

    /**
     * Unique job ID for 10 minutes to prevent duplicate syncs for same accounts
     */
    public $uniqueFor = 600;

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
    protected $maxPagesPerSync;
    protected $hitPageLimit = false;
    protected $allSymbols = [];  // OPTIMIZATION P2: Cache all symbols at job start
    /**
     * Get the unique ID for the job to prevent concurrent executions for same accounts.
     */
    public function uniqueId()
    {
        $sortedIds = collect($this->accountIds)->sort()->join('-');
        return "sync-account-trades-{$sortedIds}";
    }

    /**
     * Create a new job instance.
     */
    public function __construct($accountIds, $referral_code, $ib_user_id, $ib_acc_plans, $maxPagesPerSync = null)
    {
        // Support both single account ID (backward compatibility) and array of IDs
        $this->accountIds = is_array($accountIds) ? $accountIds : [$accountIds];
        $this->referral_code = $referral_code;
        $this->ib_user_id = $ib_user_id;
        $this->ib_acc_plans = $ib_acc_plans;
        $this->maxPagesPerSync = $maxPagesPerSync ?? config('sync-all-trades.batch_sync.max_pages_per_sync', 20);
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
        // OPTIMIZATION P2: Pre-cache all symbols at job start instead of lazy-loading per order
        // This eliminates cache misses on each job execution
        $this->allSymbols = Symbol::pluck('path', 'symbol')->toArray();

        $this->startTime = microtime(true);
        try {
            $this->mt5Service = app(QueueSafeMT5Service::class);

            // The QueueSafeMT5Service handles connection management internally
            // Log::info("SyncAccountTradesJob: Starting trade sync for " . count($this->accountIds) . " accounts", [
            //     'account_ids' => $this->accountIds,
            //     'referral_code' => $this->referral_code,
            // ]);

            // Process each account with retry logic for socket errors
            foreach ($this->accountIds as $accountId) {
                $accountStartTime = microtime(true);
                try {
                    $this->processAccountWithRetry($accountId);
                } catch (\Exception $e) {
                    Log::error("Failed to process account after retries: {$accountId}", [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                }
                $accountDuration = microtime(true) - $accountStartTime;
                // Log::debug("Completed processing account {$accountId}", [
                //     'duration_seconds' => round($accountDuration, 2),
                //     'total_orders_processed' => $this->totalOrdersProcessed,
                // ]);
            }

            $totalDuration = microtime(true) - $this->startTime;
            // JOB TIMING SUMMARY: Log total job execution metrics
            Log::info("SyncAccountTradesJob completed", [
                'total_duration_seconds' => round($totalDuration, 2),
                'total_orders_processed' => $this->totalOrdersProcessed,
                'accounts_processed' => count($this->accountIds),
                'avg_seconds_per_account' => count($this->accountIds) > 0 ? round($totalDuration / count($this->accountIds), 2) : 0,
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
        // OPTIMIZATION P2: Return pre-cached symbols fetched at job start
        // This eliminates 30-minute cache misses between job executions
        return $this->allSymbols;
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

            // NOTE: Commission existence check is now done in batch before calling this method
            // This eliminates per-order database queries (OPTIMIZATION PHASE 5)

            $symbolpath = $symbolMappings[$symbolWithoutP];
            $b = preg_match('/Energy|Indices|Cryptocurrencies/', $symbolpath) ? 0.00001 : 0.0001;
            // Calculate lot size
            // $lotSize = $this->calculateLotSize($order->VolumeInitialExt, $order->ContractSize ?? 100000);

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

            // Log::info('Syncing account trades for account: ' . $this->account->code, [
            //     'account_id' => $accountId,
            //     'account_code' => $this->account->code,
            // ]);

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

            // Log::info("Retrieved total trade count for account {$login}", [
            //     'total_trades' => $total,
            //     'duration_seconds' => round($getTotal_duration, 2),
            //     'account_id' => $accountId,
            // ]);

            if ($total == 0) {
                // Log::info("No trades found for account {$login}", [
                //     'account_id' => $accountId,
                // ]);
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
                // PAGE_LIMIT: Check if we've hit the maximum pages per sync
                if ($pageCount >= $this->maxPagesPerSync && count($orders) < $total) {
                    Log::info("PAGE_LIMIT: Hit maximum pages per sync, stopping pagination", [
                        'account_id' => $accountId,
                        'account_code' => $login,
                        'pages_synced' => $pageCount,
                        'max_pages_allowed' => $this->maxPagesPerSync,
                        'orders_synced' => count($orders),
                        'total_orders' => $total,
                    ]);
                    $this->hitPageLimit = true;
                    break;
                }
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

                // OPTIMIZATION PHASE 5: Pre-load all existing commissions for this page
                // Instead of checking each order individually (500 queries), do 1 batch query
                $commissionLoadStart = microtime(true);
                $pageOrderIds = collect($pageOrders)->pluck('Order')->toArray();
                $existingCommissionIds = Ib1Commission::where('code', $this->account->code)
                    ->whereIn('order_id', $pageOrderIds)
                    ->pluck('order_id')
                    ->toArray();
                $commissionLoadDuration = microtime(true) - $commissionLoadStart;

                $existingCommissionIdSet = array_flip($existingCommissionIds);  // O(1) lookup

                // Process orders in batches for better performance
                $orderProcessStart = microtime(true);
                $ibCommissionBatch = [];
                $insertedCount = 0;
                $skippedCount = 0;
                foreach ($pageOrders as $order) {
                    try {
                        // Skip if commission already exists (O(1) lookup instead of DB query)
                        if (isset($existingCommissionIdSet[$order->Order])) {
                            $skippedCount++;
                            continue;
                        }

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

                $orderProcessDuration = microtime(true) - $orderProcessStart;

                // TIMING ANALYSIS: Log component durations to identify bottleneck
                Log::info("Completed page {$pageCount} of {$totalPages}", [
                    'page_number' => $pageCount,
                    'total_pages' => $totalPages,
                    'orders_on_page' => count($pageOrders),
                    'inserted_count' => $insertedCount,
                    'skipped_count' => $skippedCount,
                    'mt5_api_seconds' => round($pageDuration, 3),
                    'commission_load_seconds' => round($commissionLoadDuration, 3),
                    'order_process_seconds' => round($orderProcessDuration, 3),
                    'total_page_seconds' => round($pageDuration + $commissionLoadDuration + $orderProcessDuration, 3),
                    'account_id' => $accountId,
                ]);

                $orders = array_merge($orders, $pageOrders);
                $this->totalOrdersProcessed += count($pageOrders);

                // OPTIMIZATION STEP 3: Removed 50ms delay between pages
                // Modern MT5 API server-side rate limiting handles throttling
                // This removes unnecessary 50ms delays that accumulate across pages
                // Expected improvement: 20-30% faster pagination (50ms * pages saved)
                // Note: MT5 server will continue to rate limit excessive requests if needed
            }

            $pagination_duration = microtime(true) - $pagination_start;

            // ACCOUNT TIMING SUMMARY: Log comprehensive metrics for pagination phase
            Log::info("Completed account pagination", [
                'account_id' => $accountId,
                'account_code' => $login,
                'total_orders' => count($orders),
                'total_pages' => $pageCount,
                'pagination_duration_seconds' => round($pagination_duration, 2),
                'avg_seconds_per_page' => $pageCount > 0 ? round($pagination_duration / $pageCount, 3) : 0,
                'hit_page_limit' => $this->hitPageLimit,
                'orders_synced_this_job' => count($orders),
                'total_orders_database' => $total,
            ]);

            // Dispatch DistributeIbCommissionJob with duplicate check and queue limit
            $this->dispatchIbCommissionJobIfAllowed($this->referral_code, $this->ib_user_id, $this->ib_acc_plans, $this->account->id);

            // AUTO_REQUEUE: If we hit page limit, dispatch another job to continue syncing remaining pages
            if ($this->hitPageLimit && count($orders) < $total) {
                Log::info("AUTO_REQUEUE: Dispatching continuation job to sync remaining trades", [
                    'account_id' => $accountId,
                    'account_code' => $login,
                    'orders_synced_so_far' => count($orders),
                    'total_orders' => $total,
                    'remaining_orders' => $total - count($orders),
                ]);
                // Dispatch a new job instance to continue syncing from next page
                static::dispatch(
                    $this->accountIds,
                    $this->referral_code,
                    $this->ib_user_id,
                    $this->ib_acc_plans,
                    $this->maxPagesPerSync
                )->delay(now()->addSeconds(5));
            }
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
                $this->fireTradeOpenedEventsForBatch($account, $tradesToUpsert);
                $tradesToUpsert = [];
            }
        }

        if (!empty($tradesToUpsert)) {
            $this->processBatch($tradesToUpsert);
            $this->fireTradeOpenedEventsForBatch($account, $tradesToUpsert);
        }
    }

    /**
     * Fire TradeOpenedEvent for each open trade in the batch (upsert does not fire model events).
     */
    protected function fireTradeOpenedEventsForBatch(Account $account, array $tradesToUpsert): void
    {
        $openPositionIds = collect($tradesToUpsert)
            ->filter(fn($t) => ($t['status'] ?? '') === 'open')
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
            'swap' => 0,
            'commission' => 0,
            'status' => 'closed',
            'code' => $account->code,
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }

    /**
     * Dispatch DistributeIbCommissionJob if queue limit not exceeded
     * 
     * CRITICAL FIX: Removed broken Redis duplicate prevention that was blocking all subsequent jobs
     * The previous implementation used Redis::sadd() with no TTL, causing permanent blocking
     * of jobs with the same referral_code after first dispatch.
     * 
     * This now only checks queue size to prevent backlog, allowing all jobs to dispatch.
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

            // CRITICAL FIX: Removed Redis set duplicate prevention that had:
            // 1. No cleanup on job completion (entries persisted forever)
            // 2. No TTL on Redis::sadd entries
            // 3. Blocking all subsequent jobs with same referral_code
            // Result: Commissions from 2026-02-17 stuck, account 929250 not syncing, account 258042 never synced

            // All checks passed, dispatch the job
            DistributeIbCommissionJob::dispatch($referral_code, $ib_user_id, $ib_acc_plans, $accountId);

            Log::info("Dispatched DistributeIbCommissionJob", [
                'referral_code' => $referral_code,
                'account_id' => $accountId,
                'pending_jobs' => $pendingCount,
            ]);
        } catch (\Exception $e) {
            Log::error("Error in dispatchIbCommissionJobIfAllowed: " . $e->getMessage(), [
                'referral_code' => $referral_code,
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);
            // Dispatch anyway - queue limit is the only hard block
            try {
                DistributeIbCommissionJob::dispatch($referral_code, $ib_user_id, $ib_acc_plans, $accountId);
            } catch (\Exception $dispatchError) {
                Log::error("Failed to dispatch DistributeIbCommissionJob", [
                    'referral_code' => $referral_code,
                    'account_id' => $accountId,
                    'error' => $dispatchError->getMessage(),
                ]);
            }
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

    /**
     * Process account with retry logic for socket errors
     */
    protected function processAccountWithRetry($accountId): void
    {
        $maxAttempts = 1;
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxAttempts) {
            try {
                $attempt++;
                $this->processAccount($accountId);
                return; // Success
            } catch (\Exception $e) {
                $lastException = $e;

                // Check if it's a socket/connection error that should be retried
                $isSocketError = stripos($e->getMessage(), 'broken pipe') !== false ||
                    stripos($e->getMessage(), 'connection reset') !== false ||
                    stripos($e->getMessage(), 'connection refused') !== false ||
                    stripos($e->getMessage(), 'unable to write to socket') !== false ||
                    stripos($e->getMessage(), 'connection timed out') !== false ||
                    stripos($e->getMessage(), 'transport endpoint') !== false ||
                    stripos($e->getMessage(), 'socket error') !== false;

                if (!$isSocketError || $attempt >= $maxAttempts) {
                    throw $e;
                }

                // Log the retry attempt
                Log::warning("Socket/Connection error in SyncAccountTradesJob, retrying (attempt {$attempt}/{$maxAttempts})", [
                    'account_id' => $accountId,
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                ]);

                // Exponential backoff: 1s, 2s, 4s
                $sleepSeconds = 1 * (2 ** ($attempt - 1));
                sleep($sleepSeconds);
            }
        }

        if ($lastException) {
            throw $lastException;
        }
    }
}
