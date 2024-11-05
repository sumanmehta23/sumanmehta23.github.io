<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ib1Commission extends Model
{
    use HasFactory;
    protected $table = 'ib1_commission';
    protected $fillable = [
        'user_id',
        'order_id',
        'login',
        'init_volume',
        'volume',
        'time_closed'
    ];
}
