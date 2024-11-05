<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemoDeposit extends Model
{
    use HasFactory;
    protected $table = 'demo_deposit';
    public $timestamps = false;
    protected $fillable = [
        'email',
        'trade_id',
        'deposit_amount',
        'Status'
    ];
}
