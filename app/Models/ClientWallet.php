<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class ClientWallet extends Model
{
    use HasFactory,HasUuids,SoftDeletes;
    protected $table = 'client_wallets';
    protected $guarded = [];

    // Hide encrypted fields from JSON serialization to prevent MAC errors
    protected $hidden = [
        'wallet_address',
    ];

    public function casts(){
        return [
            'wallet_address' => 'encrypted',
        ];
    }

    //  public function setWalletAddressAttribute($value)
    //  {
    //      $this->attributes['wallet_address'] = Crypt::encryptString($value);
    //  }

    //  public function getWalletAddressAttribute($value)
    //  {
    //      return Crypt::decryptString($value);
    //  }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


}
