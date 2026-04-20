<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Admin managing client/user resources
        \App\Models\User::class => \App\Policies\ClientPolicy::class,

        // Admin managing trading accounts
        \App\Models\Account::class => \App\Policies\AccountPolicy::class,

        // Admin managing system roles
        \App\Models\Role::class => \App\Policies\RolePolicy::class,

        // Admin managing system permissions
        \App\Models\Permission::class => \App\Policies\PermissionPolicy::class,

        // Admin managing staff/employees
        \App\Models\EmployeeList::class => \App\Policies\EmployeePolicy::class,

        // Admin managing trades
        \App\Models\Trade::class => \App\Policies\TradePolicy::class,

        // API-specific policies (registered for reference, mostly used via middleware)
        'api_user' => \App\Policies\Api\ApiUserPolicy::class,
        'api_trade' => \App\Policies\Api\ApiTradePolicy::class,
        'api_transaction' => \App\Policies\Api\ApiTransactionPolicy::class,
        'api_withdrawal' => \App\Policies\Api\ApiWithdrawalPolicy::class,
        'api_deposit' => \App\Policies\Api\ApiDepositPolicy::class,
        'api_webhook' => \App\Policies\Api\ApiWebhookPolicy::class,

        // User/Trader-side policies (user operations authorization)
        'user_dashboard' => \App\Policies\UserDashboardPolicy::class,
        'user_profile' => \App\Policies\UserProfilePolicy::class,
        'user_trade' => \App\Policies\UserTradePolicy::class,
        'user_transaction' => \App\Policies\UserTransactionPolicy::class,
        'user_kyc' => \App\Policies\UserKycPolicy::class,
        'user_affiliate' => \App\Policies\UserAffiliatePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Dynamically register permissions from the database
        if (Schema::hasTable('permissions')) {
            $permissions = Permission::get();
            foreach ($permissions as $permission) {
                Gate::define($permission->name, function ($user) use ($permission) {
                    return $user->hasPermission($permission->name);
                });
            }
        }
    }
}
