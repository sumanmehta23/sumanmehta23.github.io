<?php

namespace App\Models;

use App\Events\IbCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ib1 extends Model
{
    use HasFactory,HasUuids,SoftDeletes;
    protected $table = 'ib1';

    protected $guarded=[];

    protected static function booted()
    {
        parent::booted();

        // Fire IbCreated event when IB is created
        static::created(function ($ib) {
            event(new IbCreated($ib));
        });
    }
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    public function planDetails()
    {
        return $this->belongsTo(IbPlanDetails::class,'ib_plan_details_id');
    }
    public function ibWallet()
    {
        return $this->hasMany(IbWallet::class, 'user_id','user_id');
    }

}
