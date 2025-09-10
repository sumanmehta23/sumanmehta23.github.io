<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\MT5\MTWebAPI;
use App\Services\MT5ConnectionManager;
use App\Services\UniversalMT5Service;
use App\Services\EnhancedUniversalMT5Service;
use App\Services\RedisCoordinatedMT5Service;

class Mt5ApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Load MT5 API files
        $mt5ApiPath = app_path('mt5');
        require_once "$mt5ApiPath/MTWebAPI.php";

        // Register MT5 Connection Manager as singleton
        $this->app->singleton(MT5ConnectionManager::class, function ($app) {
            return MT5ConnectionManager::getInstance();
        });

        // Register Universal MT5 Service (original)
        $this->app->singleton(UniversalMT5Service::class, function ($app) {
            return new UniversalMT5Service();
        });

        // Register Enhanced Universal MT5 Service with Redis coordination
        $this->app->singleton(EnhancedUniversalMT5Service::class, function ($app) {
            return new EnhancedUniversalMT5Service();
        });

        // Register Redis Coordinated MT5 Service
        $this->app->singleton(RedisCoordinatedMT5Service::class, function ($app) {
            return new RedisCoordinatedMT5Service();
        });

        // Register MT5 REST API Service with connection pooling
        $this->app->singleton(\App\Services\MT5RestAPIService::class, function ($app) {
            return new \App\Services\MT5RestAPIService();
        });

        // Alias for primary MT5 service (configurable)
        $primaryService = config('mt5.use_redis_coordination', true)
            ? EnhancedUniversalMT5Service::class
            : UniversalMT5Service::class;

        $this->app->alias($primaryService, 'mt5.service');

        // Legacy MTWebAPI registration for backward compatibility
        $this->app->singleton(MTWebAPI::class, function ($app) {
            $agent = config('constants.AGENT');
            $path_to_logs = config('constants.PATH_TO_LOGS');
            return new MTWebAPI($agent, $path_to_logs);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
