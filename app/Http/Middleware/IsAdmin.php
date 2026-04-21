<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Check authentication first (this includes remember tokens)
        if (Auth::guard('admin')->check()) {
            Auth::setDefaultDriver('admin');
            $user = Auth::guard('admin')->user();

            // Restore session data from authenticated user
            Session::put('alogin', $user->email);
            Session::put('userRoleID', $user->role_id);
            Session::put('userRole', $user->userRole);
            Session::put('userID', $user->client_index);
            Session::put('userData', $user->toArray());
        } else {
            // Not authenticated, redirect to login
            Session::forget('alogin');

            return redirect('/admin/login');
        }

        return $next($request);
    }
}
