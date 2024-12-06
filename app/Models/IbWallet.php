<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IbWallet extends Model
{
    use HasFactory,HasUuids;
    protected $table = 'ib_wallet';
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function ib1Commission()
    {
        return $this->belongsTo(Ib1Commission::class, 'order_id', 'order_id');
    }
}
