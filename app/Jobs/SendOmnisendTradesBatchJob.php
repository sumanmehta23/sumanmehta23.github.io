<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\OmnisendService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendOmnisendTradesBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 30;
    public $tries = 2;

    protected int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function handle(OmnisendService $omnisendService): void
    {
        $lockKey = 'omnisend_trades_batch_send:' . $this->userId;
        $cacheKey = 'omnisend_trades_batch:' . $this->userId;

        $lock = Cache::lock($lockKey, 10);
        if (!$lock->get()) {
            Log::info('SendOmnisendTradesBatchJob: could not acquire lock', ['user_id' => $this->userId]);
            return;
        }

        try {
            $trades = Cache::get($cacheKey, []);
            Cache::forget($cacheKey);

            if (empty($trades)) {
                Log::info('SendOmnisendTradesBatchJob: no trades in batch', ['user_id' => $this->userId]);
                return;
            }

            $user = User::find($this->userId);
            if (!$user || empty($user->email)) {
                Log::warning('SendOmnisendTradesBatchJob: user not found or no email', ['user_id' => $this->userId]);
                return;
            }

            Log::info('SendOmnisendTradesBatchJob: sending Trades Opened event', ['user_id' => $user->id, 'email' => $user->email, 'trades_count' => count($trades)]);
            $omnisendService->trackBatchTradesOpened($user->email, $user->id, $trades);
        } finally {
            $lock->release();
        }
    }
}
