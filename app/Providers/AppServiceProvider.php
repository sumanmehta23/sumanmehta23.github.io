<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);


        RateLimiter::for('deposit', function ($request) {
            // Limit to 1 request every 10 seconds
            return Limit::perSeconds(10, 1)->by(optional($request->user())->id ?: $request->ip());
        });
        RateLimiter::for('withdraw', function ($request) {
            // Limit to 1 request every 10 seconds
            return Limit::perSeconds(10, 1)->by(optional($request->user())->id ?: $request->ip());
        });
        RateLimiter::for('processTransfer', function ($request) {
            // Limit to 1 request every 10 seconds
            return Limit::perSeconds(10, 1)->by(optional($request->user())->id ?: $request->ip());
        });
        RateLimiter::for('withdrawal', function ($request) {
            // Limit to 1 request every 10 seconds
            return Limit::perSeconds(10, 1)->by(optional($request->user())->id ?: $request->ip());
        });
        RateLimiter::for('bonusToAccount', function ($request) {
            // Limit to 1 request every 10 seconds
            return Limit::perSeconds(10, 1)->by(optional($request->user())->id ?: $request->ip());
        });
        RateLimiter::for('creditBonusToAccount', function ($request) {
            // Limit to 1 request every 10 seconds
            return Limit::perSeconds(10, 1)->by(optional($request->user())->id ?: $request->ip());
        });
        RateLimiter::for('sendResetLink', function ($request) {
            // Limit to 5 request every 10 seconds
            return Limit::perSeconds(600, 5)->by(optional($request->user())->id ?: $request->ip());
        });
        RateLimiter::for('login', function ($request) {
            // Limit to 5 request every 10 seconds
            return Limit::perSeconds(300, 3)->by(optional($request->user())->id ?: $request->ip());
        });


    }
}
