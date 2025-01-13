<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Employee;
use App\Models\EmployeeList;
use Illuminate\Auth\Access\Response;

class EmployeePolicy
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
    public function view(User $user, EmployeeList $employee): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(EmployeeList $user): bool
    {

        return $user->can("employee.create");
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(EmployeeList $user, EmployeeList $employee): bool
    {
        return $user->can("employee.update");
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmployeeList $employee): bool
    {
        return false;
    }


}
