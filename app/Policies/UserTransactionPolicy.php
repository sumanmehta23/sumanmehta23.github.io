<?php

namespace App\Policies;

use App\Models\User;

/**
 * User Transaction Policy
 * Handles authorization for user wallet, deposit, and withdrawal operations
 */
class UserTransactionPolicy
{
    /**
     * View own transactions
     */
    public function viewAny(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * Request withdrawal
     */
    public function requestWithdrawal(User $user): bool
    {
        if ($user->trashed()) {
            return false;
        }

        // Check if user KYC is verified (if required)
//        if (isset($user->kyc_verify) && $user->kyc_verify !== 'verified') {
//            // Allow withdrawal for non-KYC requiring users or those with pending KYC
//            // Adjust based on your business rules
//        }

        return true;
    }

    /**
     * Request deposit
     */
    public function requestDeposit(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * View wallet/balance
     */
    public function viewWallet(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * View transaction history
     */
    public function viewHistory(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * Export transaction history
     */
    public function export(User $user): bool
    {
        return !$user->trashed();
    }
}
