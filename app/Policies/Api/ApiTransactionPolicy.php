<?php

namespace App\Policies\Api;

use App\Models\EmployeeList;

/**
 * API Transaction Resource Policy
 * Handles authorization for transaction data access via API endpoints
 */
class ApiTransactionPolicy
{
    /**
     * Determine if the user can view any transactions (index endpoint)
     */
    public function viewAny(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->hasPermission('api:transactions:read');
    }

    /**
     * Determine if the user can export transaction data
     */
    public function export(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->hasPermission('api:transactions:export');
    }

    /**
     * Determine if the user can view transaction reports
     */
    public function viewReports(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->hasPermission('api:transactions:reports');
    }
}
