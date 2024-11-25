<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory,HasUuids;
    protected $guarded = [];
    public function casts()
    {
        return [
            'trader_password' => 'encrypted',
            'invester_password' => 'encrypted',
            'phone_password' => 'encrypted',
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function ib1Commission()
    {
        return $this->hasMany(Ib1Commission::class);
    }
    public function ib1()
    {
        return $this->belongsTo(Ib1::class);
    }
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }
    public function bonusTrans()
    {
        return $this->hasMany(BonusTrans::class);
    }
    public function relationshipManager()
    {
        return $this->belongsTo(RelationshipManager::class);
    }
}
