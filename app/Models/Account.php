<?php

namespace App\Models;

use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory, HasUuids, SoftDeletes;
    protected $guarded = [];
    public function casts()
    {
        return [
            'trader_password' => 'encrypted',
            'invester_password' => 'encrypted',
            'phone_password' => 'encrypted',
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function ib1Commission()
    {
        return $this->hasMany(Ib1Commission::class);
    }
    public function ib1()
    {
        return $this->belongsTo(Ib1::class);
    }
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }
    public function BonusTransaction()
    {
        return $this->hasMany(BonusTransaction::class);
    }
    public function relationshipManager()
    {
        return $this->belongsTo(RelationshipManager::class);
    }
    public function totalBalance()
    {
        return $this->hasMany(TotalBalance::class);
    }

    public function tradeDeposits()
    {
        return $this->hasMany(TradeDeposit::class);
    }

    public function tradeWithdrawals()
    {
        return $this->hasMany(TradeWithdrawals::class);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    public function getTotalBonusDepositAttribute()
    {
        // Sum all bonus amounts where 'admin_remark' is NOT 'Credit' and NOT '10x Trader Leverage'
        $bonusDeposit = $this->BonusTransaction
            ? $this->BonusTransaction
            ->filter(function ($transaction) {
                return ($transaction->admin_remark !== 'Credit' && $transaction->admin_remark !== '10x Trader Leverage' && $transaction->admin_remark !== 'Promo Bonus' && $transaction->admin_remark !== 'Promo Deduction' && $transaction->admin_remark !== 'Promo Addition');
            })
            ->sum(function ($transaction) {
                return (float) $transaction->bonus_amount; // Cast to float to avoid string issues
            })
            : 0;

        return $bonusDeposit;
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class, 'account_code', 'code');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Update the deal sync tracking for this account.
     */
    public function updateDealSyncStatus(Carbon $from = null, Carbon $to = null, bool $isComplete = true): void
    {
        $updateData = [
            'deals_last_fetch_at' => now(),
            'deals_sync_complete' => $isComplete
        ];

        if ($from) {
            $updateData['deals_synced_from'] = $from;
        }

        if ($to) {
            $updateData['deals_synced_to'] = $to;
        }

        $this->update($updateData);
    }

    /**
     * Check if deal data is fresh enough for trade sync.
     */
    public function isDealDataFresh(Carbon $requiredUpTo = null): bool
    {
        $requiredUpTo = $requiredUpTo ?? now()->subHours(1);

        // Check if we have recent deals and complete sync
        if (!$this->deals_sync_complete || !$this->deals_synced_to) {
            return false;
        }

        return Carbon::parse($this->deals_synced_to)->gte($requiredUpTo);
    }

    /**
     * Get the range of time we need to fetch deals for.
     */
    public function getRequiredDealSyncRange(Carbon $requiredUpTo = null): array
    {
        $requiredUpTo = $requiredUpTo ?? now();

        // If we have no deal data, start from 30 days ago
        $from = $this->deals_synced_to
            ? Carbon::parse($this->deals_synced_to)->addSecond()
            : now()->subDays(30);

        return [
            'from' => $from,
            'to' => $requiredUpTo,
            'needs_sync' => $from->lt($requiredUpTo)
        ];
    }
}
