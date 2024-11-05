<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletDeposit extends Model
{
    use HasFactory;
    protected $table = 'wallet_deposit';
    protected $fillable = [
        'email',
        'deposit_amount',
        'deposit_type',
        'transaction_id',
        'Status',
        'created_at',
        'updated_at'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'email');
    }
}
