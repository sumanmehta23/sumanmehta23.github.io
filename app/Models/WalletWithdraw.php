<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletWithdraw extends Model
{
    use HasFactory;
    protected $table = 'wallet_withdraw';
    protected $fillable = [
        'email',
        'withdraw_amount',
        'withdraw_type',
        'transaction_id',
        'status',
        'created_at',
        'updated_at'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'email','email');
    }
}
