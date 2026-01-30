<?php

namespace App\Listeners;

use App\Services\GoHighLevelService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Sends leads to GoHighLevel (GHL) when someone subscribes through an IB page.
 * Only runs when the user registered with a referral code (IB page subscription).
 */
class GoHighLevelEventsListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if (!$event instanceof Registered) {
            return;
        }

        Log::info('GoHighLevelEventsListener: Handling Registered event', [
            'user_id' => $event->user->id ?? null,
        ]);

        try {
            $user = \App\Models\User::find($event->user->id);
            if (!$user) {
                Log::warning('GoHighLevelEventsListener: User not found', ['user_id' => $event->user->id]);
                return;
            }

            // Only send to GHL when user subscribed via IB page (has referral code / ib1 set)
            if (empty($user->ib1)) {
                Log::info('GoHighLevelEventsListener: User did not register via IB page, skipping GHL', [
                    'user_id' => $user->id,
                ]);
                return;
            }

            $ghlService = app(GoHighLevelService::class);
            if (!$ghlService->hasValidCredentials()) {
                Log::warning('GoHighLevelEventsListener: GHL credentials not configured');
                return;
            }

            $contactPayload = [
                'email' => $user->email,
                'fullname' => $user->fullname ?? '',
                'number' => $user->number ?? '',
                'country' => $user->country ?? '',
                'source' => 'IB Page',
                'refercode' => $user->ib1,
                'user_id' => $user->id,
            ];

            $ghlService->createContact($contactPayload);

            Log::info('GoHighLevelEventsListener: IB lead sent to GHL', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('GoHighLevelEventsListener error: ' . $e->getMessage(), [
                'event' => get_class($event),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
