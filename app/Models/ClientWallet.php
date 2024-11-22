<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientWallet extends Model
{
    use HasFactory ,HasUuids,SoftDeletes;
    protected $table = 'client_wallets';
    protected $primaryKey = 'id';
    protected $fillable = [
        'wallet_name',
        'wallet_currency',
        'wallet_network',
        'wallet_address',
        'created_by',
        'user_id',
        'client_id',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
