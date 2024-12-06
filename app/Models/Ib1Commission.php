<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ib1Commission extends Model
{
    use HasFactory,HasUuids,SoftDeletes;
    protected $table = 'ib1_commission';
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
        public function account()
    {
        return $this->belongsTo(Account::class);
    }
    public function ibWallet()
    {
        return $this->hasOne(IbWallet::class, 'order_id', 'order_id');
    }
    
}
