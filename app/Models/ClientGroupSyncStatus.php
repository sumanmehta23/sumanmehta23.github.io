<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ClientGroupSyncStatus extends Model
{
    use HasFactory;

    protected $table = 'client_group_sync_status';

    protected $fillable = [
        'client_group_id',
        'last_sync_from',
        'last_sync_to',
        'sync_status',
        'total_trades_synced',
        'last_error',
    ];

    protected $casts = [
        'last_sync_from' => 'datetime',
        'last_sync_to' => 'datetime',
        'total_trades_synced' => 'integer',
    ];

    /**
     * Get the next sync date range
     */
    public function getNextSyncRange()
    {
        if (!$this->last_sync_to) {
            // First sync: get last 30 days
            $dateFrom = now()->subDays(30)->format('Y-m-d');
            $dateTo = now()->format('Y-m-d');
        } else {
            // Incremental sync: start from last sync end date
            $dateFrom = Carbon::parse($this->last_sync_to)->addDay()->format('Y-m-d');
            $dateTo = now()->format('Y-m-d');

            // If already caught up, sync today only
            if ($dateFrom > $dateTo) {
                $dateFrom = now()->format('Y-m-d');
                $dateTo = now()->format('Y-m-d');
            }
        }

        return [$dateFrom, $dateTo];
    }

    /**
     * Mark sync as started
     */
    public function markSyncStarted($dateFrom, $dateTo)
    {
        $this->update([
            'sync_status' => 'syncing',
            'last_sync_from' => $dateFrom,
            'last_sync_to' => $dateTo,
            'last_error' => null,
        ]);
    }

    /**
     * Mark sync as completed
     */
    public function markSyncCompleted($tradesCount)
    {
        $this->update([
            'sync_status' => 'completed',
            'total_trades_synced' => $this->total_trades_synced + $tradesCount,
            'last_error' => null,
        ]);
    }

    /**
     * Mark sync as failed
     */
    public function markSyncFailed($error)
    {
        $this->update([
            'sync_status' => 'failed',
            'last_error' => $error,
        ]);
    }
}
