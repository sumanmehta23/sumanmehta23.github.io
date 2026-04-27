<?php

namespace App\Models;

use App\Models\Trade;
use App\Enums\PlatformEnum;
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

    // Hide encrypted fields from JSON serialization to prevent MAC errors
    protected $hidden = [
        'trader_password',
        'invester_password',
        'phone_password',
    ];

    // Define platform constants (deprecated - use PlatformEnum instead)
    const PLATFORM_MT5 = 'mt5';
    const PLATFORM_X9 = 'x9';

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
        return $this->hasMany(Trade::class)->withTrashed();
    }

    public function getTotalBonusDepositAttribute()
    {
        // Sum all bonus amounts where 'admin_remark' is NOT 'Credit' and NOT '10x Trader Leverage'
        $bonusDeposit = $this->BonusTransaction
            ? $this->BonusTransaction
            ->filter(function ($transaction) {
                return ($transaction->admin_remark !== 'Credit' && $transaction->admin_remark !== '10x Trader Leverage' && $transaction->admin_remark !== 'Promo Bonus' && $transaction->admin_remark !== 'Promo Deduction' && $transaction->admin_remark !== 'Promo Addition' && $transaction->admin_remark !== 'Bonus Pay Off');
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
     *
     * Deal data is considered fresh if:
     * 1. We have recently fetched deals (within the last hour by default)
     * 2. The sync was marked as complete
     *
     * This logic is based on WHEN we last synced, not on deal coverage time.
     * This is correct because an account might not have recent trading activity,
     * but if we just synced deals, that data is still fresh.
     */
    public function isDealDataFresh(Carbon $freshnessCutoff = null): bool
    {
        $freshnessCutoff = $freshnessCutoff ?? now()->subHours(1);

        // Check if we have a recent fetch and complete sync
        if (!$this->deals_sync_complete || !$this->deals_last_fetch_at) {
            return false;
        }

        // Deal data is fresh if we fetched it recently (regardless of deal coverage time)
        return Carbon::parse($this->deals_last_fetch_at)->gte($freshnessCutoff);
    }

    /**
     * Get the range of time we need to fetch deals for.
     *
     * This determines what time range to sync deals for based on:
     * 1. If we have previous deal data, sync from the last deal time + 1 second
     * 2. If we have no deal data, sync from 30 days ago
     * 3. Always sync up to the current time to catch any new deals
     */
    public function getRequiredDealSyncRange(Carbon $syncUpTo = null): array
    {
        $syncUpTo = $syncUpTo ?? now();

        // If we have previous deal data, start from where we left off
        $from = $this->deals_synced_to
            ? Carbon::parse($this->deals_synced_to)->addSecond()
            : now()->subDays(30);

        return [
            'from' => $from,
            'to' => $syncUpTo,
            'needs_sync' => $from->lt($syncUpTo)
        ];
    }

    protected static function booted()
    {
        static::deleting(function ($account) {
            // Delete related records safely
            $account->trades()->delete();
            $account->tradeDeposits()->delete();
            $account->tradeWithdrawals()->delete();
            $account->BonusTransaction()->delete();
            $account->dailyReports()->delete();
            $account->deals()->delete();
            $account->ib1Commission()->delete();
            $account->totalBalance()->delete();
        });

        static::restoring(function ($account) {
            // Restore related records
            $account->trades()->withTrashed()->restore();
            $account->tradeDeposits()->withTrashed()->restore();
            $account->tradeWithdrawals()->withTrashed()->restore();
            $account->BonusTransaction()->withTrashed()->restore();
            $account->dailyReports()->withTrashed()->restore();
            $account->deals()->withTrashed()->restore();
            $account->totalBalance()->withTrashed()->restore();
        });
    }

    /**
     * Check if this account was created via Zapier
     * @return bool
     */
    public function isZapierAccount(): bool
    {
        return $this->created_from === 'zapier';
    }

    /**
     * Scope to filter accounts marked as not found in MT5
     */
    public function scopeNotFoundInMt5($query)
    {
        return $query->whereNotNull('deletion_type')
            ->where('account_request_status', 1)
            ->where('platform', PlatformEnum::MT5->value)
            ->where('deletion_type', 'like', '%not_found%');
    }

    /**
     * Scope to filter accounts with specific deletion type
     */
    public function scopeWithDeletionType($query, $type)
    {
        return $query->where('deletion_type', $type);
    }

}
