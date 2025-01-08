<?php

namespace App\Models;

use App\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permissions extends Model
{
    use HasFactory,HasUuids;

    protected $guarded = [];

    public function page(){
        return $this->belongsTo(Page::class);
    }
    public function roles(){
        return $this->belongsToMany(Role::class);
    }
    public function permissionGroup(){
        return $this->belongsTo(PermissionGroup::class);
    }
}

