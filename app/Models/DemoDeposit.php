<?php

namespace App\Models;

use App\Models\User;
use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DemoDeposit extends Model
{
    use HasFactory, HasUuids,SoftDeletes;
    protected $table = 'demo_deposit';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'email',
        'trade_id',
        'account_id',
        'deposit_amount',
        'Status'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
