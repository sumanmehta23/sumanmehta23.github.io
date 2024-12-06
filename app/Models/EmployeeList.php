<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EmployeeList extends Authenticatable
{
    use HasUuids,HasUuids ;
    protected $table = 'emplist';
    // protected $primaryKey = 'id';
    protected $guarded = [];
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
