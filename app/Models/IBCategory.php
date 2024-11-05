<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IBCategory extends Model
{
    use HasFactory;
    protected $table = 'ib_categories';

    protected $fillable = [
        "ib_cat_id",
        "ib_cat_name",
        "ib_cat_type",
        "ib_cat_desc",
        "is_active"
    ];
}
