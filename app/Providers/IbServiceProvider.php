<?php
namespace App\Providers;

use App\Models\Ib1;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\IbController;
use Illuminate\Support\ServiceProvider;

class IbServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('*', function ($view) {
            $user = auth()->user();
            if ($user && !isset($user->userRole)) {
                $cacheKey = 'ib1_' . $user->id;
                if (Cache::has($cacheKey)) {
                    $ibResult=Cache::get($cacheKey);
                } else {
                    $ibResult =Ib1::where('user_id', $user->id)->first();
                    Cache::put($cacheKey, $ibResult, 60);
                }
                $view->with('ibResult', $ibResult);
            } else {
                $view->with('ibResult', null);
            }
        });
    }

}
