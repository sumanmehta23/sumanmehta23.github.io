<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveAccount extends Model
{
    use HasFactory;
    protected $table = 'liveaccount';
    public $timestamps = false;
    protected $fillable = [
        'balance',
        'credit',
        'margin_free',
        'margin_level',
        'equity',
        'email',
        'name',
        'code',
        'account_type',
        'leverage',
        'currency',
        'trader_password',
        'invester_password',
        'phone_password',
        'ib1'
    ];
    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'account_type', 'ac_index');
    }

    public function BonusTransaction()
    {
        return $this->hasMany(BonusTransaction::class, 'code', 'code');
    }
}
