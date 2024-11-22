<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use HasFactory,HasUuids;
    protected $table = 'account_types';

    protected $primaryKey = "id";
    public function mt5Group()
    {
        return $this->belongsTo(Mt5Group::class, 'ac_type', 'id');
    }
    public function account()
    {
        return $this->hasMany(LiveAccount::class, 'account_type', 'ac_index');
    }
}
