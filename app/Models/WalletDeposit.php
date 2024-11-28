<?php

namespace App\Models;

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
        'created_at',
        'updated_at'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
