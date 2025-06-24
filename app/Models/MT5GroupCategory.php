<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class MT5GroupCategory extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'mt5_group_categories';

    public function accountTypes()
    {
        return $this->hasMany(AccountType::class, 'ac_category', 'mt5_grp_cat_id');
    }
}
