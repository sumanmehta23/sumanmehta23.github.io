<?php

namespace App\Policies;

use App\Models\IbPlan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IbPlanPolicy
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
    public function view(User $user, IbPlan $ibPlan): bool
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
    public function update(User $user, IbPlan $ibPlan): bool
    {
        return false;
    }

}
