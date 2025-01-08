<?php

namespace App\Policies;

use App\Models\ClientWallet;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientWalletPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClientWallet $clientWallet): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClientWallet $clientWallet): bool
    {
        return false;
    }

   
}
