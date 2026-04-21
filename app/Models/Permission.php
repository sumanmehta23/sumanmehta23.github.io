<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Permission extends Model
{
    use HasUuids;
    protected $guarded = [];
    public function roles(){
        return $this->belongsToMany(Role::class);
    }
    public function permissionGroup(){
        return $this->belongsTo(PermissionGroup::class);
    }
}
