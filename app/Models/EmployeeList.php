<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Fortify\TwoFactorAuthenticatable;

class EmployeeList extends Authenticatable
{
    use HasApiTokens, HasUuids, HasUuids, TwoFactorAuthenticatable, SoftDeletes;
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
        if (!$this->role) {
            return false;
        }
        if ($this->role->name == "Super Admin") {
            return true;
        }
        return $this->role->permissions->contains('name', $permission);
    }
    public function hasPermissions($permissions)
    {
        if (!$this->role) {
            return false;
        }
        if ($this->role->name == "Super Admin") {
            return true;
        }
        // info("Permissions to check ".json_encode([$permissions]));
        return $this->role->permissions->pluck('name')->intersect($permissions)->isNotEmpty();
    }
    public function isAdmin()
    {
        return $this->role->name === 'Admin';
    }
    public function isSuperAdmin()
    {
        return $this->role->name === 'Super Admin';
    }
}
