<?php

namespace App\Policies;

use App\Models\TradeWithdrawals;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TradeWithdrawalsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TradeWithdrawals $tradeWithdrawals): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TradeWithdrawals $tradeWithdrawals): bool
    {
        return false;
    }

}
