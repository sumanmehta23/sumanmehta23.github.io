<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PermissionGroup extends Model
{
    use HasUuids;
    protected $guarded = [];
    public function permissions(){
        return $this->hasMany(Permission::class);
    }
}
