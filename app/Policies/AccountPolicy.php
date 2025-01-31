<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\EmployeeList;
use Illuminate\Auth\Access\Response;

class AccountPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(EmployeeList $user): bool
    {
        return false;
    }
    public function viewLiveAccounts(EmployeeList $user): bool
    {
        return false;
    }
    public function viewDemoAccounts(EmployeeList $user): bool
    {
        return false;
    }
    public function viewRequestedAccounts(EmployeeList $user): bool
    {
        return false;
    }
    /**
     * Determine whether the user can view the model.
     */
    public function view(EmployeeList $user, Account $account): bool
    {
        return false;
    }
    /**
     * Determine whether the user can view the credentials.
     */
    public function viewCredentials(EmployeeList $user, Account $account): bool
    {
        return false;
    }
    /**
     * Determine whether the user can view the settings.
     */
    public function viewSettings(EmployeeList $user, Account $account): bool
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
    public function update(EmployeeList $user, Account $account): bool
    {
        return false;
    }
}
