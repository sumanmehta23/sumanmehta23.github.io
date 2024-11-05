<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RMConditionProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $rmCondition='';
        view()->share('rmCondition', $rmCondition);
    }
}
