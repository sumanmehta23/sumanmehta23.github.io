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
        // Use the account_id directly (it's a UUID string)
        if ($tradeDeposit->account_id) {
            $this->balanceSyncService->markBalanceActivity(
                $tradeDeposit->account_id,
                "trade_deposit:{$tradeDeposit->id}"
            );
        }
    }

    public function updated($tradeDeposit)
    {
        if ($tradeDeposit->account_id && $tradeDeposit->isDirty(['amount', 'status'])) {
            $this->balanceSyncService->markBalanceActivity(
                $tradeDeposit->account_id,
                "trade_deposit_updated:{$tradeDeposit->id}"
            );
        }
    }
}
