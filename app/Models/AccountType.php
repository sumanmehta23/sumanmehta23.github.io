<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountType extends Model
{
    use HasFactory,HasUuids;
    protected $table = 'account_types';

    protected $primaryKey = "id";
    public function mt5Group()
    {
        return $this->belongsTo(Mt5Group::class, 'mt5_group_id', 'id');
    }
    public function account()
    {
        return $this->hasMany(LiveAccount::class, 'account_type', 'id');
    }
}
