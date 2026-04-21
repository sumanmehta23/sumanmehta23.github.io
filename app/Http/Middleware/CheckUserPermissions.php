<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\PermissionAudit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CheckUserPermissions
{
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        // Check if user is authenticated on admin guard
       if(!Auth::guard('admin')->check()){
            $requestUri = $request->getPathInfo();
            if(str($requestUri)->contains('admin')){
                return redirect()->route('admin.login');
            }
            return redirect()->route('login');
        }
        $userRole = session('userData')['role_id'];

        $role= Role::find($userRole);
        // if ($role->name == "Super Admin") {
        //     return $next($request);
        // }
        // $userRoleID=Cache::remember('role_id', 60*60*24, function () use($userRole) {
        //     return DB::table('roles')->where('name', $userRole)->first()->role_id;
        // });
        $requestUri = $request->getPathInfo();

        $path = request()->path();

        $segments = explode('/', $path);
        $filteredPath = implode('/', array_slice($segments, 0, 2));
        // $rolePermissions = DB::table('permissions as p')
        //     ->leftJoin('pages as pg', 'p.page_id', '=', 'pg.id')
        //     ->where('p.role_id', $userRole)
        //     ->pluck('pg.filename')
        //     ->toArray();
        // if ((!in_array($requestUri, $rolePermissions)  ) && $userRole != 2) {
        //     if((count($segments)==3 && in_array("/".$filteredPath, $rolePermissions) ) && $userRole != 2){
        //         return $next($request);
        //     }else{
        //         return response()->view('errors.401', [], 401);
        //     }
        // }
        // dd($permissions);
        $admin = Auth::guard('admin')->user();
        if (!$admin->hasPermissions($permissions)) {
            $this->logAuthorizationFailure($request, $admin, $permissions);
            return response()->view('errors.401', [], 401);
        }
        return $next($request);
    }

    /**
     * Log authorization failure for audit trail and monitoring.
     *
     * @param Request $request
     * @param mixed $admin
     * @param array $permissions
     * @return void
     */
    private function logAuthorizationFailure(Request $request, $admin, array $permissions): void
    {
        $requestId = Str::uuid();

        $logData = [
            'request_id' => $requestId,
            'admin_id' => $admin->id,
            'admin_email' => $admin->email ?? $admin->name ?? 'unknown',
            'role_id' => $admin->role_id,
            'role_name' => $admin->role?->name ?? 'no_role',
            'path' => $request->path(),
            'method' => $request->method(),
            'required_permissions' => $permissions,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ];

        // Log to application logs
        Log::warning('Authorization failed - permission denied', $logData);

        // Store in database for audit dashboard/reporting
        try {
            if (Schema::hasTable('permission_audits')) {
                PermissionAudit::create([
                    'ip_address' => $request->ip(),
                    'employee_id' => $admin->id,
                    'action' => 'authorization_failed',
                    'resource' => 'route',
                    'resource_id' => null,
                    'old_values' => null,
                    'new_values' => json_encode([
                        'path' => $request->path(),
                        'method' => $request->method(),
                        'required' => $permissions,
                    ]),
                    'description' => "Access denied to {$request->path()} - required: " . implode(', ', $permissions),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to store authorization failure in audit table', [
                'error' => $e->getMessage(),
                'admin_id' => $admin->id,
            ]);
        }
    }
}
