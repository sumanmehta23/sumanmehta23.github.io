<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Trade;

/**
 * User Trade Policy
 * Handles authorization for user trading operations
 */
class UserTradePolicy
{
    /**
     * View own trades
     */
    public function viewAny(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * View specific trade
     */
    public function view(User $user, Trade $trade): bool
    {
        // User can only view their own trades
        return $user->id === $trade->account->user_id;
    }

    /**
     * Create new trade (open position)
     */
    public function create(User $user): bool
    {
        // Check if user has active trading account
        if ($user->trashed()) {
            return false;
        }

        // Check if user has at least one active account
        return $user->accounts()->where('status', 'active')->exists();
    }

    /**
     * Close trade
     */
    public function close(User $user, Trade $trade): bool
    {
        // User can only close their own trades
        if ($user->id !== $trade->account->user_id) {
            return false;
        }

        // Trade must be open
        return $trade->status === 'open';
    }

    /**
     * Export trade history
     */
    public function export(User $user): bool
    {
        return !$user->trashed();
    }
}
