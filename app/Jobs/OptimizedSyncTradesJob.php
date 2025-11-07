<?php

namespace App\Jobs;

use App\Models\Trade;
use App\Models\Deal;
use App\Jobs\DealSyncJob;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Services\UniversalMT5Service;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Carbon\Carbon;

/**
 * Optimized Sync Trades Job - Incremental Strategy
 *
 * This job implements incremental sync to dramatically reduce MT5 API requests:
 *
 * OPTIMIZATIONS:
 * 1. Incremental Time Range: Only sync trades since last sync
 * 2. Smart Skip Logic: Skip if no recent activity expected
 * 3. Reduced API Calls: Combine operations where possible
 * 4. Activity Tracking: Update last_trade_at for tier management
 *
 * REDUCTION: From 3+(2×positions) requests to 2-5 requests per account
 */
class OptimizedSyncTradesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $account;
    protected $fromTime;
    protected $maxRetries = 2;
    protected $retryDelay = 3;

    // Job configuration
    public $timeout;
    public $maxExceptions;
    public $backoff;
    public $tries;
    public $uniqueFor = 300; // Prevent duplicates for 5 minutes

    /**
     * Get the unique ID for the job to prevent concurrent executions.
     */
    public function uniqueId()
    {
        return "optimized-sync-trades-{$this->account->code}";
    }

    public function __construct(Account $account, Carbon $fromTime)
    {
        $this->account = $account;
        $this->fromTime = $fromTime;

        // Set job configuration from config file
        $this->timeout = config('sync-all-trades.job_timeout', 180); // Reduced timeout
        $this->maxExceptions = config('sync-all-trades.max_exceptions', 2);
        $this->tries = config('sync-all-trades.max_retries', 2);

        $baseDelay = config('sync-all-trades.retry_delay_base', 5);
        $this->backoff = [$baseDelay, $baseDelay * 2];
    }

    public function handle(UniversalMT5Service $mt5Service)
    {
        // Additional duplicate protection using manual cache locks
        $lockKey = "optimized-sync-lock-{$this->account->code}";
        $lock = Cache::lock($lockKey, 300); // 5 minute lock

        if (!$lock->get()) {
            Log::warning("OptimizedSyncTradesJob skipped - another instance is already running for account: {$this->account->code}");
            return;
        }

        try {
            $startTime = microtime(true); // Track execution time

            try {
                // Smaller random delay for optimized version
                $minDelay = config('sync-all-trades.random_delay_min', 1);
                $maxDelay = config('sync-all-trades.random_delay_max', 4);
                $randomDelay = rand($minDelay, $maxDelay);
                sleep($randomDelay);

                Log::info("Started OptimizedSyncTradesJob for account {$this->account->code} from {$this->fromTime->format('Y-m-d H:i:s')} (delayed {$randomDelay}s)");

                // Connect with optimized timeout
                $this->connectWithRetry($mt5Service);
                $api = $mt5Service->getApi();
                $settings = settings();

                if (!$this->account->code) {
                    Log::error("Account missing code");
                    return;
                }

                // OPTIMIZATION 1: Quick user check (1 request)
                $mt5_user = null;
                $error_code = $api->UserGet($this->account->code, $mt5_user);

                if ($error_code != MTRetCode::MT_RET_OK) {
                    Log::warning("MT5 user not found for account {$this->account->code}: " . MTRetCode::GetError($error_code));
                    $this->updateSyncStatus('not_found');
                    return;
                }

                // OPTIMIZATION 2: Incremental time range
                $fromDate = $this->fromTime->format('Y-m-d H:i:s');
                $toDate = now()->format('Y-m-d H:i:s');

                Log::info("Syncing incremental range for {$this->account->code}: {$fromDate} to {$toDate}");

                // OPTIMIZATION 3: Get total with time filter (1 request)
                $total = 0;
                $error_code = $api->HistoryGetTotal($this->account->code, $fromDate, $toDate, $total);

                if ($error_code != MTRetCode::MT_RET_OK) {
                    Log::error("MT5 HistoryGetTotal error for {$this->account->code}: " . MTRetCode::GetError($error_code));
                    $this->updateSyncStatus('error');
                    return;
                }

                // OPTIMIZATION 4: Skip if no recent orders
                if ($total == 0) {
                    Log::info("No new orders for account {$this->account->code} since {$fromDate}");
                    $this->updateSyncStatus('no_changes');
                    return;
                }

                Log::info("Found {$total} recent orders for account {$this->account->code}");

                // OPTIMIZATION 5: Get recent orders only (1 request)
                $orders = [];
                $error_code = $api->HistoryGetPage($this->account->code, $fromDate, $toDate, 0, $total, $orders);

                if ($error_code != MTRetCode::MT_RET_OK) {
                    Log::error("MT5 HistoryGetPage error for {$this->account->code}: " . MTRetCode::GetError($error_code));
                    $this->updateSyncStatus('error');
                    return;
                }

                if (empty($orders)) {
                    Log::info("No orders returned for account {$this->account->code}");
                    $this->updateSyncStatus('no_changes');
                    return;
                }

                // OPTIMIZATION 6: Get deals for all positions at once (1-2 requests)
                $this->syncOrdersOptimized($api, $orders);

                // OPTIMIZATION 7: Update activity tracking
                $this->updateActivityTracking();

                $this->updateSyncStatus('success');

                Log::info("Completed OptimizedSyncTradesJob for account {$this->account->code}. Processed {$total} orders with minimal API calls.");
            } catch (\Exception $e) {
                Log::error("Error in OptimizedSyncTradesJob for account {$this->account->code}: " . $e->getMessage());
                $this->updateSyncStatus('error', $e->getMessage());
                return;
            }
        } finally {
            // Always release the lock
            $lock->release();
        }
    }

    protected function syncOrdersOptimized($api, $orders)
    {
        // Get existing trades for comparison
        $existingTrades = Trade::where('account_id', $this->account->id)
            ->get()
            ->keyBy('position_id');

        // Group orders by position
        $ordersByPosition = collect($orders)->groupBy('ExpertPositionID');
        $tradesToUpsert = [];
        $lastTradeTime = null;

        // SMART INCREMENTAL SYNC: Use last known deal time as starting point for optimized API calls
        $fromDate = $this->fromTime->format('Y-m-d H:i:s');
        $toDate = now()->format('Y-m-d H:i:s');

        // Check if deal data is fresh (based on when we last synced, not deal coverage)
        $isDealDataFresh = $this->account->isDealDataFresh();

        if (!$isDealDataFresh) {
            // Deal data is stale (we haven't synced recently), need to sync first
            $syncRange = $this->account->getRequiredDealSyncRange();
            $syncFromTime = $syncRange['from'];
            $syncToTime = $syncRange['to'];

            Log::info("OptimizedSync[{$this->account->code}]: Deal data not recently synced, syncing deals from {$syncFromTime} to {$syncToTime}");

            // Dispatch deal sync job and wait for it to complete
            $dealSyncJob = new DealSyncJob([$this->account], [$syncFromTime]);
            $dealSyncJob->handle(app(\App\Services\UniversalMT5Service::class));

            // Log::info("OptimizedSync[{$this->account->code}]: Deal sync completed, proceeding with trade sync");
        } else {
            Log::info("OptimizedSync[{$this->account->code}]: Deal data is recently synced (last fetch: {$this->account->deals_last_fetch_at}), using existing deals");
        }

        // PRIORITY OPTIMIZATION: Check MT5 deal total count vs database count for ENTIRE date range FIRST
        // Log::info("OptimizedSync[{$this->account->code}]: Checking MT5 deal total count vs database count for entire requested range to avoid unnecessary processing...");
        $dealTotalStart = microtime(true);
        $mt5DealTotal = 0;
        $fromTimestamp = $this->fromTime->timestamp; // Unix timestamp for MT5 API
        $toTimestamp = now()->addHours(4)->timestamp; // Unix timestamp for MT5 API
        $fromDateForDB = $this->fromTime->format('Y-m-d H:i:s'); // For database queries (SAME RANGE)
        $toDateForDB = now()->addHours(4)->format('Y-m-d H:i:s'); // For database queries (SAME RANGE)

        try {
            $api = app(\App\Services\UniversalMT5Service::class)->getApi();
            $error_code = $api->DealGetTotal($this->account->code, $fromTimestamp, $toTimestamp, $mt5DealTotal);
            $dealTotalTime = round((microtime(true) - $dealTotalStart) * 1000, 2);
            if ($error_code == \App\MT5\MTRetCode::MT_RET_OK) {
                // Count existing deals in our database for the EXACT SAME RANGE
                $dbDealCount = Deal::where('account_id', $this->account->id)
                    ->whereBetween('time_done', [$fromDateForDB, $toDateForDB])
                    ->count();

                Log::info("OptimizedSync[{$this->account->code}]: MT5 deal total: {$mt5DealTotal}, DB deal count: {$dbDealCount} (range: {$fromDateForDB} to {$toDateForDB}, check took {$dealTotalTime}ms)");

                if ($mt5DealTotal == $dbDealCount) {
                    if ($mt5DealTotal > 0) {
                        // Database is perfectly in sync with MT5 - use database deals!
                        Log::info("OptimizedSync[{$this->account->code}]: Deal counts match perfectly! Using DATABASE OPTIMIZATION - no MT5 processing needed.");

                        // Use existing deals in database for processing
                        $dealsQuery = Deal::where('account_id', $this->account->id)
                            ->whereBetween('time_done', [$fromDateForDB, $toDateForDB]);

                        $totalDeals = $dealsQuery->count();
                        $allDeals = $dealsQuery->get()->map(function ($deal) {
                            return (object) [
                                'Deal' => $deal->deal_id,
                                'Order' => $deal->order_id,
                                'Time' => strtotime($deal->time_done),
                                'TimeMsc' => strtotime($deal->time_done) * 1000,
                                'Type' => $deal->type,
                                'Entry' => $deal->entry,
                                'Magic' => $deal->magic ?? 0,
                                'PositionID' => $deal->position_id,
                                'Reason' => $deal->reason ?? '',
                                'Volume' => $deal->volume,
                                'Price' => $deal->price,
                                'Commission' => $deal->commission ?? 0,
                                'Swap' => $deal->swap ?? 0,
                                'Profit' => $deal->profit ?? 0,
                                'Symbol' => $deal->symbol,
                                'Comment' => $deal->comment ?? '',
                                'ExternalID' => $deal->external_id ?? ''
                            ];
                        })->toArray();

                        $timing = round((microtime(true) - $dealTotalStart) * 1000, 2);
                        Log::info("OptimizedSync[{$this->account->code}]: DONE in {$timing}ms using COMPREHENSIVE DEAL COUNT optimization (avoided ALL MT5 processing!)");

                        // Update account status
                        $this->account->update(['last_balance_sync_at' => now()]);
                        return 'success';
                    } else {
                        // Both MT5 and DB report 0 deals for this range
                        // Log::info("OptimizedSync[{$this->account->code}]: Both MT5 and DB report 0 deals for range. No activity to sync.");

                        $this->account->update(['last_balance_sync_at' => now()]);
                        $timing = round((microtime(true) - $dealTotalStart) * 1000, 2);
                        Log::info("OptimizedSync[{$this->account->code}]: DONE in {$timing}ms (no deals optimization)");

                        return 'no_changes';
                    }
                } else {
                    // Deal counts differ - need to sync the difference
                    $dealDifference = $mt5DealTotal - $dbDealCount;
                    Log::info("OptimizedSync[{$this->account->code}]: Deal count mismatch! MT5: {$mt5DealTotal}, DB: {$dbDealCount}, Difference: {$dealDifference}. Proceeding with sync...");
                }
            } else {
                Log::warning("OptimizedSync[{$this->account->code}]: DealGetTotal failed with error: " . \App\MT5\MTRetCode::GetError($error_code) . ". Proceeding with fallback sync...");
            }
        } catch (\Exception $e) {
            Log::warning("OptimizedSync[{$this->account->code}]: DealGetTotal exception: " . $e->getMessage() . ". Proceeding with fallback sync...");
        }

        // INCREMENTAL OPTIMIZATION: Check for last known deal time
        $latestDeal = Deal::where('account_id', $this->account->id)->latest('time_done')->first();
        $optimizationStartTime = microtime(true); // Track optimization timing

        if ($latestDeal) {
            // Use incremental sync from last known deal time
            $incrementalFromTime = Carbon::parse($latestDeal->time_done);
            $incrementalFromDb = $incrementalFromTime->format('Y-m-d H:i:s');

            Log::info("OptimizedSync[{$this->account->code}]: Using INCREMENTAL sync from last deal time: {$incrementalFromDb} (instead of {$fromDate})");

            // Check for NEW deals since last known deal
            $newDealsQuery = Deal::where('account_id', $this->account->id)
                ->where('time_done', '>', $incrementalFromDb)
                ->whereBetween('time_done', [$incrementalFromDb, $toDate]);
            $newDealsCount = $newDealsQuery->count();

            // Also get existing deals in the user-requested range for completeness
            $existingDealsQuery = Deal::where('account_id', $this->account->id)
                ->whereBetween('time_done', [$fromDate, $toDate]);
            $existingDealsCount = $existingDealsQuery->count();

            if ($existingDealsCount > 0) {
                Log::info("OptimizedSync[{$this->account->code}]: Found {$existingDealsCount} existing deals in requested range + {$newDealsCount} new deals since last sync - USING DATABASE OPTIMIZATION!");

                // Use existing deals and skip MT5 API calls
                $dealsQuery = $existingDealsQuery;
            } else {
                // Check if we need to do optimized MT5 API call for NEW deals only
                $daysSinceLastDeal = now()->diffInDays($incrementalFromTime);
                if ($daysSinceLastDeal <= 30) {
                    // Account is relatively active, use incremental approach for MT5 API
                    Log::info("OptimizedSync[{$this->account->code}]: Account last active {$daysSinceLastDeal} days ago. Using INCREMENTAL MT5 API from {$incrementalFromDb}...");

                    // Override fromTime to use incremental approach
                    $this->fromTime = $incrementalFromTime;
                    $fromDate = $incrementalFromTime->format('Y-m-d H:i:s');
                    $fromDateForMT5 = $incrementalFromTime->format('F d, Y'); // For MT5 API
                    $toDateForMT5 = now()->addHours(4)->format('F d, Y'); // For MT5 API
                    $toDate = now()->addHours(4)->format('Y-m-d H:i:s'); // For database queries

                    Log::info("OptimizedSync[{$this->account->code}]: OPTIMIZATION: Reduced MT5 API query range from {$daysSinceLastDeal} days to incremental sync since last deal");

                    // CRITICAL OPTIMIZATION: Check MT5 deal total count vs database count BEFORE expensive pagination
                    Log::info("OptimizedSync[{$this->account->code}]: Checking MT5 deal total count vs database count to avoid unnecessary pagination...");
                    $dealTotalStart = microtime(true);
                    $mt5DealTotal = 0;

                    try {
                        $api = app(\App\Services\UniversalMT5Service::class)->getApi();
                        $error_code = $api->DealGetTotal($this->account->code, $fromDateForMT5, $toDateForMT5, $mt5DealTotal);
                        $dealTotalTime = round((microtime(true) - $dealTotalStart) * 1000, 2);

                        if ($error_code == \App\MT5\MTRetCode::MT_RET_OK) {
                            // Count existing deals in our database for the same range
                            $dbDealCount = Deal::where('account_id', $this->account->id)
                                ->whereBetween('time_done', [$fromDate, $toDate])
                                ->count();

                            Log::info("OptimizedSync[{$this->account->code}]: MT5 deal total: {$mt5DealTotal}, DB deal count: {$dbDealCount} (check took {$dealTotalTime}ms)");

                            if ($mt5DealTotal == $dbDealCount && $mt5DealTotal > 0) {
                                // Database is perfectly in sync with MT5 - no new deals to fetch!
                                Log::info("OptimizedSync[{$this->account->code}]: Deal counts match perfectly! Using DATABASE OPTIMIZATION - no MT5 pagination needed.");

                                // Use existing deals in database for processing
                                $dealsQuery = Deal::where('account_id', $this->account->id)
                                    ->whereBetween('time_done', [$fromDate, $toDate]);

                                $timing = round((microtime(true) - $optimizationStartTime) * 1000, 2);
                                Log::info("OptimizedSync[{$this->account->code}]: DONE in {$timing}ms using DEAL COUNT optimization (avoided 15+ pages of MT5 API!)");
                            } elseif ($mt5DealTotal == 0) {
                                // No deals in MT5 for this range
                                Log::info("OptimizedSync[{$this->account->code}]: MT5 reports 0 deals for range. No activity to sync.");

                                $this->account->update(['last_balance_sync_at' => now()]);
                                $timing = round((microtime(true) - $optimizationStartTime) * 1000, 2);
                                Log::info("OptimizedSync[{$this->account->code}]: DONE in {$timing}ms (MT5 reports no deals)");

                                return 'no_changes';
                            } else {
                                // Deal counts differ - need to sync the difference
                                $dealDifference = $mt5DealTotal - $dbDealCount;
                                Log::info("OptimizedSync[{$this->account->code}]: Deal count mismatch! MT5: {$mt5DealTotal}, DB: {$dbDealCount}, Difference: {$dealDifference}. Proceeding with MT5 sync...");
                                // Create empty query to proceed with MT5 API path
                                $dealsQuery = Deal::where('account_id', $this->account->id)->whereRaw('1 = 0');
                            }
                        } else {
                            Log::warning("OptimizedSync[{$this->account->code}]: DealGetTotal failed with error: " . \App\MT5\MTRetCode::GetError($error_code) . ". Proceeding with fallback MT5 order sync...");
                            // Create empty query to proceed with MT5 API path
                            $dealsQuery = Deal::where('account_id', $this->account->id)->whereRaw('1 = 0');
                        }
                    } catch (\Exception $e) {
                        Log::warning("OptimizedSync[{$this->account->code}]: DealGetTotal exception: " . $e->getMessage() . ". Proceeding with fallback MT5 order sync...");
                        // Create empty query to proceed with MT5 API path
                        $dealsQuery = Deal::where('account_id', $this->account->id)->whereRaw('1 = 0');
                    }
                } else {
                    // Account is inactive for too long, skip expensive API calls
                    Log::info("OptimizedSync[{$this->account->code}]: Account inactive for {$daysSinceLastDeal} days (last deal: {$incrementalFromDb}). Skipping expensive API calls.");

                    $this->account->update(['last_balance_sync_at' => now()]);
                    $timing = round((microtime(true) - $optimizationStartTime) * 1000, 2);
                    Log::info("OptimizedSync[{$this->account->code}]: DONE in {$timing}ms - inactive account optimization");

                    return 'no_changes';
                }

                // Create empty query to proceed with MT5 API path
                $dealsQuery = Deal::where('account_id', $this->account->id)->whereRaw('1 = 0');
            }
        } else {
            Log::info("OptimizedSync[{$this->account->code}]: No existing deals found, proceeding with full MT5 API sync for range {$fromDate} to {$toDate}");
            // Create empty query to proceed with MT5 API path
            $dealsQuery = Deal::where('account_id', $this->account->id)->whereRaw('1 = 0');
        }

        // Fetch deals from local database (either existing deals or empty if going to MT5 API)
        $totalDeals = $dealsQuery->count();
        $allDeals = [];

        if ($totalDeals > 0) {
            $allDeals = $dealsQuery->get()->map(function ($deal) {
                return (object) [
                    'Deal' => $deal->deal_id,
                    'Order' => $deal->order_id,
                    'Position' => $deal->position_id,
                    'Symbol' => $deal->symbol,
                    'Type' => $deal->type,
                    'Action' => $deal->action,
                    'Entry' => $deal->entry,
                    'Volume' => (float) $deal->volume,
                    'Price' => (float) $deal->price,
                    'Profit' => (float) $deal->profit,
                    'Swap' => (float) $deal->swap,
                    'Commission' => (float) $deal->commission,
                    'Comment' => $deal->comment,
                    'Reason' => $deal->reason,
                    'TimeDone' => $deal->time_done->timestamp,
                    'TimeMsc' => $deal->time_msc,
                    'Magic' => $deal->magic,
                    'RateProfit' => (float) $deal->rate_profit,
                    'RateMargin' => (float) $deal->rate_margin,
                ];
            })->toArray();

            Log::info("OptimizedSync[{$this->account->code}]: Fetched {$totalDeals} deals from database");
        }

        // Index deals by order for quick lookup
        $dealsByOrder = collect($allDeals)->groupBy('Order');

        foreach ($ordersByPosition as $positionId => $positionOrders) {
            $positionOrders = $positionOrders->sortBy('TimeDone');
            $existingTrade = $existingTrades->get($positionId);

            // Get deals for this position
            $relevantDeals = $dealsByOrder->get($positionId, collect());
            $rateProfit = $relevantDeals->first()->RateProfit ?? 1;

            if ($positionOrders->count() < 2) {
                // OPEN TRADE: Insert if does not exist
                if (!$existingTrade) {
                    $tradeData = $this->prepareOpenTrade($positionId, $positionOrders->first());
                    $tradesToUpsert[] = $tradeData;

                    // Track latest trade time
                    $tradeTime = Carbon::parse($positionOrders->first()['TimeDone']);
                    if (!$lastTradeTime || $tradeTime->gt($lastTradeTime)) {
                        $lastTradeTime = $tradeTime;
                    }
                }
            } else {
                // CLOSED TRADE: Update if exists, otherwise insert
                $tradeData = $this->prepareClosedTrade($positionId, $positionOrders->first(), $positionOrders->last(), $rateProfit);

                if ($existingTrade) {
                    $tradeData['id'] = $existingTrade->id;
                }

                $tradesToUpsert[] = $tradeData;

                // Track latest trade time
                $closeTime = Carbon::parse($positionOrders->last()['TimeDone']);
                if (!$lastTradeTime || $closeTime->gt($lastTradeTime)) {
                    $lastTradeTime = $closeTime;
                }
            }
        }

        // Batch upsert all trades
        if (!empty($tradesToUpsert)) {
            foreach ($tradesToUpsert as $trade) {
                if (isset($trade['id'])) {
                    // Update existing
                    Trade::where('id', $trade['id'])->update($trade);
                } else {
                    // Insert new
                    Trade::create($trade);
                }
            }
        }

        // Update account's last trade time
        if ($lastTradeTime) {
            $this->account->update(['last_trade_at' => $lastTradeTime]);
        }
    }

    protected function prepareOpenTrade($positionId, $openOrder)
    {
        // CRITICAL: Validate position_id before creating trade data
        if (empty($positionId) || $positionId == 0 || $positionId === '0') {
            $this->logInvalidPositionId('open', $positionId, $openOrder, [
                'order_data' => $openOrder,
                'account_id' => $this->account->id,
                'account_code' => $this->account->code
            ]);

            throw new \InvalidArgumentException("Invalid position_id for open trade: {$positionId}");
        }

        return [
            'account_id' => $this->account->id,
            'code' => $this->account->code,
            'position_id' => $positionId,
            'external_position_id' => $positionId,
            'symbol' => $openOrder['Symbol'],
            'currency' => $this->account->currency,
            'sell' => $openOrder['Type'] == 1, // 1 = sell, 0 = buy
            'volume' => $openOrder['VolumeClosed'],
            'open_price' => $openOrder['PriceOrder'],
            'stop_loss' => $openOrder['PriceSL'] ?? null,
            'take_profit' => $openOrder['PriceTP'] ?? null,
            'opened' => Carbon::parse($openOrder['TimeDone']),
            'status' => 'OPEN',
            'last_update' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    protected function prepareClosedTrade($positionId, $openOrder, $closeOrder, $rateProfit)
    {
        // CRITICAL: Validate position_id before creating trade data
        if (empty($positionId) || $positionId == 0 || $positionId === '0') {
            $this->logInvalidPositionId('closed', $positionId, $closeOrder, [
                'open_order_data' => $openOrder,
                'close_order_data' => $closeOrder,
                'rate_profit' => $rateProfit,
                'account_id' => $this->account->id,
                'account_code' => $this->account->code
            ]);

            throw new \InvalidArgumentException("Invalid position_id for closed trade: {$positionId}");
        }

        $profit = ($closeOrder['Profit'] ?? 0) / $rateProfit;
        $commission = ($openOrder['Commission'] ?? 0) + ($closeOrder['Commission'] ?? 0);
        $swap = ($openOrder['Storage'] ?? 0) + ($closeOrder['Storage'] ?? 0);

        return [
            'account_id' => $this->account->id,
            'code' => $this->account->code,
            'position_id' => $positionId,
            'external_position_id' => $positionId,
            'symbol' => $openOrder['Symbol'],
            'currency' => $this->account->currency,
            'sell' => $openOrder['Type'] == 1,
            'volume' => $openOrder['VolumeClosed'],
            'open_price' => $openOrder['PriceOrder'],
            'current_price' => $closeOrder['PriceOrder'],
            'stop_loss' => $openOrder['PriceSL'] ?? null,
            'take_profit' => $openOrder['PriceTP'] ?? null,
            'profit' => $profit,
            'commission' => $commission,
            'swap' => $swap,
            'opened' => Carbon::parse($openOrder['TimeDone']),
            'closed' => Carbon::parse($closeOrder['TimeDone']),
            'status' => 'CLOSED',
            'last_update' => now(),
            'updated_at' => now(),
        ];
    }

    protected function updateActivityTracking()
    {
        // Update tier based on recent activity
        $recentTrades = Trade::where('account_id', $this->account->id)
            ->where(function ($q) {
                $q->where('opened', '>=', now()->subDay())
                    ->orWhere('closed', '>=', now()->subDay());
            })
            ->exists();

        if ($recentTrades && $this->account->sync_tier !== 'very_active') {
            $this->account->update(['sync_tier' => 'very_active']);
            Log::info("Upgraded account {$this->account->code} to very_active tier");
        }
    }

    protected function updateSyncStatus($status, $error = null)
    {
        $this->account->update([
            'last_balance_sync_at' => now(),
            'last_sync_attempt_at' => now(),
            'sync_status' => $status === 'success' ? 'synced' : 'pending',
            'sync_error' => $error,
        ]);
    }

    protected function connectWithRetry(UniversalMT5Service $mt5Service)
    {
        // UniversalMT5Service handles connection pooling and retries
        if (!$mt5Service->connect()) {
            throw new \Exception("Failed to connect to MT5 after retries");
        }
    }

    /**
     * Log invalid position_id attempts with comprehensive details for admin investigation
     */
    protected function logInvalidPositionId(string $tradeType, $positionId, $order, array $context = []): void
    {
        $logData = array_merge([
            'trade_type' => $tradeType,
            'position_id' => $positionId,
            'account_id' => $this->account->id,
            'account_code' => $this->account->code,
            'account_demo' => $this->account->demo,
            'order_id' => $order['Order'] ?? 'unknown',
            'order_symbol' => $order['Symbol'] ?? 'unknown',
            'order_type' => $order['Type'] ?? 'unknown',
            'order_volume' => $order['VolumeClosed'] ?? 'unknown',
            'order_price' => $order['PriceOrder'] ?? 'unknown',
            'order_time_done' => $order['TimeDone'] ?? 'unknown',
            'order_comment' => $order['Comment'] ?? '',
            'timestamp' => now(),
            'job_id' => $this->job->getJobId() ?? 'unknown',
            'queue' => $this->job->getQueue() ?? 'unknown',
            'severity' => 'CRITICAL',
            'issue_type' => 'INVALID_POSITION_ID_OPTIMIZED_JOB',
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15)
        ], $context);

        // Log critical data integrity issue with full context
        Log::critical("🚨 INVALID POSITION_ID in OptimizedSyncTradesJob: {$tradeType} trade with position_id = {$positionId}", $logData);

        // Log to admin activity log for dashboard visibility
        activity('trade_data_integrity')
            ->withProperties($logData)
            ->log("🚨 CRITICAL: Invalid position_id ({$positionId}) in {$tradeType} trade for account {$this->account->code}");

        // Send immediate admin notification if configured
        try {
            Log::channel('slack')->critical("Invalid position_id detected in optimized trade sync", [
                'account' => $this->account->code,
                'position_id' => $positionId,
                'trade_type' => $tradeType,
                'order_id' => $order['Order'] ?? 'unknown'
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to send admin notification for invalid position_id: " . $e->getMessage());
        }
    }
}
