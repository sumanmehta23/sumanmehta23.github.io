<?php

namespace App\Jobs;

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
class OptimizedSyncTradesJob implements ShouldQueue
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

        // OPTIMIZATION: Get all deals at once instead of per position
        $fromDate = $this->fromTime->format('Y-m-d H:i:s');
        $toDate = now()->format('Y-m-d H:i:s');

        $totalDeals = 0;
        $error_code = $api->DealGetTotal($this->account->code, $fromDate, $toDate, $totalDeals);

        $allDeals = [];
        if ($error_code == MTRetCode::MT_RET_OK && $totalDeals > 0) {
            $api->DealGetPage($this->account->code, $fromDate, $toDate, 0, $totalDeals, $allDeals);
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
