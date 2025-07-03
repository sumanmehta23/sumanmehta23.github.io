<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory,HasUuids, SoftDeletes;
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

    public function getTotalBonusDepositAttribute()
    {
        // Sum all bonus amounts where 'admin_remark' is NOT 'Credit' and NOT '10x Trader Leverage'
        $bonusDeposit = $this->BonusTransaction
            ? $this->BonusTransaction
                ->filter(function ($transaction) {
                    return ($transaction->admin_remark !== 'Credit' && $transaction->admin_remark !== '10x Trader Leverage' && $transaction->admin_remark !== 'Promo Bonus');
                })
                ->sum(function ($transaction) {
                    return (float) $transaction->bonus_amount; // Cast to float to avoid string issues
                })
            : 0;

        return $bonusDeposit;
    }



}
