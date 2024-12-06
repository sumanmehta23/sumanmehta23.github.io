<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'aspnetusers';

    protected $primaryKey = 'id';
     protected $guarded = [];

    public $timestamps = false;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function BonusTransaction()
    {
        return $this->hasMany(BonusTransaction::class);
    }
   // Has many live accounts
   public function liveAccounts()
   {
       return $this->hasMany(Account::class)->where('demo', false);
   }

   // Has many demo accounts
   public function demoAccounts()
   {
       return $this->hasMany(Account::class)->where('demo', true);
   }
   public function ib1Commissions()
    {
        return $this->hasMany(Ib1Commission::class);
    }
    public function ib()
    {
        return $this->hasOne(Ib1::class);
    }
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
    public function wallets()
    {
        return $this->hasMany(ClientWallet::class);
    }
    public function walletDeposits()
    {
        return $this->hasMany(WalletDeposit::class);
    }
    
    public function walletWithdraws()
    {
        return $this->hasMany(WalletWithdraw::class);
    }
    public function getWalletBalanceAttribute()
    {
        return Cache::remember("user:{$this->id}:wallet_balance", now()->addMinutes(10), function () {
            $totalDeposit = WalletDeposit::where('user_id', $this->id)
                ->where('status', 1)
                ->sum('deposit_amount');

            $totalWithdraw = WalletWithdraw::where('user_id', $this->id)
                ->where('status', '<>', 2)
                ->sum('withdraw_amount');

            return (float) $totalDeposit - (float) $totalWithdraw;
        });
    }
}
