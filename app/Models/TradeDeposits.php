<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TradeDeposits extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'trade_deposit';
    public $timestamps = false;
    protected $guarded = [];

    public function liveAccount()
    {
        return $this->hasOne(LiveAccount::class, 'trade_id', 'trade_id');
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
