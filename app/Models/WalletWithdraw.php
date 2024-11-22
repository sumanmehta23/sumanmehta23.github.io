<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletWithdraw extends Model
{
    use HasFactory,HasUuids,SoftDeletes;
    protected $table = 'wallet_withdraw';
    protected $fillable = [
        'email',
        'user_id',
        'withdraw_amount',
        'withdraw_type',
        'client_bank',
        'transaction_id',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
