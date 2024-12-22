<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (!session('alogin')) {
            return redirect('/admin/login')->with('error','You do not have access to this page.');
        }
        if (Auth::guard('admin')->check()) {
            Auth::setDefaultDriver('admin');
        }

        return $next($request);
    }
}
