<?php

namespace App\Jobs;

use App\Models\Trade;
use App\Models\Account;
use App\Services\OptimizedMT5Service;
use App\MT5\MTRetCode;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Carbon\Carbon;

/**
 * Batch Sync Trades Job - Multiple Accounts per Connection
 *
 * This job processes multiple accounts in a single job to:
 * 1. Reuse MT5 connection across accounts (major performance gain)
 * 2. Reduce job overhead (fewer queue items)
 * 3. Better resource utilization
 * 4. Maintain reliability with proper error handling per account
 */
class BatchSyncTradesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $accounts;
    protected $fromTimes;
    public $timeout = 300; // 5 minutes for batch
    public $tries = 2;

    public function __construct(array $accounts, array $fromTimes = [])
    {
        // Convert Account models to serializable array format
        $this->accounts = collect($accounts)->map(function ($account) {
            return [
                'id' => $account->id,
                'code' => $account->code,
                'demo' => $account->demo,
                'last_balance_sync_at' => $account->last_balance_sync_at,
                'last_trade_at' => $account->last_trade_at,
            ];
        })->toArray();

        $this->fromTimes = $fromTimes;

        // Set timeout based on number of accounts with optimized timing
        // Base: 5 minutes, then 60 seconds per account (reduced from 90) + 2 minute buffer (reduced from 5)
        $this->timeout = max(300, count($accounts) * 60 + 120);
    }

    public function handle(OptimizedMT5Service $mt5Service)
    {
        $jobStartTime = microtime(true);
        $accountCodes = collect($this->accounts)->pluck('code')->join(', ');
        $accountCount = count($this->accounts);
        $startMemory = memory_get_usage(true);

        Log::info("Starting BatchSyncTradesJob for {$accountCount} accounts: {$accountCodes} (Memory: " . round($startMemory / 1024 / 1024, 2) . "MB)");

        $startTime = now();
        $results = [
            'processed' => 0,
            'success' => 0,
            'errors' => 0,
            'no_changes' => 0,
            'not_found' => 0
        ];

        $connectionTime = 0;
        $accountTimings = [];

        try {
            // Track MT5 connection time
            $connectionStart = microtime(true);
            if (!$mt5Service->connect()) {
                throw new \Exception("Failed to establish MT5 connection");
            }
            $connectionTime = round((microtime(true) - $connectionStart) * 1000, 2);
            $api = $mt5Service->getApi();

            foreach ($this->accounts as $index => $accountData) {
                $accountIterationStart = microtime(true);
                try {
                    // Convert array back to Account model for processing
                    $account = Account::find($accountData['id']);
                    if (!$account) {
                        Log::warning("Account {$accountData['code']} not found in database");
                        $results['not_found']++;
                        $results['processed']++;
                        continue;
                    }

                    $fromTime = $this->fromTimes[$index] ?? now()->subDays(7);
                    $result = $this->syncSingleAccount($api, $account, $fromTime);
                    $results[$result]++;
                    $results['processed']++;

                    $accountTime = round((microtime(true) - $accountIterationStart) * 1000, 2);
                    $accountTimings[] = ['account' => $account->code, 'time' => $accountTime, 'result' => $result];

                    Log::info("Account {$account->code}: {$result} ({$accountTime}ms)");
                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['processed']++;
                    $accountTime = round((microtime(true) - $accountIterationStart) * 1000, 2);
                    $accountTimings[] = ['account' => $accountData['code'], 'time' => $accountTime, 'result' => 'error'];
                    Log::error("Error syncing account {$accountData['code']}: " . $e->getMessage());

                    // Report error to connection pool for adaptive management
                    $mt5Service->reportError();
                }

                // Small delay between accounts to avoid overwhelming MT5
                if ($index < count($this->accounts) - 1) {
                    usleep(100000); // 0.1 second - optimized for better throughput
                }
            }
        } catch (\Exception $e) {
            Log::error("BatchSyncTradesJob failed: " . $e->getMessage());
            throw $e;
        }

        $duration = $startTime->diffInSeconds(now());
        $totalJobTime = round((microtime(true) - $jobStartTime) * 1000, 2);
        $avgPerAccount = round($duration / $accountCount, 2);
        $avgPerAccountMs = round($totalJobTime / $accountCount, 2);
        $endMemory = memory_get_usage(true);
        $memoryUsed = round(($endMemory - $startMemory) / 1024 / 1024, 2);
        $peakMemory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        // Calculate statistics
        $accountTimes = array_column($accountTimings, 'time');
        $minTime = !empty($accountTimes) ? min($accountTimes) : 0;
        $maxTime = !empty($accountTimes) ? max($accountTimes) : 0;
        $medianTime = !empty($accountTimes) ? $this->calculateMedian($accountTimes) : 0;

        // Detailed performance breakdown
        $performanceReport = [
            'job_total_ms' => $totalJobTime,
            'connection_ms' => $connectionTime,
            'avg_per_account_ms' => $avgPerAccountMs,
            'min_account_ms' => $minTime,
            'max_account_ms' => $maxTime,
            'median_account_ms' => $medianTime,
            'memory_used_mb' => $memoryUsed,
            'peak_memory_mb' => $peakMemory,
            'account_breakdown' => $accountTimings
        ];

        Log::info("BatchSyncTradesJob PERFORMANCE SUMMARY: {$results['processed']} accounts in {$totalJobTime}ms " .
            "(avg: {$avgPerAccountMs}ms/account, median: {$medianTime}ms). " .
            "Connection: {$connectionTime}ms. Range: {$minTime}ms-{$maxTime}ms. " .
            "Success: {$results['success']}, No changes: {$results['no_changes']}, Errors: {$results['errors']}, Not found: {$results['not_found']} " .
            "Memory: {$memoryUsed}MB used, {$peakMemory}MB peak.");

        Log::info("PERF_BREAKDOWN: " . json_encode($performanceReport));
    }

    private function calculateMedian(array $values): float
    {
        sort($values);
        $count = count($values);
        $middle = floor(($count - 1) / 2);

        if ($count % 2) {
            return $values[$middle];
        } else {
            return ($values[$middle] + $values[$middle + 1]) / 2;
        }
    }

    protected function syncSingleAccount($api, Account $account, Carbon $fromTime): string
    {
        $accountStartTime = microtime(true);
        $timings = [];
        $apiCalls = [];

        if (!$account->code) {
            return 'error';
        }

        // Phase 1: MT5 User Check
        $phaseStart = microtime(true);
        $mt5_user = null;
        $error_code = $api->UserGet($account->code, $mt5_user);
        $timings['mt5_user_check'] = round((microtime(true) - $phaseStart) * 1000, 2);
        $apiCalls[] = ['UserGet', $timings['mt5_user_check']];

        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::warning("MT5 user not found for account {$account->code}");
            $this->updateSyncStatus($account, 'not_found');
            return 'not_found';
        }

        try {
            // Phase 2: Database Query for Existing Trades
            $phaseStart = microtime(true);
            $existingTrades = Trade::where('account_id', $account->id)
                ->select(['id', 'position_id', 'status', 'close_time', 'updated_at'])
                ->get()
                ->keyBy('position_id');
            $timings['db_existing_trades'] = round((microtime(true) - $phaseStart) * 1000, 2);

            $login = $account->code;
            $fromDate = $fromTime->format('F d, Y');
            $toDate = now()->addHours(4)->format('F d, Y');
            $total = 0;
            $orders = [];

            // Phase 3: MT5 HistoryGetTotal
            $phaseStart = microtime(true);
            $error_code = $this->executeWithRetries(function () use ($api, $login, $fromDate, $toDate, &$total) {
                return $api->HistoryGetTotal($login, $fromDate, $toDate, $total);
            });
            $timings['mt5_history_total'] = round((microtime(true) - $phaseStart) * 1000, 2);
            $apiCalls[] = ['HistoryGetTotal', $timings['mt5_history_total']];

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 HistoryGetTotal final error for login {$login}: " . MTRetCode::GetError($error_code));
                $this->updateSyncStatus($account, 'error');
                return 'error';
            }

            // Skip if no recent orders
            if ($total == 0) {
                $totalTime = round((microtime(true) - $accountStartTime) * 1000, 2);
                Log::info("PERF[{$account->code}]: {$totalTime}ms total (no orders) - " . json_encode($timings));
                $this->updateSyncStatus($account, 'no_changes');
                return 'no_changes';
            }

            // Phase 4: MT5 HistoryGetPage
            $phaseStart = microtime(true);
            $error_code = $this->executeWithRetries(function () use ($api, $login, $fromDate, $toDate, $total, &$orders) {
                return $api->HistoryGetPage($login, $fromDate, $toDate, 0, $total, $orders);
            });
            $timings['mt5_history_page'] = round((microtime(true) - $phaseStart) * 1000, 2);
            $apiCalls[] = ['HistoryGetPage', $timings['mt5_history_page']];

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 HistoryGetPage error for login {$login}: " . MTRetCode::GetError($error_code));
                $this->updateSyncStatus($account, 'error');
                return 'error';
            }

            // Phase 5: Data Processing - Orders Grouping
            $phaseStart = microtime(true);
            $ordersByPosition = collect($orders)->groupBy('ExpertPositionID');
            $tradesToUpsert = [];
            $savedCount = 0;
            $timings['orders_processing'] = round((microtime(true) - $phaseStart) * 1000, 2);

            // Phase 6: MT5 DealGetTotal
            $phaseStart = microtime(true);
            $totalDeals = 0;
            $allDeals = [];
            $error_code = $api->DealGetTotal($account->code, $fromDate, $toDate, $totalDeals);
            $timings['mt5_deal_total'] = round((microtime(true) - $phaseStart) * 1000, 2);
            $apiCalls[] = ['DealGetTotal', $timings['mt5_deal_total']];

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("Failed to get total deals for account {$account->code}: " . MTRetCode::GetError($error_code));
                $this->updateSyncStatus($account, 'error');
                return 'error';
            }

            // Phase 7: MT5 DealGetPage (if deals exist)
            if ($totalDeals > 0) {
                $phaseStart = microtime(true);
                $error_code = $api->DealGetPage($account->code, $fromDate, $toDate, 0, $totalDeals, $allDeals);
                $timings['mt5_deal_page'] = round((microtime(true) - $phaseStart) * 1000, 2);
                $apiCalls[] = ['DealGetPage', $timings['mt5_deal_page']];

                if ($error_code != MTRetCode::MT_RET_OK) {
                    Log::error("Failed to get deals for account {$account->code}: " . MTRetCode::GetError($error_code));
                    $this->updateSyncStatus($account, 'error');
                    return 'error';
                }
            } else {
                $timings['mt5_deal_page'] = 0;
            }

            // Phase 8: Trade Data Preparation
            $phaseStart = microtime(true);
            // Phase 8: Trade Data Preparation
            $phaseStart = microtime(true);
            $batchProcessingTime = 0;
            foreach ($ordersByPosition as $positionId => $positionOrders) {
                $positionOrders = $positionOrders->sortBy('TimeDone');
                $existingTrade = $existingTrades->get($positionId);

                // Filter deals for this specific position from the already-fetched deals
                $filteredDeals = array_values(array_filter($allDeals, fn($deal) => $deal->Order == $positionId));
                $rateProfit = $filteredDeals[0]->RateProfit ?? 1;  // Default to 1 if no deal found

                if ($positionOrders->count() < 2) {
                    // OPEN TRADE: Insert if does not exist
                    if (!$existingTrade) {
                        $tradeData = $this->prepareOpenTrade($account, $positionId, $positionOrders->first());
                        $tradesToUpsert[] = $tradeData;
                        $savedCount++;
                    }
                } else {
                    // CLOSED TRADE: Update if exists, otherwise insert new
                    if ($existingTrade) {
                        $closedTradeData = $this->prepareClosedTrade($account, $positionId, $positionOrders->first(), $positionOrders->last(), $rateProfit);
                        // Set ID to perform update on the correct row
                        $closedTradeData['id'] = $existingTrade->id;
                        $tradesToUpsert[] = $closedTradeData;
                        $savedCount++;
                    } else {
                        // No open trade exists but we have a closed trade - insert it
                        $closedTradeData = $this->prepareClosedTrade($account, $positionId, $positionOrders->first(), $positionOrders->last(), $rateProfit);
                        $tradesToUpsert[] = $closedTradeData;
                        $savedCount++;
                    }
                }

                // Dynamic batch size based on account complexity
                $batchSize = count($this->accounts) > 5 ? 100 : 50; // Larger batches for bigger jobs

                if (count($tradesToUpsert) >= $batchSize) { // Process in dynamic batches
                    $batchStart = microtime(true);
                    $this->processBatch($tradesToUpsert);
                    $batchProcessingTime += round((microtime(true) - $batchStart) * 1000, 2);
                    $tradesToUpsert = [];
                }
            }
            $timings['trade_preparation'] = round((microtime(true) - $phaseStart) * 1000, 2);

            // Phase 9: Final Batch Processing
            $phaseStart = microtime(true);
            if (!empty($tradesToUpsert)) {
                $this->processBatch($tradesToUpsert);
            }
            $timings['final_batch'] = round((microtime(true) - $phaseStart) * 1000, 2);
            $timings['total_batch_processing'] = $batchProcessingTime + $timings['final_batch'];

            // Phase 10: Update Account Status
            $phaseStart = microtime(true);
            $this->updateSyncStatus($account, 'success', $savedCount);
            $timings['status_update'] = round((microtime(true) - $phaseStart) * 1000, 2);

            // Final Performance Summary
            $totalTime = round((microtime(true) - $accountStartTime) * 1000, 2);
            $totalApiTime = array_sum(array_column($apiCalls, 1));
            $totalDbTime = $timings['db_existing_trades'] + $timings['total_batch_processing'] + $timings['status_update'];

            Log::info("PERF[{$account->code}]: {$totalTime}ms total | " .
                "API: {$totalApiTime}ms (" . count($apiCalls) . " calls) | " .
                "DB: {$totalDbTime}ms | " .
                "Processing: {$timings['trade_preparation']}ms | " .
                "Orders: {$total}, Deals: {$totalDeals}, Trades: {$savedCount} | " .
                "Breakdown: " . json_encode($timings) . " | " .
                "API Calls: " . json_encode($apiCalls));

            return 'success';
        } catch (\Exception $e) {
            $totalTime = round((microtime(true) - $accountStartTime) * 1000, 2);
            Log::error("PERF[{$account->code}]: {$totalTime}ms ERROR - " . $e->getMessage());
            Log::error("Error syncing account {$account->code}: " . $e->getMessage());
            $this->updateSyncStatus($account, 'error');
            return 'error';
        }
    }

    protected function executeWithRetries($callback)
    {
        // Note: This method is now simplified since OptimizedMT5Service 
        // handles retries internally. Keep for backward compatibility.
        return $callback();
    }

    protected function prepareOpenTrade($account, $positionId, $order)
    {
        // Log::info("account code  ".$account->code);
        // Log::info("order  ".json_encode($order));
        // Log::info("order id  ".$order->Order);
        // Log::info("trade type  ".$order->Type);
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
            'type' => $order->Type ? 'sell' : 'buy',
            'updated_at' => now(),
            'volume' => $order->VolumeInitial / 10000,
            'volume_ext' => $order->VolumeInitialExt,
        ];
    }

    protected function prepareClosedTrade($account, $positionId, $openOrder, $closeOrder, $rateProfit)
    {
        $multiplier = $openOrder->Type ? -1 : 1;

        // Log::info("account code  ".$account->code);
        // Log::info("order  ".json_encode($closeOrder));
        // Log::info("order id  ".$closeOrder->Order);
        // Log::info("trade type  ".$closeOrder->Type);

        return [
            'account_id' => $account->id,
            'position_id' => $positionId,
            'order_id' => $openOrder->Order,
            'symbol' => $openOrder->Symbol,
            'type' => $openOrder->Type ? 'sell' : 'buy',
            'volume' => $openOrder->VolumeInitial / 10000,
            'volume_ext' => $openOrder->VolumeInitialExt,
            'open_price' => $openOrder->PriceCurrent,
            'close_price' => $closeOrder->PriceCurrent,
            'sl' => $openOrder->PriceSL,
            'tp' => $openOrder->PriceTP,
            'open_time' => date('Y-m-d H:i:s', $openOrder->TimeDone),
            'close_time' => date('Y-m-d H:i:s', $closeOrder->TimeDone),
            'state' => $closeOrder->State,
            'comment' => $openOrder->Comment,
            'profit' => round((($closeOrder->PriceCurrent - $openOrder->PriceCurrent) * ($openOrder->VolumeInitialExt / 100000000) * $openOrder->ContractSize) * $rateProfit * $multiplier, 2),
            'status' => 'closed',
            'code' => $account->code,
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }

    protected function processBatch(array $trades)
    {
        $batchStart = microtime(true);
        try {
            // Optimized upsert with composite unique key to prevent duplicates
            // Using account_id + position_id as the unique identifier prevents duplicate trades
            Trade::upsert(
                $trades,
                ['account_id', 'position_id'], // composite unique identifier
                ['close_price', 'close_time', 'state', 'status', 'profit', 'updated_at'] // only essential columns
            );
            $batchTime = round((microtime(true) - $batchStart) * 1000, 2);
            Log::debug("DB Batch: " . count($trades) . " trades in {$batchTime}ms");
        } catch (\Exception $e) {
            $batchTime = round((microtime(true) - $batchStart) * 1000, 2);
            Log::error("Error processing trade batch (" . count($trades) . " trades, {$batchTime}ms): " . $e->getMessage());
            throw $e;
        }
    }

    protected function updateSyncStatus(Account $account, string $status, int $tradesCount = 0): void
    {
        $syncStatus = match ($status) {
            'success', 'no_changes' => 'synced',
            'not_found' => 'error',
            'error' => 'error',
            default => 'pending'
        };

        $syncError = null;
        if ($status === 'error') {
            $syncError = 'Sync failed';
        } elseif ($status === 'not_found') {
            $syncError = 'MT5 account not found';
        }

        $account->update([
            'last_balance_sync_at' => now(),
            'last_sync_attempt_at' => now(),
            'sync_status' => $syncStatus,
            'sync_error' => $syncError
        ]);

        Log::info("Updated sync status for account {$account->code}: {$status} -> {$syncStatus} (trades: {$tradesCount})");
    }

    protected function updateLastTradeTime(Account $account, $orders): void
    {
        if (empty($orders)) {
            return;
        }

        // Find the most recent trade time
        $latestTime = 0;
        foreach ($orders as $order) {
            $latestTime = max($latestTime, $order->TimeDone, $order->TimeSetup);
        }

        if ($latestTime > 0) {
            $account->update([
                'last_trade_at' => Carbon::createFromTimestamp($latestTime)
            ]);
        }
    }

    protected function connectWithRetry(OptimizedMT5Service $mt5Service, int $maxRetries = 3): void
    {
        // This method is now deprecated - OptimizedMT5Service handles retries internally
        if (!$mt5Service->connect()) {
            throw new \Exception("Failed to connect to MT5 after {$maxRetries} attempts");
        }
    }
}
