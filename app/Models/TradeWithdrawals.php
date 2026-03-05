<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TradeWithdrawals extends Model
{
    use HasFactory, HasUuids,SoftDeletes;
    protected $table = 'trade_withdrawal';
    // public $timestamps = false;
    protected $guarded = [];

    public function casts()
    {
        return [
            'approved_date' => 'datetime',
        ];
    }

    public function account()
    {
        return $this->belongsTo(Account::class)->withTrashed();
    }
    public function withdrawTo()
    {
        return $this->belongsTo(Account::class, 'withdraw_to', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function totalBalance()
    {
        return $this->belongsTo(TotalBalance::class,'code','code');
    }
    public function clientWallet()
    {
        return $this->belongsTo(ClientWallet::class,'client_wallet_id','id');
    }
}
