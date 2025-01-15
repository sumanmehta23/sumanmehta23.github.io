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

    public function user()
    {
        return $this->belongsToMany(
            User::class,
            'relationship_manager',
            'rm_id',
            'user_id'
        )
        ->withPivot('added_by');
    }
    public function hasPermission($permission)
    {
        if ($this->role == "Super Admin") {
            return true;
        }
        return $this->role->permissions->contains('name', $permission);
    }
    public function hasPermissions($permissions)
    {
        if ($this->role->name == "Super Admin") {
            return true;
        }
        // info("Permissions to check ".json_encode([$permissions]));
        return $this->role->permissions->pluck('name')->intersect($permissions)->isNotEmpty();
    }
}
