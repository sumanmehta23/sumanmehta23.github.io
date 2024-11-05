<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckUserPermissions
{
    public function handle(Request $request, Closure $next)
    {
        $userRoleID = session('userData')['role_id'];
        if ($userRoleID == 1) {
            return $next($request);
        }
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
