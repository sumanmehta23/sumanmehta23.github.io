<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IbWallet extends Model
{
    use HasFactory;
    protected $table = 'ib_wallet';
    protected $fillable = ['email','ib_withdraw','remark'];
}
