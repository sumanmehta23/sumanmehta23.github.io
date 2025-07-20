<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

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
            return redirect('/admin/login');
        }
        if (Auth::guard('admin')->check()) {
            Auth::setDefaultDriver('admin');
        } else {
            Session::forget('alogin');
            return redirect('/admin/login');
        }

        return $next($request);
    }
}
