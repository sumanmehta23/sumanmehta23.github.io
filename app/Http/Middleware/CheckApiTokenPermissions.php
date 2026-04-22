<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\PermissionAudit;

/**
 * Checks if an authenticated Sanctum token holder has the required API permissions.
 *
 * Usage in routes:
 *   Route::middleware(['auth:sanctum', 'check.api.permissions:api:users:read'])->group(...)
 *
 * Permissions follow the pattern: api:resource:action
 * Examples: api:users:read, api:trades:read, api:withdrawals:write
 */
class CheckApiTokenPermissions
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
        $user = $request->user();

        // No authenticated user
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'API token is required for this endpoint'
            ], 401);
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
            $this->auditUnauthorizedAccess($request, $user, $permissions);

            return response()->json([
                'message' => 'Forbidden',
                'error' => 'You do not have permission to access this API endpoint'
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if the user has the required permission.
     * For API tokens, permission checking uses the associated user's role and permissions.
     *
     * @param  \Illuminate\Foundation\Auth\User  $user
     * @param  string  $permission
     * @return bool
     */
    private function hasPermission($user, string $permission): bool
    {
        // If user is an EmployeeList (admin), check their permissions via traits
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true; // Super Admin has all API permissions
        }

        // Check permission in user's role
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }

        // If user has roles relationship (polymorphic)
        if ($user->roles) {
            foreach ($user->roles as $role) {
                if ($role->permissions && $role->permissions->contains('name', $permission)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Audit failed API authorization attempts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Foundation\Auth\User  $user
     * @param  array  $permissions
     * @return void
     */
    private function auditUnauthorizedAccess(Request $request, $user, array $permissions): void
    {
        try {
            PermissionAudit::create([
                'employee_id' => $user->id ?? null,
                'action' => 'api_access_denied',
                'resource' => implode(',', $permissions),
                'path' => $request->path(),
                'ip_address' => $request->ip(),
                'method' => $request->method(),
                'description' => 'Unauthorized API access attempt - insufficient permissions',
            ]);
        } catch (\Exception $e) {
            // Silent fail - don't break the request if audit logging fails
            \Illuminate\Support\Facades\Log::warning('Failed to audit API access: ' . $e->getMessage());
        }
    }
}
