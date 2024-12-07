<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalTransfer extends Model
{
    use HasFactory;
    protected $table = 'internal_transfers_list';
    public function accountTo()
    {
        return $this->belongsTo(Account::class,'it_to');
    }
    public function accountFrom()
    {
        return $this->belongsTo(Account::class,'it_from');
    }
}
