<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IBPlan extends Model
{
    use HasFactory;
    protected $table = 'ib_plans';
    public function category()
    {
        return $this->hasMany(Category::class);
    }
}
