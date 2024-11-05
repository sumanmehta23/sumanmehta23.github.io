<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TotalBalance extends Model
{
    use HasFactory;
    protected $table = 'total_balance';
    public $timestamps = false;
    protected $fillable = [
        'email',
        'trade_id',
        'withdraw_amount',
        'deposit_amount',
        'status',
        'reg_date'
    ];
}
