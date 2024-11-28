<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

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

    public function bonusTrans()
    {
        return $this->hasMany(BonusTrans::class);
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
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
