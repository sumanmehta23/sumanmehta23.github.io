<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use HasFactory;
    protected $table = 'account_types';

    protected $primaryKey = "id";
    public function mt5Group()
    {
        return $this->belongsTo(Mt5Group::class, 'ac_type', 'id');
    }

}
