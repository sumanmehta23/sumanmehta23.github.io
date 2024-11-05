<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeWithdrawals extends Model
{
    use HasFactory;
    protected $table = 'trade_withdrawal';
    public $timestamps = false;
    protected $fillable = [
        'email',
        'trade_id',
        'withdrawal_amount',
        'withdraw_type',
        'withdraw_to',
        'wallet_qr',
        'Status',
        'withdraw_date'
    ];
}
