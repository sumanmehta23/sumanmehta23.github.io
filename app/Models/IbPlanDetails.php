<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IbPlanDetails extends Model
{
    use HasFactory,HasUuids,SoftDeletes;
    protected $table = 'ib_plan_details';
    protected $guarded = [];

    public function plan()
    {
        return $this->belongsTo(IbCategory::class,'ib_category_id');
    }
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }
}
