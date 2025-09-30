<?php

namespace App\Listeners;

use App\Events\AccountTradesDepositEvent;
use App\Events\KycVerifiedEvent;
use App\Services\CustomerIoService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CustomerIoEventsListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        Log::info('CustomerIoEventsListener: Starting to handle event', [
            'event_class' => get_class($event),
            'queue_id' => $this->job ? $this->job->getJobId() : 'unknown'
        ]);
        
        try {
            Log::info('CustomerIoEventsListener: Resolving CustomerIoService');
            // Resolve service manually to avoid constructor injection issues in queue
            $customerIoService = app(CustomerIoService::class);
            Log::info('CustomerIoEventsListener: CustomerIoService resolved successfully');
            
            if ($event instanceof Registered) {
                Log::info('CustomerIoEventsListener: Handling Registered event');
                // Fresh load the user to avoid serialization issues
                $user = \App\Models\User::find($event->user->id);
                if (!$user) {
                    Log::warning('CustomerIoEventsListener: User not found', ['user_id' => $event->user->id]);
                    return;
                }
                
                // Prepare payload for Customer.io
                $customerIoPayload = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->fullname ?? '',
                    'last_name' => '', // lqhlaravel doesn't have separate lastname field
                    'deposit_amount' => 0,
                    'created_at' => $user->created_at ? $user->created_at->timestamp : null,
                    'updated_at' => $user->updated_at ? $user->updated_at->timestamp : null,
                    'registered_at' => now()->timestamp,
                ];

                // Update customer attributes
                $customerIoService->createOrUpdateCustomer($customerIoPayload);
                
                // Also send a specific registration event for better tracking
                $customerIoService->trackEvent($user->email, 'user_registered', [
                    'user_id' => $user->id,
                    'registered_at' => now()->timestamp,
                    'source' => 'website'
                ]);

            } elseif ($event instanceof KycVerifiedEvent) {
                Log::info('CustomerIoEventsListener: Handling KycVerifiedEvent');
                // Fresh load the user to avoid serialization issues  
                $user = \App\Models\User::find($event->user->id);
                if (!$user) {
                    Log::warning('CustomerIoEventsListener: User not found', ['user_id' => $event->user->id]);
                    return;
                }
                
                // Prepare payload for Customer.io
                $customerIoPayload = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->fullname ?? '',
                    'last_name' => '', // lqhlaravel doesn't have separate lastname field
                    'deposit_amount' => 0, // Will be updated when deposit happens
                    'created_at' => $user->created_at ? $user->created_at->timestamp : null,
                    'updated_at' => $user->updated_at ? $user->updated_at->timestamp : null,
                    'kyc_verified' => true,
                    'kyc_verified_at' => now()->timestamp,
                    'kyc_status' => 'verified',
                ];

                // Update customer attributes
                $customerIoService->createOrUpdateCustomer($customerIoPayload);
                
                // Also send a specific KYC event for better tracking
                $customerIoService->trackEvent($user->email, 'kyc_verified', [
                    'user_id' => $user->id,
                    'verified_at' => now()->timestamp,
                    'status' => 'verified'
                ]);

            } elseif ($event instanceof AccountTradesDepositEvent) {
                Log::info('CustomerIoEventsListener: Handling AccountTradesDepositEvent');
                // Fresh load the user to avoid serialization issues
                $user = \App\Models\User::find($event->user->id);
                if (!$user) {
                    Log::warning('CustomerIoEventsListener: User not found', ['user_id' => $event->user->id]);
                    return;
                }
                
                // Prepare payload for Customer.io
                $customerIoPayload = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->fullname ?? '',
                    'last_name' => '', // lqhlaravel doesn't have separate lastname field
                    'deposit_amount' => $event->totalDeposit,
                    'created_at' => $user->created_at ? $user->created_at->timestamp : null,
                    'updated_at' => $user->updated_at ? $user->updated_at->timestamp : null,
                    'last_deposit_at' => now()->timestamp,
                ];

                // Update customer attributes
                $customerIoService->createOrUpdateCustomer($customerIoPayload);
                
                // Also send a specific deposit event for better tracking
                $customerIoService->trackEvent($user->email, 'deposit_made', [
                    'user_id' => $user->id,
                    'amount' => $event->totalDeposit,
                    'deposit_at' => now()->timestamp,
                    'currency' => 'USD'
                ]);
            }

            Log::info('CustomerIoEventsListener: Event handled successfully', [
                'event_class' => get_class($event)
            ]);
            
        } catch (\Exception $e) {
            Log::error('CustomerIoEventsListener error: ' . $e->getMessage(), [
                'event' => get_class($event),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            // Re-throw to make queue job fail and see the error
            throw $e;
        }
    }
}
