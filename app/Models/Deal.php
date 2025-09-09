<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Deal extends Model
{
    protected $fillable = [
        'account_id',
        'deal_id',
        'order_id',
        'position_id',
        'symbol',
        'type',
        'volume',
        'price',
        'profit',
        'swap',
        'commission',
        'comment',
        'reason',
        'time_done',
        'time_msc',
        'time_setup',
        'magic',
        'external_id',
        'rate_profit',
        'rate_margin',
        'raw_data',
    ];

    protected $casts = [
        'time_done' => 'datetime',
        'time_setup' => 'datetime',
        'volume' => 'decimal:8',
        'price' => 'decimal:5',
        'profit' => 'decimal:2',
        'swap' => 'decimal:2',
        'commission' => 'decimal:2',
        'rate_profit' => 'decimal:8',
        'rate_margin' => 'decimal:8',
        'raw_data' => 'json',
    ];

    protected $dates = [
        'time_done',
        'time_setup',
    ];

    /**
     * Get the account that owns the deal.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the trade that this deal belongs to (if any).
     */
    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class, 'position_id', 'position_id')
            ->where('account_id', $this->account_id);
    }

    /**
     * Scope to get deals for a specific account.
     */
    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope to get deals since a specific time.
     */
    public function scopeSince($query, Carbon $since)
    {
        return $query->where('time_done', '>=', $since);
    }

    /**
     * Scope to get deals for a specific position.
     */
    public function scopeForPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    /**
     * Get the latest deal time for an account.
     */
    public static function getLatestDealTime($accountId): ?Carbon
    {
        $latestDeal = static::where('account_id', $accountId)
            ->orderBy('time_done', 'desc')
            ->first();

        return $latestDeal ? $latestDeal->time_done : null;
    }

    /**
     * Get deals grouped by position for an account.
     */
    public static function getByPositions($accountId, $fromTime = null)
    {
        $query = static::where('account_id', $accountId)
            ->whereNotNull('position_id')
            ->where('position_id', '>', 0);

        if ($fromTime) {
            $query->where('time_done', '>=', $fromTime);
        }

        return $query->orderBy('time_done')
            ->get()
            ->groupBy('position_id');
    }

    /**
     * Calculate total profit for a position from deals.
     */
    public static function calculatePositionProfit($accountId, $positionId): float
    {
        return static::where('account_id', $accountId)
            ->where('position_id', $positionId)
            ->sum('profit');
    }

    /**
     * Check if we have complete deal data for a position.
     */
    public static function hasCompletePositionData($accountId, $positionId): bool
    {
        $deals = static::where('account_id', $accountId)
            ->where('position_id', $positionId)
            ->get();

        // Check if we have both entry and exit deals
        $hasEntry = $deals->where('type', 0)->count() > 0; // Buy entry
        $hasExit = $deals->where('type', 1)->count() > 0;  // Sell exit

        return $hasEntry && $hasExit;
    }

    /**
     * Get the time range of deals for an account.
     */
    public static function getDealTimeRange($accountId): array
    {
        $deals = static::where('account_id', $accountId)
            ->selectRaw('MIN(time_done) as earliest, MAX(time_done) as latest, COUNT(*) as total')
            ->first();

        return [
            'earliest' => $deals->earliest ? Carbon::parse($deals->earliest) : null,
            'latest' => $deals->latest ? Carbon::parse($deals->latest) : null,
            'total' => $deals->total ?? 0
        ];
    }

    /**
     * Get the earliest deal time for an account.
     */
    public static function getEarliestDealTime($accountId): ?Carbon
    {
        $earliestDeal = static::where('account_id', $accountId)
            ->orderBy('time_done', 'asc')
            ->first();

        return $earliestDeal ? $earliestDeal->time_done : null;
    }

    /**
     * Check if we have deals for a specific time range.
     */
    public static function hasDealsInRange($accountId, Carbon $from, Carbon $to): bool
    {
        return static::where('account_id', $accountId)
            ->where('time_done', '>=', $from)
            ->where('time_done', '<=', $to)
            ->exists();
    }

    /**
     * Get deals count for a specific time range.
     */
    public static function getDealsCountInRange($accountId, Carbon $from, Carbon $to): int
    {
        return static::where('account_id', $accountId)
            ->where('time_done', '>=', $from)
            ->where('time_done', '<=', $to)
            ->count();
    }

    /**
     * Check if deal data is fresh enough for trade sync.
     * Returns true if we have deals up to at least the specified threshold.
     */
    public static function isDealDataFresh($accountId, Carbon $requiredUpTo = null): bool
    {
        $requiredUpTo = $requiredUpTo ?? now()->subHours(1); // Default: 1 hour ago

        $latestDealTime = static::getLatestDealTime($accountId);

        return $latestDealTime && $latestDealTime->gte($requiredUpTo);
    }

    /**
     * Get missing deal time ranges for an account.
     * Identifies gaps in deal data that need to be fetched.
     */
    public static function getMissingDealRanges($accountId, Carbon $from, Carbon $to): array
    {
        $deals = static::where('account_id', $accountId)
            ->where('time_done', '>=', $from)
            ->where('time_done', '<=', $to)
            ->orderBy('time_done')
            ->pluck('time_done');

        $gaps = [];
        $currentTime = $from->copy();
        $gapThreshold = 60 * 60; // 1 hour gap threshold

        foreach ($deals as $dealTime) {
            $dealCarbon = Carbon::parse($dealTime);

            // If there's a significant gap, record it
            if ($dealCarbon->diffInSeconds($currentTime) > $gapThreshold) {
                $gaps[] = [
                    'from' => $currentTime->copy(),
                    'to' => $dealCarbon->copy()->subSecond()
                ];
            }

            $currentTime = $dealCarbon->copy()->addSecond();
        }

        // Check if there's a gap at the end
        if ($currentTime->lt($to)) {
            $gaps[] = [
                'from' => $currentTime->copy(),
                'to' => $to->copy()
            ];
        }

        return $gaps;
    }
}
