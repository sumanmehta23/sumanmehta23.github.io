<?php

namespace App\Policies;

use App\Models\User;

/**
 * User Affiliate Policy
 * Handles authorization for user affiliate/IB (introducing broker) operations
 */
class UserAffiliatePolicy
{
    /**
     * View own affiliate dashboard
     */
    public function view(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * View referral link
     */
    public function viewReferralLink(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * View referrals (people who signed up using referral code)
     */
    public function viewReferrals(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * View commission history
     */
    public function viewCommissions(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * Request commission payout/withdrawal
     */
    public function requestPayout(User $user): bool
    {
        if ($user->trashed()) {
            return false;
        }

        // Check if user has commission balance
        $commissionBalance = $this->getUserCommissionBalance($user);
        return $commissionBalance >= $this->getMinimumPayoutAmount();
    }

    /**
     * Enroll in affiliate program
     */
    public function enroll(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * Update affiliate settings
     */
    public function updateSettings(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * Export commission history
     */
    public function export(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * Get user's total commission balance
     *
     * @param  User  $user
     * @return float
     */
    private function getUserCommissionBalance(User $user): float
    {
        // This is a placeholder - adjust based on your data structure
        // typically stored in a commission/wallet table
        return $user->commission_balance ?? 0;
    }

    /**
     * Get minimum withdrawal amount for commissions
     *
     * @return float
     */
    private function getMinimumPayoutAmount(): float
    {
        // Typically 50-100 USD minimum
        return (float) config('affiliate.minimum_payout', 50);
    }
}
