<?php

namespace App\Providers;

use App\Events\AccountTradesDepositEvent;
use App\Events\IbCreated;
use App\Events\IbStatusChanged;
use App\Events\KycVerifiedEvent;
use App\Listeners\GoHighLevelMainIbListener;
use App\Listeners\OmnisendEventsListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            OmnisendEventsListener::class,
            // GoHighLevelEventsListener removed - Only Main IB should be sent to GHL, not Referred IB
        ],

        KycVerifiedEvent::class => [
            OmnisendEventsListener::class,
        ],

        AccountTradesDepositEvent::class => [
            OmnisendEventsListener::class,
        ],

        IbCreated::class => [
            GoHighLevelMainIbListener::class,
        ],

        IbStatusChanged::class => [
            GoHighLevelMainIbListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
