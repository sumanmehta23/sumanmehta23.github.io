<?php

namespace App\Policies\Api;

use App\Models\User;
use App\Models\EmployeeList;

/**
 * API User Resource Policy
 * Handles authorization for user data access via API endpoints
 */
class ApiUserPolicy
{
    /**
     * Determine if the user can view any users (index endpoint)
     */
    public function viewAny(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->hasPermission('api:users:read');
    }

    /**
     * Determine if the user can view a specific user
     */
    public function view(EmployeeList $admin, User $user): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        if (!$admin->hasPermission('api:users:read')) {
            return false;
        }

        // Check country restrictions
        if ($admin->hasCountryRestrictions()) {
            $allowedCountries = $admin->getCountriesIds();
            return in_array($user->country, $allowedCountries);
        }

        return true;
    }

    /**
     * Determine if the user can export user data
     */
    public function export(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->hasPermission('api:users:export');
    }
}
