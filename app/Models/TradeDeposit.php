<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TradeDeposit extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'trade_deposits';
    public $timestamps = true;
    protected $guarded = [];


    public function account()
    {
        return $this->belongsTo(Account::class);
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
        return $this->belongsTo(ClientWallet::class);
    }
}
