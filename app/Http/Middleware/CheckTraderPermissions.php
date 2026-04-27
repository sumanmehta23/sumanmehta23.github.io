<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks if an authenticated trader/user has the required permissions for user operations.
 *
 * This middleware enforces permission-based access control for user-side operations
 * like profile updates, trading, transactions, KYC, and affiliate operations.
 *
 * Usage in routes:
 *   Route::middleware(['auth', 'check.trader.permissions:user:editProfile'])->group(...)
 *
 * Permissions follow the pattern: user:operation
 * Examples: user:editProfile, user:trade:create, user:transaction:view
 */
class CheckTraderPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = auth()->user();

        // No authenticated user
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please log in to access this resource.');
        }

        // Check user account status
        if (!$this->isAccountActive($user)) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Your account is no longer active. Please contact support.');
        }

        // If no permissions specified, allow access (only auth required)
        if (empty($permissions)) {
            return $next($request);
        }

        // Check if user has any of the required permissions
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if ($this->hasPermission($user, $permission)) {
                $hasPermission = true;
                break;
            }
        }

        // User doesn't have required permission
        if (!$hasPermission) {
            return redirect()->back()
                ->with('error', 'You do not have permission to access this resource.')
                ->with('status', 403);
        }

        return $next($request);
    }

    /**
     * Check if user account is active and not deleted/suspended.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    private function isAccountActive($user): bool
    {
        // Check if user is not deleted/soft deleted (check deleted_at column directly)
        if (isset($user->deleted_at) && $user->deleted_at !== null) {
            return false;
        }

        // Check if user status is active (adjust field name based on your schema)
        if (isset($user->status) && $user->status !== 'active' && $user->status !== 1) {
            return false;
        }

        return true;
    }

    /**
     * Check if the user has the required permission.
     *
     * @param  \App\Models\User  $user
     * @param  string  $permission
     * @return bool
     */
    private function hasPermission($user, string $permission): bool
    {
        // User permissions check using hasPermission method if available
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }

        // Check if user has roles with permissions
        if (method_exists($user, 'roles') && $user->roles) {
            foreach ($user->roles as $role) {
                if (method_exists($role, 'permissions') && $role->permissions && $role->permissions->contains('name', $permission)) {
                    return true;
                }
            }
        }

        // Default to true for basic user operations if no permission system exists
        // This allows backward compatibility while maintaining security structure
        return $this->isBasicUserPermission($permission);
    }

    /**
     * Check if this is a basic user permission that should be allowed by default.
     * Basic authenticated users should be able to access these core operations.
     *
     * @param  string  $permission
     * @return bool
     */
    private function isBasicUserPermission(string $permission): bool
    {
        // List of permissions that basic authenticated users should have by default
        $basicPermissions = [
            // Dashboard
            'user:viewDashboard',
            'user:viewProfile',
            'user:viewAccounts',

            // Profile/Settings
            'user:editProfile',
            'user:editPassword',
            'user:editEmail',
            'user:editPhoneNumber',

            // Trading
            'user:trade:view',
            'user:trade:viewOwn',

            // Transactions
            'user:transaction:view',
            'user:transaction:viewOwn',
            'user:wallet:view',

            // KYC
            'user:kyc:view',
            'user:kyc:upload',

            // Affiliate/IB
            'user:affiliate:view',
            'user:affiliate:viewCommissions',

            // Competition
            'user:competition:view',
        ];

        return in_array($permission, $basicPermissions);
    }
}
