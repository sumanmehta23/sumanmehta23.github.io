<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LoginHistory extends Model
{
    use HasFactory, HasUuids;
    protected $table="login_history";
    public $timestamps=false;
    protected $guarded=[];
   
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
