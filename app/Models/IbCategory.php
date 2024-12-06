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
    protected $guarded = [];

    public function plans(){
        return $this->hasMany(IbPlan::class);
    }
}
