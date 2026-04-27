<?php

namespace App\Policies\Api;

use App\Models\EmployeeList;

/**
 * API Wallet/Deposit Resource Policy
 * Handles authorization for deposit data access via API endpoints
 */
class ApiDepositPolicy
{
    /**
     * Determine if the user can view any deposits (index endpoint)
     */
    public function viewAny(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->hasPermission('api:deposits:read');
    }

    /**
     * Determine if the user can export deposit data
     */
    public function export(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->hasPermission('api:deposits:export');
    }
}
