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
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        
       if(!Auth::check()){
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
        if (!$request->user()->hasPermissions($permissions)) {
            return response()->view('errors.401', [], 401); 
        }
        return $next($request);
    }
}
