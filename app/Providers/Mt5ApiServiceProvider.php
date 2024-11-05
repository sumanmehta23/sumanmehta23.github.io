<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\MT5\MTWebAPI;

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
        // Instantiate MTWebAPI
        $agent = config('constants.AGENT');
        $path_to_logs = config('constants.PATH_TO_LOGS');

        $this->api = new MTWebAPI($agent, $path_to_logs);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
