<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeWithdrawals extends Model
{
    use HasFactory;
    protected $table = 'trade_withdrawal';
    // public $timestamps = false;
    protected $fillable = [
        'email',
        'user_id',
        'account_id',
        'withdrawal_amount',
        'withdraw_type',
        'withdraw_to',
        'wallet_qr',
        'Status',
        'withdraw_date'
    ];
    
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    public function withdrawTo()
    {
        return $this->belongsTo(Account::class, 'withdraw_to', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
