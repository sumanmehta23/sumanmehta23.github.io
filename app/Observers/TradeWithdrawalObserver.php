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
        // Assume account_code is the field, or we'll need to map from account_id to account_code
        $accountCode = $tradeWithdrawal->account_code ?? $tradeWithdrawal->account_id;

        if ($accountCode) {
            $this->balanceSyncService->markBalanceActivity(
                $accountCode,
                "trade_withdrawal:{$tradeWithdrawal->id}"
            );
        }
    }

    public function updated($tradeWithdrawal)
    {
        $accountCode = $tradeWithdrawal->account_code ?? $tradeWithdrawal->account_id;

        if ($accountCode && $tradeWithdrawal->isDirty(['amount', 'status'])) {
            $this->balanceSyncService->markBalanceActivity(
                $accountCode,
                "trade_withdrawal_updated:{$tradeWithdrawal->id}"
            );
        }
    }
}
