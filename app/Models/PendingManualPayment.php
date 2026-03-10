<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PendingManualPayment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'pending_manual_payments';

    public $timestamps = true;

    protected $fillable = [
        'payment_log_id',
        'user_id',
        'account_id',
        'email',
        'code',
        'transaction_id',
        'coin',
        'coin_amount',
        'usd_value',
        'initial_requested_amount',
        'deposit_date',
        'polygon_response',
        'promocode',
        'status',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'coin_amount' => 'decimal:8',
        'usd_value' => 'decimal:2',
        'initial_requested_amount' => 'decimal:2',
        'deposit_date' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the payment log associated with this pending payment.
     */
    public function paymentLog()
    {
        return $this->belongsTo(PaymentLog::class);
    }

    /**
     * Get the user associated with this pending payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the account associated with this pending payment.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the admin who processed this payment.
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include processing payments.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope a query to only include completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include rejected payments.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
