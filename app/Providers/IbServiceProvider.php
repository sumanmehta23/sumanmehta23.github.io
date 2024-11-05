<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\IbController;
use App\Models\Ib1;

class IbServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('*', function ($view) {
            $email = session('clogin');
            if ($email) {
                $ibResult = Ib1::where('email', $email)->first();
                $view->with('ibResult', $ibResult);
            } else {
                $view->with('ibResult', null);
            }
        });
    }

}
