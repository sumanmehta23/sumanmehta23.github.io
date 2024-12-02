<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CheckUserPermissions
{
    public function handle(Request $request, Closure $next)
    {
        $userRole = session('userData')['role_id'];
        $role= Role::find($userRole);
        if ($role->name == "Super Admin") {
            return $next($request);
        }
        // $userRoleID=Cache::remember('role_id', 60*60*24, function () use($userRole) {
        //     return DB::table('roles')->where('name', $userRole)->first()->role_id;
        // });
        $requestUri = $request->getPathInfo();
        $rolePermissions = DB::table('permissions as p')
            ->leftJoin('pages as pg', 'p.page_id', '=', 'pg.id')
            ->where('p.role_id', $userRole)
            ->pluck('pg.filename')
            ->toArray();

        
       
        
        if (!in_array($requestUri, $rolePermissions) && $userRole != 2) {
            return response()->view('errors.401', [], 401);
        }
        return $next($request);
    }
}
