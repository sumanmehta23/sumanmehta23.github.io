<?php

namespace App\Services;

use App\Models\Account;
use App\Services\UniversalMT5Service;
use App\MT5\MTRetCode;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BalanceSyncService
{
    protected $mt5Service;

    public function __construct(UniversalMT5Service $mt5Service)
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
            // Ensure MT5 connection is ready
            if (!$this->mt5Service->connect()) {
                throw new \Exception("Failed to establish MT5 connection for balance sync");
            }

            // Get accounts to sync
            $accounts = $this->getAccountsForBalanceSync($accountCodes, $forceSync);

            if ($accounts->isEmpty()) {
                Log::info("No accounts require balance sync");
                return $results;
            }

            Log::info("Found {$accounts->count()} accounts for balance sync");

            foreach ($accounts as $account) {
                try {
                    $result = $this->syncSingleAccountBalance($account);
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
    private function syncSingleAccountBalance(Account $account): string
    {
        $accountCode = $account->code;

        // Use the proper service method instead of direct API calls
        $accountData = $this->mt5Service->getAccountBalance((int)$accountCode);

        if (!$accountData) {
            Log::warning("MT5 account data not found for balance sync: {$accountCode}");
            return 'not_found';
        }

        // Extract balance and equity from the standardized response
        $currentBalance = (float) ($accountData['balance'] ?? 0);
        $currentEquity = (float) ($accountData['equity'] ?? 0);

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
            'balance' => $currentBalance,
            'equity' => $currentEquity,
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
     * 
     * @param string|int $accountIdentifier Can be account ID (UUID), account code, or legacy integer ID
     * @param string $reason Reason for the balance activity
     */
    public function markBalanceActivity($accountIdentifier, string $reason = 'manual'): void
    {
        // Add debug logging to help diagnose type issues
        Log::debug("BalanceSyncService::markBalanceActivity called", [
            'account_identifier' => $accountIdentifier,
            'account_identifier_type' => gettype($accountIdentifier),
            'reason' => $reason,
            'caller' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? 'unknown'
        ]);

        try {
            // Type validation and conversion
            if (!is_string($accountIdentifier) && !is_int($accountIdentifier) && !is_numeric($accountIdentifier)) {
                throw new \InvalidArgumentException(
                    "Invalid account identifier type: " . gettype($accountIdentifier) .
                        ". Expected string, int, or numeric value. Received: " . var_export($accountIdentifier, true)
                );
            }

            // Convert to string for consistent handling
            $accountIdentifier = (string) $accountIdentifier;

            // Determine if we have an account ID (UUID), account code, or legacy integer ID
            $query = Account::query();

            if (is_string($accountIdentifier)) {
                // Check if it's a UUID (account ID) or account code
                if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $accountIdentifier)) {
                    // It's a UUID - use as account ID
                    $query->where('id', $accountIdentifier);
                    $logIdentifier = "account ID {$accountIdentifier}";
                } else {
                    // It's an account code
                    $query->where('code', $accountIdentifier);
                    $logIdentifier = "account code {$accountIdentifier}";
                }
            } else {
                // Legacy integer ID support (though this shouldn't happen with UUIDs)
                $query->where('id', $accountIdentifier);
                $logIdentifier = "account ID {$accountIdentifier}";
            }

            $updated = $query->update([
                'last_balance_changed_at' => now(),
                'has_balance_activity' => true
            ]);

            if ($updated > 0) {
                Log::info("Marked balance activity for {$logIdentifier}: {$reason}");
            } else {
                Log::warning("No account found for identifier {$accountIdentifier} when marking balance activity: {$reason}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to mark balance activity for account identifier {$accountIdentifier}: " . $e->getMessage());
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
