<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Crypt;

class ClientWallet extends Model
{
    use HasFactory,HasUuids;
    protected $table = 'client_wallets';
    protected $guarded = [];
   

     public function setWalletAddressAttribute($value)
     {
         $this->attributes['wallet_address'] = Crypt::encryptString($value);
     }
 
     public function getWalletAddressAttribute($value)
     {
         return Crypt::decryptString($value);
     }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


}
