<?php

namespace App\Policies;

use App\Models\Ib1;
use App\Models\EmployeeList;
use Illuminate\Auth\Access\Response;

class IbPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(EmployeeList $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(EmployeeList $user, Ib1 $ib1): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(EmployeeList $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(EmployeeList $user, Ib1 $ib1): bool
    {
        info("User is ".$user->id);
        return false;
    }
    /**
     * Determine whether the user can update the model.
     */
    public function manageSettings(EmployeeList $user, Ib1 $ib1): bool
    {
        return false;
    }
    /**
     * Determine whether the user can update the model.
     */
    public function manageRequests(EmployeeList $user, Ib1 $ib1): bool
    {
        return false;
    }

}
