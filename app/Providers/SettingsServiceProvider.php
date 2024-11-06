<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Fetch all settings from the database and share them with all views
        if (Setting::tableExists()) {
            $settings = Setting::all()->pluck('value', 'name')->toArray();
        } else {
            $settings = [];
        }
        view()->share('settings', $settings);
    }

    public function register()
    {
        //
    }
}
