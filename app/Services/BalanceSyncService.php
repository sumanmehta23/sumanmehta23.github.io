<?php

namespace App\Services;

use Carbon\Carbon;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\BonusTransaction;
use Illuminate\Support\Facades\Log;
use App\Services\UniversalMT5Service;

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
    public function syncAccountBalances(array $accountCodes = null, bool $forceSync = false, int $intelligentInterval = 10): array
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

        // Log how many accounts are excluded due to not being found in MT5
        $excludedCount = Account::whereNotNull('code')
            ->where('demo', false)
            ->whereIn('sync_status', ['not_found_in_mt5'])
            ->count();

        if ($excludedCount > 0) {
            Log::info("Excluding {$excludedCount} accounts marked as not_found_in_mt5 from balance sync");
        }

        try {
            // Ensure MT5 connection is ready
            if (!$this->mt5Service->connect()) {
                throw new \Exception("Failed to establish MT5 connection for balance sync");
            }

            // Get accounts to sync
            $accounts = $this->getAccountsForBalanceSync($accountCodes, $forceSync, $intelligentInterval);

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

                    // Log::debug("Balance sync for account {$account->code}: {$result}");
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
    private function getAccountsForBalanceSync(array $accountCodes = null, bool $forceSync = false, int $intelligentInterval = 10)
    {
        $query = Account::whereNotNull('code')
            ->where('demo', false) // Non-competition accounts only
            ->whereNotIn('sync_status', ['not_found_in_mt5']) // Exclude accounts not found in MT5
        ;

        if ($accountCodes) {
            $query->whereIn('code', $accountCodes);
        }

        if (!$forceSync) {
            // Use intelligent sync interval instead of static 10 minutes
            Log::info("Using intelligent balance sync interval: {$intelligentInterval} minutes");

            $query->where(function ($q) use ($intelligentInterval) {
                $q->whereNull('last_balance_sync_at')
                    ->orWhere('last_balance_sync_at', '<', now()->subMinutes($intelligentInterval));
            });
        }

        return $query->orderBy('last_balance_sync_at', 'asc')
            ->limit(2000) // Limit to prevent overwhelming
            ->get();
    }

    /**
     * Sync balance for a single account
     */
    private function syncSingleAccountBalance(Account $account): string
    {
        $accountCode = $account->code;
        Log::info("account sync".$account->code);
        // Use the proper service method instead of direct API calls
        $accountData = $this->mt5Service->getAccountBalance((int)$accountCode);

        if (!$accountData) {
            Log::warning("MT5 account data not found for balance sync: {$accountCode} - marking as not_found_in_mt5");

            // Mark account as not found in MT5 to avoid future processing
            $account->update([
                'sync_status' => 'not_found_in_mt5',
                'sync_error' => 'Account not found in MT5 server',
                'sync_flagged_at' => now(),
                'sync_flag_reason' => 'Account not found during balance sync'
            ]);

            return 'not_found';
        }
        Log::info("message".json_encode($accountData));
        // Extract balance and equity from the standardized response
        $currentBalance = (float) ($accountData['balance'] ?? 0);
        $currentEquity = (float) ($accountData['equity'] ?? 0);
        $currentCredit = (float) ($accountData['credit'] ?? 0);

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

        $bonusPayoffSyncAt = now();

        $accountPromoBonus = $account->BonusTransaction()
                    ->where('bonus_type', 'Bonus In')
                    // ->whereIn('admin_remark', ['Promo Bonus', '10x Trader Leverage'])
                    ->when($account->bonus_payoff_sync_at, function ($query, $bonusPayoffSyncAt) {
                        $query->where('bonus_date', '<=', $bonusPayoffSyncAt);
                    })
                    ->whereNotNull('transaction_id');

        $bonus = $account->BonusTransaction()
            ->where(function ($query) {
                $query->where('bonus_type', 'Bonus In')
                    ->orWhere('bonus_type', 'Bonus Out');
            })
            ->selectRaw("
                SUM(CASE
                    WHEN admin_remark NOT LIKE '%Credit%'
                    AND admin_remark NOT LIKE '%10x Trader Leverage%'
                    AND admin_remark NOT LIKE '%Promo Bonus%'
                    AND admin_remark NOT LIKE '%Promo Deduction%'
                    AND admin_remark NOT LIKE '%Promo Addition%'
                    AND admin_remark NOT LIKE '%Bonus Pay Off%'
                    THEN bonus_amount
                    ELSE 0
                END) AS total_bonus,

                SUM(CASE
                    WHEN admin_remark LIKE '%Promo Bonus%'
                    THEN bonus_amount
                    ELSE 0
                END) AS total_promo_bonus_amount,

                SUM(CASE
                    WHEN admin_remark LIKE '%Promo Bonus%'
                    THEN bonus_used
                    ELSE 0
                END) AS total_promo_bonus_used,

                SUM(CASE
                    WHEN admin_remark LIKE '%Promo Deduction%'
                    THEN bonus_amount
                    ELSE 0
                END) AS total_promo_deduction
            ")
            ->first();

        // Log::info('Promo Bonus Query: '.$accountPromoBonus->toSql(), $accountPromoBonus->getBindings());
        Log::info("account sync for payoff".json_encode($bonus));
        Log::info("account promo bonus".$accountPromoBonus->sum('bonus_amount'));
        // Log::info("account currentCredit".$currentCredit);
        // Log::info("account code".$account->code);
        $avalableBonus = $bonus->total_promo_bonus_amount - ($bonus->total_promo_bonus_used );


        //make credit equal


        if ($currentCredit > 0 &&  ($avalableBonus > $currentCredit) ) {
            $deduction = $avalableBonus - $currentCredit;
            if ($deduction > 0) {
                Log::info("BatchBalanceSyncJob: Creating bonus payoff transaction", [
                    'account_id' => $account->id,
                    'deduction_amount' => $deduction,
                    'email' => $account->email
                ]);

                BonusTransaction::create([
                    'email' => $account->email,
                    'user_id' => $account->user_id,
                    'account_id' => $account->id,
                    'code' => $account->code,
                    'bonus_amount' => $deduction * -1,
                    'bonus_type' => 'Bonus Out',
                    'status' => 1,
                    'admin_remark' => 'Bonus Pay Off',
                    'bonus_currency' => 'USD',
                ]);

                // Log::debug("BatchBalanceSyncJob: Updating bonus used amounts");
                $accountPromoBonus->update([
                    'bonus_used' => \DB::raw('bonus_amount')
                ]);

                $account->bonus_payoff_sync_at = now();
                $account->save();
            } else {
            }
        }

        if ($currentCredit == 0 &&  ($accountPromoBonus->sum('bonus_amount') > $currentCredit) ) {
                Log::info("BatchBalanceSyncJob: Checking bonus payoff for account", [
                    'account_id' => $account->id,
                    'code' => $account->code,
                    'current_balance' => $currentBalance,
                    'current_credit' => $currentCredit
                ]);



                $accountBonusAmunt = $accountPromoBonus->sum('bonus_amount');
                $accountUsedAmunt = $accountPromoBonus->sum('bonus_used');

                Log::debug("BatchBalanceSyncJob: Bonus amounts calculated", [
                    'account_id' => $account->id,
                    'total_bonus' => $accountBonusAmunt,
                    'used_bonus' => $accountUsedAmunt,
                    'last_payoff_sync' => $account->bonus_payoff_sync_at
                ]);

                $deduction = ((float)($accountBonusAmunt - $accountUsedAmunt));
                if ($deduction > 0) {
                    Log::info("BatchBalanceSyncJob: Creating bonus payoff transaction", [
                        'account_id' => $account->id,
                        'deduction_amount' => $deduction,
                        'email' => $account->email
                    ]);

                    BonusTransaction::create([
                        'email' => $account->email,
                        'user_id' => $account->user_id,
                        'account_id' => $account->id,
                        'code' => $account->code,
                        'bonus_amount' => $deduction * -1,
                        'bonus_type' => 'Bonus Out',
                        'status' => 1,
                        'admin_remark' => 'Bonus Pay Off',
                        'bonus_currency' => 'USD',
                    ]);

                    // Log::debug("BatchBalanceSyncJob: Updating bonus used amounts");
                    $accountPromoBonus->update([
                        'bonus_used' => \DB::raw('bonus_amount')
                    ]);

                    $account->bonus_payoff_sync_at = now();
                    $account->save();

                    // Log::info("BatchBalanceSyncJob: Bonus payoff completed", [
                    //     'account_id' => $account->id,
                    //     'sync_time' => $account->bonus_payoff_sync_at
                    // ]);
                } else {
                    // Log::debug("BatchBalanceSyncJob: No bonus deduction needed", [
                    //     'account_id' => $account->id,
                    //     'deduction_calculated' => $deduction
                    // ]);
                }
            }

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
