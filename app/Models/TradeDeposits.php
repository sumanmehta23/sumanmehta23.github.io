<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeDeposits extends Model
{
    use HasFactory;
    protected $table = 'trade_deposit';
    public $timestamps = false;
    protected $fillable = [
        'email',
        'trade_id',
        'account_id',
        'deposit_amount',
        'deposit_type',
        'deposit_from',
        'deposit_proof',
        'status',
        'deposted_date'
    ];
    public function liveAccount()
    {
        return $this->hasOne(LiveAccount::class, 'trade_id', 'trade_id');
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
