<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CheckUserPermissions
{
    public function handle(Request $request, Closure $next)
    {
        $userRole = session('userData')['userRole'];
        
        if ($userRole == "Super Admin") {
            return $next($request);
        }
        $userRoleID=Cache::remember('userRoleId', 60*60*24, function () use($userRole) {
            return DB::table('roles')->where('name', $userRole)->first()->role_id;
        });
        $requestUri = $request->path();
        $rolePermissions = DB::table('permissions as p')
            ->leftJoin('pages as pg', 'p.page_id', '=', 'pg.page_id')
            ->where('p.role_id', $userRoleID)
            ->pluck('pg.filename')
            ->toArray();
        if (!in_array($requestUri, $rolePermissions) && $userRoleID != 2) {
            return response()->view('errors.401', [], 401);
        }
        return $next($request);
    }
}
