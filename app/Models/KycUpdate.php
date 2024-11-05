<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycUpdate extends Model
{
    use HasFactory;
    protected $table = 'kyc_update';
    public $timestamps=false;
}
