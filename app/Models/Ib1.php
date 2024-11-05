<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ib1 extends Model
{
    use HasFactory;
    protected $table = 'ib1';
    public $timestamps=false;
    protected $fillable=['uid','email','name','password','number','username','emailToken','status'];
}
