<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceSnapshot extends Model
{
    protected $table = 'price_snapshots';
    protected $primaryKey = 'Symbol';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'Symbol',
        'component1',
        'component2',
        'Timestamp',
        'Price',
        'Ask',
        'Bid',
        'RateToUSD',
        'digits',
        'mul_factor',
        'contractsize',
        'minlots',
        'maxlots',
        'mmr',
        'leverage'
    ];
}
