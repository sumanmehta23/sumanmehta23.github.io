<?php

namespace App\Traits;

/**
 * AuthorizationHelpers Trait
 *
 * Provides common authorization helper methods.
 * Used by both EmployeeList (admin) and User models.
 *
 * Centralizes authorization logic to prevent duplication.
 */
trait AuthorizationHelpers
{
    /**
     * Check if this user is a Super Admin.
     *
     * Uses flag instead of string comparison for resilience.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        // EmployeeList model
        if (method_exists($this, 'role')) {
            return $this->role?->name === 'Super Admin' ||
                $this->role?->is_super_admin === true;
        }

        // Other models don't have super admin status
        return false;
    }

    /**
     * Check if user has country-based access restrictions.
     *
     * @return bool
     */
    public function hasCountryRestrictions(): bool
    {
        // Only EmployeeList has country restrictions
        if (!method_exists($this, 'role')) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return false;
        }

        if (!$this->role?->relationLoaded('countries')) {
            $this->load('role.countries');
        }

        return $this->role?->countries?->isNotEmpty() ?? false;
    }

    /**
     * Get array of country IDs for this user.
     *
     * Only relevant for EmployeeList with country restrictions.
     *
     * @return array
     */
    public function getCountriesIds(): array
    {
        if (!method_exists($this, 'role')) {
            return [];
        }

        if (!$this->role?->relationLoaded('countries')) {
            $this->load('role.countries');
        }

        return $this->role?->countries?->pluck('id')->toArray() ?? [];
    }

    /**
     * Get country names for this user.
     *
     * @return array|null
     */
    public function getAllowedCountries(): ?array
    {
        if (!method_exists($this, 'role')) {
            return null;
        }

        if ($this->isSuperAdmin()) {
            return null; // Super admin has access to all
        }

        if (!$this->role?->relationLoaded('countries')) {
            $this->load('role.countries');
        }

        if ($this->role?->countries?->isEmpty()) {
            return null; // No countries assigned = access to all
        }

        return $this->role?->countries?->pluck('country_name')->toArray();
    }
}
