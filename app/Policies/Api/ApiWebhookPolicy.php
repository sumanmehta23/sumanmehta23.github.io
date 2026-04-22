<?php

namespace App\Policies\Api;

use App\Models\EmployeeList;

/**
 * API Webhook Resource Policy
 * Handles authorization for webhook management and monitoring
 */
class ApiWebhookPolicy
{
    /**
     * Determine if the user can view webhook status
     */
    public function viewStatus(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->hasPermission('api:webhooks:read');
    }

    /**
     * Determine if the user can manage (enable/disable) webhooks
     */
    public function manage(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->hasPermission('api:webhooks:manage');
    }
}
