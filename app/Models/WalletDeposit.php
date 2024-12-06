<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletDeposit extends Model
{
    use HasFactory,HasUuids,SoftDeletes;
    protected $table = 'wallet_deposit';
    protected $fillable = [
        'email',
        'deposit_amount',
        'deposit_type',
        'transaction_id',
        'Status',
        'user_id',
        'created_at',
        'updated_at'
    ];
    protected static function boot()
    {
        parent::boot();
        static::created(function ($walletDeposit) {
            Cache::forget("user:{$walletDeposit->user_id}:wallet_balance");
        });
        static::updated(function ($walletDeposit) {
            Cache::forget("user:{$walletDeposit->user_id}:wallet_balance");
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
