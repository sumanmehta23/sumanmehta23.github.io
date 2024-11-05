<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mt5Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'mt5_group_id',
        'mt5_group_name',
        'mt5_group_type',
        'mt5_group_desc',
        'is_active',
        'updated_by',
        'created_at',
        'updated_at'
    ];
    public function accountTypes()
    {
        return $this->hasMany(AccountType::class, 'ac_type', 'mt5_group_id');
    }
}
