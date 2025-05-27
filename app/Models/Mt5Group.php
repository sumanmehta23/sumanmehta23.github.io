<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Mt5Group extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function accountTypes()
    {
        return $this->hasMany(AccountType::class, 'ac_type', 'mt5_group_id');
    }
}
