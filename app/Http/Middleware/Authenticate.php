<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            // If not authenticated, redirect to a specific route, e.g., 'login'
            return $request->expectsJson() ? null : route('login');
        }
        // If authenticated, redirect to the intended route or another route
        return route('dashboard');  // Redirect to 'dashboard' if already authenticated
    }
}
