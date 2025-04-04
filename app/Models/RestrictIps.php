<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestrictIps extends Model
{
    use HasFactory,HasUuids,SoftDeletes;
    protected $table = 'restrict_ips';
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class,'email', 'email');
    }
}
