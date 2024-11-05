<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Fetch all settings from the database and share them with all views
        $settings = Setting::all()->pluck('value', 'name')->toArray();
        view()->share('settings', $settings);
    }

    public function register()
    {
        //
    }
}
