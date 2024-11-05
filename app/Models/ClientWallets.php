<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientWallets extends Model
{
    use HasFactory;
    protected $table = 'client_wallets';
    protected $primaryKey = 'client_wallet_id';
    protected $fillable = [
        'wallet_name',
        'wallet_currency',
        'wallet_network',
        'wallet_address',
        'created_by',
        'user_id',
        'status'
    ];
}
