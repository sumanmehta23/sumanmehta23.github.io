<?php

namespace App\Observers;

use App\Models\TradeWithdrawals;
use App\Services\BalanceSyncService;
use Illuminate\Support\Facades\Log;

class TradeWithdrawalObserver
{
    protected $balanceSyncService;

    public function __construct(BalanceSyncService $balanceSyncService)
    {
        $this->balanceSyncService = $balanceSyncService;
    }

    public function created($tradeWithdrawal)
    {
        // Use the account_id directly (it's a UUID string)
        if ($tradeWithdrawal->account_id) {
            $this->balanceSyncService->markBalanceActivity(
                $tradeWithdrawal->account_id,
                "trade_withdrawal:{$tradeWithdrawal->id}"
            );
        }
    }

    public function updated($tradeWithdrawal)
    {
        if ($tradeWithdrawal->account_id && $tradeWithdrawal->isDirty(['amount', 'status'])) {
            $this->balanceSyncService->markBalanceActivity(
                $tradeWithdrawal->account_id,
                "trade_withdrawal_updated:{$tradeWithdrawal->id}"
            );
        }
    }
}
