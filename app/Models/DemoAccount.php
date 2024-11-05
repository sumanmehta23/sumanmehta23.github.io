<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemoAccount extends Model
{
    use HasFactory;
    protected $table = 'demoaccount';
    public $timestamps = false;
    protected $fillable = [
        'balance',
        'email',
        'name',
        'trade_id',
        'account_type',
        'leverage',
        'currency',
        'trader_pwd',
        'invester_pwd',
        'phone_pwd',
    ];
    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'account_type', 'ac_group');
    }
}
