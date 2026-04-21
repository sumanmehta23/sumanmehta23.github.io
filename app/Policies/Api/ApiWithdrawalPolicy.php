<?php

namespace App\Policies\Api;

use App\Models\EmployeeList;

/**
 * API Withdrawal Resource Policy
 * Handles authorization for withdrawal data access via API endpoints
 */
class ApiWithdrawalPolicy
{
    /**
     * Determine if the user can view any withdrawals (index endpoint)
     */
    public function viewAny(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->hasPermission('api:withdrawals:read');
    }

    /**
     * Determine if the user can export withdrawal data
     */
    public function export(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->hasPermission('api:withdrawals:export');
    }
}
