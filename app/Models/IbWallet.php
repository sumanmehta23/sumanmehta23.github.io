<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class IbWallet extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'ib_wallet';
    protected $guarded = [];
    protected $fillable = [
        'id',
        'user_id',
        'account_id',
        'ib1_commission_id',
        'ib_wallet',
        'ib_withdraw',
        'email',
        'code',
        'order_id',
        'remark',
        'ib_level',
        'reg_date',
        'overpayment_flag',
        'primary_wallet_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id')
            ->withTrashed();
    }

    public function ib1Commission()
    {
        return $this->belongsTo(Ib1Commission::class, 'order_id', 'order_id');
    }

    /**
     * Get the primary wallet entry this overpayment is linked to
     */
    public function primaryWallet()
    {
        return $this->belongsTo(IbWallet::class, 'primary_wallet_id', 'id');
    }
}
