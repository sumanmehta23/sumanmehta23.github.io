<?php

namespace App\Traits;

use App\Models\EmployeeList;

/**
 * CountryFilterable Trait
 *
 * Provides query scope to automatically filter results based on admin's country restrictions.
 *
 * Usage in queries:
 *   $clients = Client::whereAllowedCountries(auth('admin')->user())->get();
 *   $accounts = Account::whereAllowedCountries($admin)->get();
 *
 * Only applies filtering if the admin has country restrictions.
 * Super Admin and admins without restrictions get all results.
 */
trait CountryFilterable
{
    /**
     * Scope to filter query by admin's allowed countries.
     *
     * If admin has no country restrictions, returns all records (unfiltered).
     * If admin has country restrictions, only returns records matching those countries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param EmployeeList|null $admin
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereAllowedCountries($query, ?EmployeeList $admin = null)
    {
        // Use current authenticated admin if none provided
        if ($admin === null) {
            $admin = auth('admin')->user();
        }

        // If no admin or admin is not authenticated, return query as-is
        if (!$admin) {
            return $query;
        }

        // If admin is Super Admin, no filtering needed (has access to all)
        if ($admin->isSuperAdmin()) {
            return $query;
        }

        // If admin has country restrictions, filter by allowed countries
        if ($admin->hasCountryRestrictions()) {
            $allowedCountries = $admin->getCountriesIds();

            return $query->whereIn('country_id', $allowedCountries);
        }

        // No restrictions, return all
        return $query;
    }

    /**
     * Scope to filter query by a single country.
     *
     * Checks if admin has access to the requested country.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $countryId
     * @param EmployeeList|null $admin
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereCountry($query, $countryId, ?EmployeeList $admin = null)
    {
        if ($admin === null) {
            $admin = auth('admin')->user();
        }

        if (!$admin) {
            return $query->where('country_id', $countryId);
        }

        // If admin has country restrictions, verify access
        if ($admin->hasCountryRestrictions()) {
            $allowedCountries = $admin->getCountriesIds();

            if (!in_array($countryId, $allowedCountries)) {
                // Return empty query - admin not allowed
                return $query->whereRaw('0');
            }
        }

        return $query->where('country_id', $countryId);
    }

    /**
     * Scope to exclude specific countries from results.
     *
     * Useful for queries where you want to show everything except certain countries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $countryIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereNotCountries($query, array $countryIds)
    {
        return $query->whereNotIn('country_id', $countryIds);
    }

    /**
     * Check if current admin can access this country.
     *
     * Useful for instance methods or policies.
     *
     * @param mixed $countryId
     * @param EmployeeList|null $admin
     * @return bool
     */
    public function isAccessibleToAdmin($countryId, ?EmployeeList $admin = null): bool
    {
        if ($admin === null) {
            $admin = auth('admin')->user();
        }

        if (!$admin) {
            return false;
        }

        // Super Admin can access all
        if ($admin->isSuperAdmin()) {
            return true;
        }

        // If no restrictions, can access
        if (!$admin->hasCountryRestrictions()) {
            return true;
        }

        // Check if country is in allowed list
        return in_array($countryId, $admin->getCountriesIds());
    }
}
