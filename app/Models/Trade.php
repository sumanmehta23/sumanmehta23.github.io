<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Trade extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'trades';

    protected $guarded = [];

    protected $casts = [
        'open_time' => 'datetime',
        'close_time' => 'datetime',
        'volume' => 'decimal:2',
        'volume_ext' => 'decimal:2',
        'open_price' => 'decimal:5',
        'close_price' => 'decimal:5',
        'profit' => 'decimal:2',
        'sl' => 'decimal:5',
        'tp' => 'decimal:5',
        'commission' => 'decimal:2',
        'swap' => 'decimal:2',
        'sell' => 'boolean',
        'invalid' => 'boolean',
        'partial' => 'boolean',
        'final_state' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the user that owns the trade through the account
     */
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            Account::class,
            'id', // Foreign key on accounts table
            'id', // Foreign key on users table
            'account_id', // Local key on trades table
            'user_id' // Local key on accounts table
        );
    }
}
