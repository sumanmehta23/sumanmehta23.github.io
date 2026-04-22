<?php

namespace App\Policies;

use App\Models\Trade;
use App\Models\EmployeeList;

/**
 * TradePolicy
 *
 * Authorization policy for admin management of trading data.
 * Controls access to trades, trade history, and export operations.
 *
 * Trades are tied to accounts which are tied to users (clients) by country.
 */
class TradePolicy
{
    /**
     * Determine whether admin can view all trades.
     *
     * @param EmployeeList $admin
     * @return bool
     */
    public function viewAny(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        // Only admins with explicit permission can view trades
        if (!$admin->hasPermission('trade:viewAny')) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether admin can view a specific trade.
     *
     * @param EmployeeList $admin
     * @param Trade $trade
     * @return bool
     */
    public function view(EmployeeList $admin, Trade $trade): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        // Check permission
        if (!$admin->hasPermission('trade:view')) {
            return false;
        }

        // Check country access via the account's user
        if ($admin->hasCountryRestrictions()) {
            $account = $trade->account;
            if ($account && $account->user) {
                if (!in_array($account->user->country_id, $admin->getCountriesIds())) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Determine whether admin can export trades.
     *
     * @param EmployeeList $admin
     * @return bool
     */
    public function export(EmployeeList $admin): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        // Check permission
        if (!$admin->hasPermission('trade:export')) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether admin can view trade analysis/reports.
     *
     * @param EmployeeList $admin
     * @param Trade $trade
     * @return bool
     */
    public function viewAnalysis(EmployeeList $admin, Trade $trade): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        // Check permission
        if (!$admin->hasPermission('trade:viewAnalysis')) {
            return false;
        }

        // Check country access
        if ($admin->hasCountryRestrictions()) {
            $account = $trade->account;
            if ($account && $account->user) {
                if (!in_array($account->user->country_id, $admin->getCountriesIds())) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Determine whether admin can close/cancel a trade.
     *
     * @param EmployeeList $admin
     * @param Trade $trade
     * @return bool
     */
    public function close(EmployeeList $admin, Trade $trade): bool
    {
        // Super Admin bypass
        if ($admin->isSuperAdmin()) {
            return true;
        }

        // Check permission
        if (!$admin->hasPermission('trade:close')) {
            return false;
        }

        // Check country access
        if ($admin->hasCountryRestrictions()) {
            $account = $trade->account;
            if ($account && $account->user) {
                if (!in_array($account->user->country_id, $admin->getCountriesIds())) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Determine whether admin can delete trade records (rare operation).
     *
     * @param EmployeeList $admin
     * @param Trade $trade
     * @return bool
     */
    public function delete(EmployeeList $admin, Trade $trade): bool
    {
        // Only Super Admin can delete trades
        return $admin->isSuperAdmin();
    }
}
