<?php

namespace App\Policies;

use App\Models\Ib1Commission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IbCommissionPolicy
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
    public function view(User $user, Ib1Commission $ib1Commission): bool
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
    public function update(User $user, Ib1Commission $ib1Commission): bool
    {
        return false;
    }

}
