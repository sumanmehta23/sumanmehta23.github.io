<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Trade extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $table = 'trades';

    protected $guarded = [];

    protected $casts = [
        'open_time' => 'datetime',
        'close_time' => 'datetime',
        'volume' => 'decimal:2',
        'volume_ext' => 'decimal:2',
        'open_price' => 'decimal:5',
        'close_price' => 'decimal:5',
        'profit' => 'decimal:2',
        'sl' => 'decimal:5',
        'tp' => 'decimal:5',
        'commission' => 'decimal:2',
        'swap' => 'decimal:2',
        'sell' => 'boolean',
        'invalid' => 'boolean',
        'partial' => 'boolean',
        'final_state' => 'boolean',
    ];
    /**
     * Boot method for model-level validation
     */
    protected static function boot()
    {
        parent::boot();

        // Validate position_id before creating/updating
        static::creating(function ($trade) {
            static::validatePositionId($trade, 'creating');
        });

        static::updating(function ($trade) {
            static::validatePositionId($trade, 'updating');
        });
    }

    /**
     * Validate position_id to prevent zero or invalid values
     */
    protected static function validatePositionId($trade, $operation)
    {
        if (empty($trade->position_id) || $trade->position_id == 0 || $trade->position_id === '0') {
            $logData = [
                'operation' => $operation,
                'account_id' => $trade->account_id,
                'account_code' => $trade->code,
                'order_id' => $trade->order_id,
                'symbol' => $trade->symbol,
                'position_id' => $trade->position_id,
                'full_payload' => $trade->toArray(),
                'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10),
                'timestamp' => now(),
                'severity' => 'CRITICAL',
                'issue_type' => 'INVALID_POSITION_ID'
            ];

            // Log critical data integrity issue
            Log::critical("INVALID POSITION_ID DETECTED: Attempt to {$operation} trade with position_id = {$trade->position_id}", $logData);

            // Log to admin activity log for dashboard visibility
            activity('trade_data_integrity')
                ->withProperties($logData)
                ->log("🚨 CRITICAL: Invalid position_id ({$trade->position_id}) detected during trade {$operation}");

            // Prevent the save operation
            throw new \InvalidArgumentException(
                "Invalid position_id: {$trade->position_id}. Trades must have valid non-zero position IDs. " .
                    "Account: {$trade->code}, Order: {$trade->order_id}, Symbol: {$trade->symbol}"
            );
        }
    }

    /**
     * Activity log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['position_id', 'account_id', 'order_id', 'symbol', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the user that owns the trade through the account
     */
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            Account::class,
            'id', // Foreign key on accounts table
            'id', // Foreign key on users table
            'account_id', // Local key on trades table
            'user_id' // Local key on accounts table
        );
    }
}
