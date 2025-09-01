<?php

namespace App\Jobs;

use App\Models\Trade;
use App\Models\Account;
use App\Services\MT5Service;
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

        // Set timeout based on number of accounts (60s per account + buffer)
        $this->timeout = max(300, count($accounts) * 60 + 120);
    }

    public function handle(MT5Service $mt5Service)
    {
        $accountCodes = collect($this->accounts)->pluck('code')->join(', ');
        $accountCount = count($this->accounts);

        Log::info("Starting BatchSyncTradesJob for {$accountCount} accounts: {$accountCodes}");

        $startTime = now();
        $results = [
            'processed' => 0,
            'success' => 0,
            'errors' => 0,
            'no_changes' => 0,
            'not_found' => 0
        ];

        try {
            // Single MT5 connection for all accounts
            $this->connectWithRetry($mt5Service);
            $api = $mt5Service->getApi();

            foreach ($this->accounts as $index => $accountData) {
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

                    Log::info("Account {$account->code}: {$result}");
                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['processed']++;
                    Log::error("Error syncing account {$accountData['code']}: " . $e->getMessage());
                }

                // Small delay between accounts to avoid overwhelming MT5
                if ($index < count($this->accounts) - 1) {
                    usleep(500000); // 0.5 second
                }
            }
        } catch (\Exception $e) {
            Log::error("BatchSyncTradesJob failed: " . $e->getMessage());
            throw $e;
        }

        $duration = $startTime->diffInSeconds(now());
        $avgPerAccount = round($duration / $accountCount, 2);

        Log::info("BatchSyncTradesJob completed: {$results['processed']} accounts in {$duration}s (avg: {$avgPerAccount}s/account). " .
            "Success: {$results['success']}, No changes: {$results['no_changes']}, Errors: {$results['errors']}, Not found: {$results['not_found']}");
    }

    protected function syncSingleAccount($api, Account $account, Carbon $fromTime): string
    {
        if (!$account->code) {
            return 'error';
        }

        // Quick user check
        $mt5_user = null;
        $error_code = $api->UserGet($account->code, $mt5_user);

        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::warning("MT5 user not found for account {$account->code}");
            $this->updateSyncStatus($account, 'not_found');
            return 'not_found';
        }

        // Incremental time range
        $fromDate = $fromTime->format('Y-m-d H:i:s');
        $toDate = now()->format('Y-m-d H:i:s');

        // Get total with time filter
        $total = 0;
        $error_code = $api->HistoryGetTotal($account->code, $fromDate, $toDate, $total);

        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::error("MT5 HistoryGetTotal error for {$account->code}: " . MTRetCode::GetError($error_code));
            $this->updateSyncStatus($account, 'error');
            return 'error';
        }

        // Skip if no recent orders
        if ($total == 0) {
            $this->updateSyncStatus($account, 'no_changes');
            return 'no_changes';
        }

        // Get recent orders
        $orders = [];
        $error_code = $api->HistoryGetPage($account->code, $fromDate, $toDate, 0, $total, $orders);

        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::error("MT5 HistoryGetPage error for {$account->code}: " . MTRetCode::GetError($error_code));
            $this->updateSyncStatus($account, 'error');
            return 'error';
        }

        // Process and store trades
        $savedTrades = $this->processAndStoreTrades($account, $orders);

        $this->updateSyncStatus($account, 'success', $savedTrades);
        $this->updateLastTradeTime($account, $orders);

        return 'success';
    }

    protected function processAndStoreTrades(Account $account, array $orders): int
    {
        $savedCount = 0;

        foreach ($orders as $order) {
            try {
                // Check if trade already exists
                $existingTrade = Trade::where('order_id', $order['Ticket'])
                    ->where('account_id', $account->id)
                    ->first();

                if ($existingTrade) {
                    // Update existing trade if needed
                    if ($this->shouldUpdateTrade($existingTrade, $order)) {
                        $this->updateTradeFromOrder($existingTrade, $order);
                        $savedCount++;
                    }
                } else {
                    // Create new trade
                    $this->createTradeFromOrder($account, $order);
                    $savedCount++;
                }
            } catch (\Exception $e) {
                Log::error("Error processing trade {$order['Ticket']} for account {$account->code}: " . $e->getMessage());
            }
        }

        return $savedCount;
    }

    protected function shouldUpdateTrade(Trade $trade, array $order): bool
    {
        // Update if close price changed or status changed
        return $trade->close_price != $order['PriceClose'] ||
            $trade->status != $this->getTradeStatus($order);
    }

    protected function createTradeFromOrder(Account $account, array $order): void
    {
        Trade::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'account_id' => $account->id,
            'code' => $account->code,
            'order_id' => (string) $order['Ticket'],
            'symbol' => $order['Symbol'],
            'position_id' => $order['Ticket'],
            'type' => $order['Type'] == 0 ? 'buy' : 'sell',
            'volume' => $order['Volume'],
            'volume_ext' => $order['Volume'] * 100,
            'open_price' => $order['PriceOpen'],
            'close_price' => $order['PriceClose'],
            'profit' => $order['Profit'],
            'sl' => $order['SL'] ?? 0,
            'tp' => $order['TP'] ?? 0,
            'comment' => $order['Comment'] ?? '',
            'status' => $this->getTradeStatus($order),
            'state' => (string) $order['State'],
            'open_time' => Carbon::createFromTimestamp($order['TimeSetup']),
            'close_time' => $order['TimeSetup'] != $order['TimeDone'] ?
                Carbon::createFromTimestamp($order['TimeDone']) : null,
        ]);
    }

    protected function updateTradeFromOrder(Trade $trade, array $order): void
    {
        $trade->update([
            'close_price' => $order['PriceClose'],
            'profit' => $order['Profit'],
            'status' => $this->getTradeStatus($order),
            'close_time' => $order['TimeSetup'] != $order['TimeDone'] ?
                Carbon::createFromTimestamp($order['TimeDone']) : null,
        ]);
    }

    protected function getTradeStatus(array $order): string
    {
        // Map MT5 order state to our status
        return match ($order['State']) {
            1 => 'open',      // ORDER_STATE_STARTED
            2 => 'closed',    // ORDER_STATE_FILLED
            3 => 'cancelled', // ORDER_STATE_CANCELED
            default => 'unknown'
        };
    }

    protected function updateSyncStatus(Account $account, string $status, int $tradesCount = 0): void
    {
        $account->update([
            'last_balance_sync_at' => now(),
        ]);
    }

    protected function updateLastTradeTime(Account $account, array $orders): void
    {
        if (empty($orders)) {
            return;
        }

        // Find the most recent trade time
        $latestTime = 0;
        foreach ($orders as $order) {
            $latestTime = max($latestTime, $order['TimeDone'], $order['TimeSetup']);
        }

        if ($latestTime > 0) {
            $account->update([
                'last_trade_at' => Carbon::createFromTimestamp($latestTime)
            ]);
        }
    }

    protected function connectWithRetry(MT5Service $mt5Service, int $maxRetries = 3): void
    {
        $attempt = 0;
        while ($attempt < $maxRetries) {
            try {
                $mt5Service->connect();
                return;
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt >= $maxRetries) {
                    throw new \Exception("Failed to connect to MT5 after {$maxRetries} attempts: " . $e->getMessage());
                }
                sleep(2 * $attempt); // Exponential backoff
            }
        }
    }
}
