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
        // Assume account_code is the field, or we'll need to map from account_id to account_code
        $accountCode = $bonusTransaction->account_code ?? $bonusTransaction->account_id;

        if ($accountCode) {
            $this->balanceSyncService->markBalanceActivity(
                $accountCode,
                "bonus_transaction:{$bonusTransaction->id}"
            );
        }
    }

    public function updated($bonusTransaction)
    {
        $accountCode = $bonusTransaction->account_code ?? $bonusTransaction->account_id;

        if ($accountCode && $bonusTransaction->isDirty(['amount', 'status'])) {
            $this->balanceSyncService->markBalanceActivity(
                $accountCode,
                "bonus_transaction_updated:{$bonusTransaction->id}"
            );
        }
    }
}
