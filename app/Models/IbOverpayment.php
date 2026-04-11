<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class IbOverpayment extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'overpaid_amount' => 'decimal:10',
        'recovered_amount' => 'decimal:10',
        'balance_at_detection' => 'decimal:10',
        'detected_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'recovered_at' => 'datetime',
    ];

    public function ibUser()
    {
        return $this->belongsTo(User::class, 'ib_user_id');
    }

    public function duplicateCommission()
    {
        return $this->belongsTo(Ib1Commission::class, 'duplicate_commission_id');
    }

    public function duplicateWallet()
    {
        return $this->belongsTo(IbWallet::class, 'duplicate_wallet_id');
    }

    public function originalCommission()
    {
        return $this->belongsTo(Ib1Commission::class, 'original_commission_id');
    }

    public function originalWallet()
    {
        return $this->belongsTo(IbWallet::class, 'original_wallet_id');
    }

    public function remainingBalance(): float
    {
        return (float) $this->overpaid_amount - (float) $this->recovered_amount;
    }
}
