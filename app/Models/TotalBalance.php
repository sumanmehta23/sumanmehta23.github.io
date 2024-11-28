<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TotalBalance extends Model
{
    use HasFactory,HasUuids,SoftDeletes;
    protected $table = 'total_balance';
    public $timestamps = false;
    protected $fillable = [
        'email',
        'user_id',
        'code',
        'withdraw_amount',
        'deposit_amount',
        'status',
        'reg_date',
        'trading_deposited',
        'trading_withdrawal',
        'refer_commission_amount',
        'deposit_type'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
