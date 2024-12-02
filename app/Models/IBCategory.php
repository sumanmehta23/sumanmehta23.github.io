<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IbCategory extends Model
{
    use HasFactory, SoftDeletes, HasUuids;
    protected $table = 'ib_categories';

    protected $fillable = [
        "ib_cat_id",
        "ib_cat_name",
        "ib_cat_type",
        "ib_cat_desc",
        "is_active"
    ];
    public function plans(){
        return $this->hasMany(IbPlan::class);
    }
}
