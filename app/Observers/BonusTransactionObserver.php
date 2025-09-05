<?php

namespace App\Observers;

use App\Models\Account;
use App\Services\BalanceSyncService;
use Illuminate\Support\Facades\Log;

class BonusTransactionObserver
{
    protected $balanceSyncService;

    public function __construct(BalanceSyncService $balanceSyncService)
    {
        $this->balanceSyncService = $balanceSyncService;
    }

    public function created($bonusTransaction)
    {
        // Use the account_id directly (it's a UUID string)
        if ($bonusTransaction->account_id) {
            $this->balanceSyncService->markBalanceActivity(
                $bonusTransaction->account_id,
                "bonus_transaction:{$bonusTransaction->id}"
            );
        }
    }

    public function updated($bonusTransaction)
    {
        if ($bonusTransaction->account_id && $bonusTransaction->isDirty(['amount', 'status'])) {
            $this->balanceSyncService->markBalanceActivity(
                $bonusTransaction->account_id,
                "bonus_transaction_updated:{$bonusTransaction->id}"
            );
        }
    }
}
