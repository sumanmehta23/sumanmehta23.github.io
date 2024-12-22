<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IbPlan extends Model
{
    use HasFactory,HasUuids,SoftDeletes;
    protected $table = 'ib_plans';
    public function category()
    {
        return $this->belongsTo(IbCategory::class);
    }
}
