<?php

namespace App\Policies;

use App\Models\User;

/**
 * User KYC Policy
 * Handles authorization for user KYC (Know Your Customer) operations
 */
class UserKycPolicy
{
    /**
     * View own KYC status
     */
    public function view(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * Upload/submit KYC documents
     */
    public function upload(User $user): bool
    {
        if ($user->trashed()) {
            return false;
        }

        // Check if KYC is not already approved
        // Users typically can resubmit if rejected or pending
        return in_array($user->kyc_verify, ['pending', 'rejected', 'not_verified', null]);
    }

    /**
     * View KYC documents
     */
    public function viewDocuments(User $user): bool
    {
        return !$user->trashed();
    }

    /**
     * Resubmit KYC after rejection
     */
    public function resubmit(User $user): bool
    {
        if ($user->trashed()) {
            return false;
        }

        // Allow resubmit only if rejected
        return $user->kyc_verify === 'rejected';
    }
}
