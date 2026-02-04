<?php

namespace App\Listeners;

use App\Events\IbCreated;
use App\Events\IbStatusChanged;
use App\Services\GoHighLevelService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Sends Main IB (Introducing Broker account holders) to GoHighLevel.
 * Triggers when:
 * 1. IB is created (automatic approval) - status = 1
 * 2. IB status changes to approved (status = 1) - admin approval
 */
class GoHighLevelMainIbListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event (Laravel expects single handle method).
     */
    public function handle(object $event): void
    {
        Log::info('GoHighLevelMainIbListener: Event received', [
            'event_class' => get_class($event),
        ]);

        if ($event instanceof IbCreated) {
            $this->handleIbCreated($event);
        } elseif ($event instanceof IbStatusChanged) {
            $this->handleIbStatusChanged($event);
        } else {
            Log::warning('GoHighLevelMainIbListener: Unknown event type', [
                'event_class' => get_class($event),
            ]);
        }
    }

    /**
     * Handle IB created event (Main IB enrollment).
     */
    protected function handleIbCreated(IbCreated $event): void
    {
        $ib = $event->ib;

        // Only send to GHL if IB is active (status = 1)
        if ($ib->status != 1) {
            Log::info('GoHighLevelMainIbListener: IB created but not active, skipping GHL', [
                'ib_id' => $ib->id,
                'status' => $ib->status,
            ]);
            return;
        }

        $this->sendMainIbToGhl($ib, 'IB Created');
    }

    /**
     * Handle IB status changed event (admin approval).
     */
    protected function handleIbStatusChanged(IbStatusChanged $event): void
    {
        $ib = $event->ib;

        // Only send to GHL when status changes to active (1)
        if ($event->newStatus != 1) {
            Log::info('GoHighLevelMainIbListener: IB status changed but not to active, skipping GHL', [
                'ib_id' => $ib->id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
            ]);
            return;
        }

        // Skip if already was active (avoid duplicate sends)
        if ($event->oldStatus == 1) {
            Log::info('GoHighLevelMainIbListener: IB already active, skipping duplicate send', [
                'ib_id' => $ib->id,
            ]);
            return;
        }

        $this->sendMainIbToGhl($ib, 'IB Approved');
    }

    /**
     * Send Main IB contact to GoHighLevel.
     */
    protected function sendMainIbToGhl($ib, string $context): void
    {
        try {
            $ghlService = app(GoHighLevelService::class);
            if (!$ghlService->hasValidCredentials()) {
                Log::warning('GoHighLevelMainIbListener: GHL credentials not configured');
                return;
            }

            
            // Load user relationship if not already loaded
            if (!$ib->relationLoaded('user')) {
                $ib->load('user');
            }

            $user = $ib->user;
            if (!$user) {
                Log::warning('GoHighLevelMainIbListener: User not found for IB', [
                    'ib_id' => $ib->id,
                    'user_id' => $ib->user_id,
                ]);
                return;
            }

            $contactPayload = [
                'email' => $ib->email ?? $user->email,
                'fullname' => $ib->name ?? $user->fullname ?? '',
                'number' => $ib->number ?? $user->number ?? '',
                'country' => $ib->country ?? $user->country ?? '',
                'source' => 'Main IB',
                'refercode' => $ib->referral_code,
                'user_id' => $user->id,
                'tags' => ['Main IB'],
            ];

            $result = $ghlService->createContact($contactPayload);

            if ($result) {
                Log::info('GoHighLevelMainIbListener: Main IB upserted to GHL successfully', [
                    'ib_id' => $ib->id,
                    'user_id' => $user->id,
                    'email' => $contactPayload['email'],
                    'phone' => $contactPayload['number'],
                    'context' => $context,
                ]);
            } else {
                Log::warning('GoHighLevelMainIbListener: Failed to upsert Main IB to GHL', [
                    'ib_id' => $ib->id,
                    'user_id' => $user->id,
                    'email' => $contactPayload['email'],
                    'phone' => $contactPayload['number'],
                    'context' => $context,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('GoHighLevelMainIbListener error: ' . $e->getMessage(), [
                'ib_id' => $ib->id ?? null,
                'context' => $context,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
