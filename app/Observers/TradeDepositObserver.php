<?php

namespace App\Observers;

use App\Models\Account;
use App\Services\BalanceSyncService;
use Illuminate\Support\Facades\Log;

class TradeDepositObserver
{
    protected $balanceSyncService;

    public function __construct(BalanceSyncService $balanceSyncService)
    {
        $this->balanceSyncService = $balanceSyncService;
    }

    public function created($tradeDeposit)
    {
        // Assume account_code is the field, or we'll need to map from account_id to account_code
        $accountCode = $tradeDeposit->account_code ?? $tradeDeposit->account_id;

        if ($accountCode) {
            $this->balanceSyncService->markBalanceActivity(
                $accountCode,
                "trade_deposit:{$tradeDeposit->id}"
            );
        }
    }

    public function updated($tradeDeposit)
    {
        $accountCode = $tradeDeposit->account_code ?? $tradeDeposit->account_id;

        if ($accountCode && $tradeDeposit->isDirty(['amount', 'status'])) {
            $this->balanceSyncService->markBalanceActivity(
                $accountCode,
                "trade_deposit_updated:{$tradeDeposit->id}"
            );
        }
    }
}
