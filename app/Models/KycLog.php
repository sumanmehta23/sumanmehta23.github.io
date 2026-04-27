<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycLog extends Model
{
    use HasFactory,HasUlids;

    protected $guarded = [];

    protected $casts=[
        'callback_payload'=>'array'
    ];

    /**
     * Get the user that owns this KYC log
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}