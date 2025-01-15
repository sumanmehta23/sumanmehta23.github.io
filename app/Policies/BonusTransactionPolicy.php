<?php

namespace App\Policies;

use App\Models\BonusTransaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BonusTransactionPolicy
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
    public function view(User $user, BonusTransaction $bonusTransaction): bool
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
    public function update(User $user, BonusTransaction $bonusTransaction): bool
    {
        return false;
    }

   
}
