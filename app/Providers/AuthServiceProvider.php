<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Dynamically register permissions from the database
        $permissions = Permission::get();

        foreach ($permissions as $permission) {
            Gate::define($permission->name, function ($user) use ($permission) {
                // Check if the user has the permission (adjust logic as needed)
                return $user->hasPermission($permission->name);
            });
        }
    }
}
