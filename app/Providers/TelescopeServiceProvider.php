<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Laravel\Telescope\Telescope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        Telescope::filter(function (IncomingEntry $entry) {
            return app()->isLocal() ||
                $entry->type === 'client-request' ||
                $entry->isReportableException() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return (in_array($user->email, [
                'admin@lqhmarkets.com'
            ]) || Str::endsWith($user->email, '@tradetech.app'));
        });
    }
    protected function authorization()
    {
        $this->gate();
        Telescope::auth(function ($request) {
            if (Auth::guard('admin')->check()) {
                Auth::shouldUse('admin');
            }
            return Gate::check('viewTelescope', [$request->user('admin')]);
        });
    }
}
