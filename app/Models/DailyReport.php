<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'report_date' => 'date',
        'equity' => 'decimal:2',
        'balance' => 'decimal:2'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_code', 'code');
    }
}
