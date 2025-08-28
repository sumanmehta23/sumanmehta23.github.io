<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(300)->by($request->user()?->id ?: $request->ip());
        });

        // Higher rate limit for pagination endpoints
        RateLimiter::for('api-pagination', function (Request $request) {
            // Authenticated users get higher limits
            if ($request->user()) {
                return Limit::perMinute(2000)->by($request->user()->id);
            }

            // Unauthenticated users (by IP) get lower limits
            return Limit::perMinute(500)->by($request->ip());
        });

        // Very high rate limit for internal/trusted applications
        RateLimiter::for('api-internal', function (Request $request) {
            return Limit::perMinute(5000)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
