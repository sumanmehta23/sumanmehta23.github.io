<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletWithdraw extends Model
{
    use HasFactory,HasUuids,SoftDeletes;
    protected $table = 'wallet_withdraw';
    protected $guarded = [];
    protected static function boot()
    {
        parent::boot();
        static::created(function ($walletWithdraw) {
            Cache::forget("user:{$walletWithdraw->user_id}:wallet_balance");
        });
        static::updated(function ($walletWithdraw) {
            Cache::forget("user:{$walletWithdraw->user_id}:wallet_balance");
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function totalBalance()
    {
        return $this->belongsTo(TotalBalance::class,'email','email');
    }
    public function relationshipManager()
    {
        return $this->belongsToMany(RelationshipManager::class,'relationship_manager', 'user_id','rm_id');
    }
    public function emplist()
    {
        return $this->belongsTo(EmployeeList::class);
    }
    public function clientWallet()
    {
        return $this->belongsTo(ClientWallet::class);
    }

    public function getFilteredWithdrawalSumAttribute()
    {
        return $this->where('withdraw_type', 'Wallet Withdrawal')
                    ->where('status', 1)
                    ->sum('withdraw_amount');
    }
}
