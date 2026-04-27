<?php

namespace App\Policies;

use App\Models\User;

/**
 * User Profile Policy
 * Handles authorization for user profile and account settings
 */
class UserProfilePolicy
{
    /**
     * View own profile
     */
    public function view(User $user, User $targetUser): bool
    {
        // Users can only view their own profile
        return $user->id === $targetUser->id;
    }

    /**
     * Edit own profile
     */
    public function update(User $user, User $targetUser): bool
    {
        // Users can only edit their own profile
        return $user->id === $targetUser->id && !$user->trashed();
    }

    /**
     * Change password
     */
    public function changePassword(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * Update email address
     */
    public function updateEmail(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * Update phone number
     */
    public function updatePhone(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * Enable/disable two-factor authentication
     */
    public function manage2FA(User $user): bool
    {
        return !$user->trashed();
    }
}
