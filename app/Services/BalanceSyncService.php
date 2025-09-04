<?php

namespace App\Services;

use App\Models\Account;
use App\Services\OptimizedMT5Service;
use App\MT5\MTRetCode;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BalanceSyncService
{
    protected $mt5Service;

    public function __construct(OptimizedMT5Service $mt5Service)
    {
        $this->mt5Service = $mt5Service;
    }

    /**
     * Sync balance and equity for non-competition accounts
     */
    public function syncAccountBalances(array $accountCodes = null, bool $forceSync = false): array
    {
        $startTime = microtime(true);
        $results = [
            'processed' => 0,
            'updated' => 0,
            'no_change' => 0,
            'errors' => 0,
            'not_found' => 0
        ];

        Log::info("Starting balance sync" . ($accountCodes ? " for specific accounts: " . implode(', ', $accountCodes) : " for all eligible accounts"));

        try {
            // Get MT5 connection
            if (!$this->mt5Service->connect()) {
                throw new \Exception("Failed to establish MT5 connection for balance sync");
            }
            $api = $this->mt5Service->getApi();

            // Get accounts to sync
            $accounts = $this->getAccountsForBalanceSync($accountCodes, $forceSync);

            if ($accounts->isEmpty()) {
                Log::info("No accounts require balance sync");
                return $results;
            }

            Log::info("Found {$accounts->count()} accounts for balance sync");

            foreach ($accounts as $account) {
                try {
                    $result = $this->syncSingleAccountBalance($api, $account);
                    $results[$result]++;
                    $results['processed']++;

                    Log::debug("Balance sync for account {$account->code}: {$result}");
                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['processed']++;
                    Log::error("Error syncing balance for account {$account->code}: " . $e->getMessage());
                    $this->mt5Service->reportError();
                }

                // Small delay to avoid overwhelming MT5
                usleep(50000); // 50ms
            }
        } catch (\Exception $e) {
            Log::error("Balance sync failed: " . $e->getMessage());
            throw $e;
        }

        $duration = round((microtime(true) - $startTime) * 1000, 2);
        Log::info("Balance sync completed: {$results['processed']} accounts in {$duration}ms. " .
            "Updated: {$results['updated']}, No change: {$results['no_change']}, Errors: {$results['errors']}, Not found: {$results['not_found']}");

        return $results;
    }

    /**
     * Get accounts that need balance sync
     */
    private function getAccountsForBalanceSync(array $accountCodes = null, bool $forceSync = false)
    {
        $query = Account::whereNotNull('code')
            ->where('demo', false) // Non-competition accounts only
            ->whereNull('competition_start_date') // Exclude competition accounts
            ->whereNull('competition_end_date');

        if ($accountCodes) {
            $query->whereIn('code', $accountCodes);
        }

        if (!$forceSync) {
            // Only sync accounts that haven't been synced in the last 15 minutes
            // or have never been synced
            $query->where(function ($q) {
                $q->whereNull('last_balance_sync_at')
                    ->orWhere('last_balance_sync_at', '<', now()->subMinutes(15));
            });
        }

        return $query->orderBy('last_balance_sync_at', 'asc')
            ->limit(1000) // Limit to prevent overwhelming
            ->get();
    }

    /**
     * Sync balance for a single account
     */
    private function syncSingleAccountBalance($api, Account $account): string
    {
        $accountCode = $account->code;

        // Get user info from MT5
        $mt5_user = null;
        $error_code = $api->UserGet($accountCode, $mt5_user);

        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::warning("MT5 user not found for balance sync: {$accountCode}");
            return 'not_found';
        }

        // Extract balance and equity with proper null checking
        $currentBalance = 0.0;
        $currentEquity = 0.0;

        if ($mt5_user && is_object($mt5_user)) {
            $currentBalance = (float) ($mt5_user->Balance ?? 0);
            $currentEquity = (float) ($mt5_user->Equity ?? 0);
        }

        $previousBalance = $account->last_known_balance;
        $previousEquity = $account->last_known_equity;

        // Check if balance or equity changed
        $balanceChanged = $previousBalance === null ||
            abs($currentBalance - $previousBalance) >= 0.01;

        $equityChanged = $previousEquity === null ||
            abs($currentEquity - $previousEquity) >= 0.01;

        $hasChanges = $balanceChanged || $equityChanged;

        // Update account data
        $updateData = [
            'last_balance_sync_at' => now(),
            'last_known_balance' => $currentBalance,
            'last_known_equity' => $currentEquity
        ];

        if ($hasChanges) {
            $updateData['last_balance_changed_at'] = now();
            $updateData['has_balance_activity'] = true;
        }

        $account->update($updateData);

        // Log significant changes
        if ($hasChanges) {
            $balanceDiff = $previousBalance ? $currentBalance - $previousBalance : 0;
            $equityDiff = $previousEquity ? $currentEquity - $previousEquity : 0;

            Log::info("Balance change detected for account {$accountCode}: " .
                "Balance: {$previousBalance} → {$currentBalance} (Δ{$balanceDiff}), " .
                "Equity: {$previousEquity} → {$currentEquity} (Δ{$equityDiff})");

            return 'updated';
        }

        return 'no_change';
    }

    /**
     * Mark account as having balance activity (called from deposit/withdrawal handlers)
     */
    public function markBalanceActivity(int $accountId, string $reason = 'manual'): void
    {
        try {
            Account::where('id', $accountId)->update([
                'last_balance_changed_at' => now(),
                'has_balance_activity' => true
            ]);

            Log::info("Marked balance activity for account ID {$accountId}: {$reason}");
        } catch (\Exception $e) {
            Log::error("Failed to mark balance activity for account ID {$accountId}: " . $e->getMessage());
        }
    }

    /**
     * Get accounts that need trade sync based on balance changes
     */
    public function getAccountsNeedingTradeSync(): \Illuminate\Database\Eloquent\Collection
    {
        return Account::whereNotNull('code')
            ->where('has_balance_activity', true)
            ->where(function ($query) {
                $query->whereNull('last_balance_sync_at')
                    ->orWhereColumn('last_balance_changed_at', '>', 'last_balance_sync_at')
                    ->orWhere('last_balance_sync_at', '<', now()->subHours(6)); // Force sync every 6 hours
            })
            ->orderBy('last_balance_changed_at', 'desc')
            ->get();
    }
}
