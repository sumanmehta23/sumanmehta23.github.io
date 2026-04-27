<?php

namespace App\Policies;

use App\Models\User;

/**
 * User Dashboard Policy
 * Handles authorization for user dashboard and account overview access
 */
class UserDashboardPolicy
{
    /**
     * View own dashboard
     */
    public function view(User $user, User $targetUser = null): bool
    {
        // User can always view their own dashboard
        if ($targetUser === null) {
            return true;
        }

        // Users can only view their own data
        return $user->id === $targetUser->id;
    }

    /**
     * View own accounts/trading accounts
     */
    public function viewAccounts(User $user): bool
    {
        // Check if user account is active
        return !$user->trashed() && $user->status === 'active';
    }

    /**
     * View overall financial summary
     */
    public function viewFinancialSummary(User $user): bool
    {
        return !$user->trashed();
    }
}
