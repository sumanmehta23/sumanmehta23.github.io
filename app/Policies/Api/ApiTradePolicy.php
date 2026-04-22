<?php

namespace App\Policies\Api;

use App\Models\Trade;
use App\Models\EmployeeList;

/**
 * API Trade Resource Policy
 * Handles authorization for trade data access via API endpoints
 */
class ApiTradePolicy
{
    /**
     * Determine if the user can view any trades (index endpoint)
     */
    public function viewAny(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->hasPermission('api:trades:read');
    }

    /**
     * Determine if the user can view a specific trade
     */
    public function view(EmployeeList $admin, Trade $trade): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        if (!$admin->hasPermission('api:trades:read')) {
            return false;
        }

        // Check country restrictions via account -> user relationship
        if ($admin->hasCountryRestrictions()) {
            $allowedCountries = $admin->getCountriesIds();
            $userCountry = $trade->account->user->country ?? null;
            return in_array($userCountry, $allowedCountries);
        }

        return true;
    }

    /**
     * Determine if the user can export trade data
     */
    public function export(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->hasPermission('api:trades:export');
    }

    /**
     * Determine if the user can view trade analysis
     */
    public function analyze(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->hasPermission('api:trades:analyze');
    }
}
