<?php

namespace App\Listeners;

use App\Events\AccountTradesDepositEvent;
use App\Events\KycVerifiedEvent;
use App\Events\TradeOpenedEvent;
use App\Jobs\SendOmnisendTradesBatchJob;
use App\Models\Trade;
use App\Services\OmnisendService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OmnisendEventsListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        // Force immediate logging to file
        \Illuminate\Support\Facades\File::append(
            storage_path('logs/omnisend-debug.log'),
            '[' . now()->toDateTimeString() . '] OmnisendEventsListener triggered: ' . get_class($event) . PHP_EOL
        );

        Log::info('OmnisendEventsListener: Starting to handle event', [
            'event_class' => get_class($event),
            'queue_id' => $this->job ? $this->job->getJobId() : 'unknown'
        ]);

        try {
            Log::info('OmnisendEventsListener: Resolving OmnisendService');
            // Resolve service manually to avoid constructor injection issues in queue
            $omnisendService = app(OmnisendService::class);
            Log::info('OmnisendEventsListener: OmnisendService resolved successfully');

            if ($event instanceof Registered) {
                Log::info('OmnisendEventsListener: Handling Registered event');
                // Fresh load the user to avoid serialization issues
                $user = \App\Models\User::find($event->user->id);
                if (!$user) {
                    Log::warning('OmnisendEventsListener: User not found', ['user_id' => $event->user->id]);
                    return;
                }

                // Prepare payload for Omnisend
                $contactPayload = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->fullname ?? '',
                    'last_name' => '', // lqhlaravel doesn't have separate lastname field
                    'deposit_amount' => 0,
                    'created_at' => $user->created_at ? $user->created_at->timestamp : null,
                    'registered_at' => now()->timestamp,
                    'tags' => ['Account Created'],
                ];

                // Create/update contact
                $omnisendService->createOrUpdateContact($contactPayload);

                // Also send a specific registration event for better tracking
                $omnisendService->trackEvent($user->email, 'user_registered', [
                    'user_id' => (string) $user->id,
                    'registered_at' => now()->toIso8601String(),
                    'source' => 'website'
                ]);
            } elseif ($event instanceof KycVerifiedEvent) {
                Log::info('OmnisendEventsListener: Handling KycVerifiedEvent');
                // Fresh load the user to avoid serialization issues  
                $user = \App\Models\User::find($event->user->id);
                if (!$user) {
                    Log::warning('OmnisendEventsListener: User not found', ['user_id' => $event->user->id]);
                    return;
                }

                // Prepare payload for Omnisend
                $contactPayload = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->fullname ?? '',
                    'last_name' => '', // lqhlaravel doesn't have separate lastname field
                    'deposit_amount' => 0, // Will be updated when deposit happens
                    'created_at' => $user->created_at ? $user->created_at->timestamp : null,
                    'kyc_verified' => true,
                    'kyc_verified_at' => now()->timestamp,
                    'kyc_status' => 'verified',
                    'tags' => ['KYC Completed'],
                ];

                // Create/update contact
                $omnisendService->createOrUpdateContact($contactPayload);

                // Also send a specific KYC event for better tracking
                $omnisendService->trackEvent($user->email, 'kyc_verified', [
                    'user_id' => (string) $user->id,
                    'verified_at' => now()->toIso8601String(),
                    'status' => 'verified'
                ]);
            } elseif ($event instanceof AccountTradesDepositEvent) {
                Log::info('OmnisendEventsListener: Handling AccountTradesDepositEvent');
                // Fresh load the user to avoid serialization issues
                $user = \App\Models\User::find($event->user->id);
                if (!$user) {
                    Log::warning('OmnisendEventsListener: User not found', ['user_id' => $event->user->id]);
                    return;
                }

                // Determine deposit tag based on total deposit amount
                $depositTag = $omnisendService->getDepositTag($event->totalDeposit);
                $tags = [];
                if (!empty($depositTag)) {
                    $tags[] = $depositTag;
                }

                // Prepare payload for Omnisend
                $contactPayload = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->fullname ?? '',
                    'last_name' => '', // lqhlaravel doesn't have separate lastname field
                    'deposit_amount' => $event->totalDeposit,
                    'created_at' => $user->created_at ? $user->created_at->timestamp : null,
                    'last_deposit_at' => now()->timestamp,
                    'tags' => $tags,
                ];

                // Create/update contact
                $omnisendService->createOrUpdateContact($contactPayload);

                // Also send a specific deposit event for better tracking
                $omnisendService->trackEvent($user->email, 'deposit_made', [
                    'user_id' => (string) $user->id,
                    'amount' => (float) $event->totalDeposit,
                    'deposit_at' => now()->toIso8601String(),
                    'currency' => 'USD'
                ]);
            } elseif ($event instanceof TradeOpenedEvent) {
                $this->handleTradeOpened($event, $omnisendService);
            }

            Log::info('OmnisendEventsListener: Event handled successfully', [
                'event_class' => get_class($event)
            ]);
        } catch (\Exception $e) {
            Log::error('OmnisendEventsListener error: ' . $e->getMessage(), [
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

    /**
     * Handle TradeOpenedEvent: update profile with last_open_trade_at, batch trade and dispatch send job.
     */
    protected function handleTradeOpened(TradeOpenedEvent $event, OmnisendService $omnisendService): void
    {
        $user = $event->user;
        $trade = $event->trade;

        if (!$user || !$trade || !$trade->isStatusOpen() || empty($user->email)) {
            Log::warning('OmnisendEventsListener: TradeOpenedEvent skipped (missing user, trade, or email)', [
                'user_id' => $user?->id,
                'trade_id' => $trade?->id,
            ]);
            return;
        }

        $user = \App\Models\User::find($user->id);
        if (!$user) {
            return;
        }

        // User's most recent open trade (by open_time) for profile timestamp
        $latestOpenTrade = Trade::whereHas('account', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->where('status', 'open')
            ->orderByDesc('open_time')
            ->first();

        $lastOpenTradeAt = $latestOpenTrade && $latestOpenTrade->open_time
            ? $latestOpenTrade->open_time->timestamp
            : $trade->open_time?->timestamp ?? now()->timestamp;

        Log::info('Omnisend: updating contact last_open_trade_at', [
            'user_id' => $user->id,
            'email' => $user->email,
            'last_open_trade_at' => date('Y-m-d H:i:s', $lastOpenTradeAt),
        ]);

        $contactPayload = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->fullname ?? '',
            'last_name' => '',
            'last_open_trade_at' => $lastOpenTradeAt,
        ];
        $omnisendService->createOrUpdateContact($contactPayload);

        $tradePayload = [
            'trade_id' => (string) $trade->id,
            'account_id' => (string) $trade->account_id,
            'symbol' => $trade->symbol ?? '',
            'type' => (string) ($trade->type ?? ''),
            'volume' => (float) $trade->volume,
            'open_time' => $trade->open_time ? $trade->open_time->toIso8601String() : now()->toIso8601String(),
        ];

        $cacheKey = 'omnisend_trades_batch:' . $user->id;
        $batch = Cache::get($cacheKey, []);
        $batch[] = $tradePayload;
        Cache::put($cacheKey, $batch, 60);

        Log::info('Omnisend: trade added to batch, job dispatched (5s)', ['user_id' => $user->id, 'batch_size' => count($batch)]);
        SendOmnisendTradesBatchJob::dispatch($user->id)->delay(now()->addSeconds(5));
    }
}
