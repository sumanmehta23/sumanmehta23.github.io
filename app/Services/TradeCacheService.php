<?php

namespace App\Services;

use App\Models\Trade;
use App\Models\Account;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * High-Performance Trade Cache Service
 * 
 * Optimizes BatchSyncTradesJob performance through intelligent caching
 */
class TradeCacheService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_PREFIX = 'trades:';

    /**
     * Get cached existing trades for an account
     * 
     * Uses Redis with optimized data structure
     */
    public function getAccountTrades(Account $account): \Illuminate\Support\Collection
    {
        $cacheKey = self::CACHE_PREFIX . "account:{$account->id}:existing";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($account) {
            Log::debug("Cache MISS: Loading trades for account {$account->code}");

            $startTime = microtime(true);

            // Optimized query with covering index
            $trades = Trade::where('account_id', $account->id)
                ->select(['id', 'position_id', 'status', 'close_time', 'updated_at'])
                ->get()
                ->keyBy('position_id');

            $loadTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::debug("Cache LOAD: {$trades->count()} trades loaded in {$loadTime}ms for account {$account->code}");

            return $trades;
        });
    }

    /**
     * Invalidate account cache after updates
     */
    public function invalidateAccount(Account $account): void
    {
        $cacheKey = self::CACHE_PREFIX . "account:{$account->id}:existing";
        Cache::forget($cacheKey);
        Log::debug("Cache INVALIDATED for account {$account->code}");
    }

    /**
     * Warm up cache for multiple accounts (batch pre-loading)
     */
    public function warmupAccounts(array $accounts): void
    {
        $startTime = microtime(true);

        foreach ($accounts as $account) {
            $this->getAccountTrades($account);
        }

        $warmupTime = round((microtime(true) - $startTime) * 1000, 2);
        Log::info("Cache WARMUP: " . count($accounts) . " accounts pre-loaded in {$warmupTime}ms");
    }

    /**
     * Cache trade position mapping for faster lookups
     */
    public function cachePositionMapping(Account $account, array $positions): void
    {
        $cacheKey = self::CACHE_PREFIX . "account:{$account->id}:positions";

        // Store position -> trade ID mapping
        $mapping = collect($positions)->mapWithKeys(function ($position, $positionId) {
            return [$positionId => [
                'trade_count' => count($position),
                'has_open' => $position->where('status', 'open')->isNotEmpty(),
                'last_updated' => now()
            ]];
        });

        Cache::put($cacheKey, $mapping, self::CACHE_TTL);
    }

    /**
     * Get cached account statistics for performance monitoring
     */
    public function getAccountStats(Account $account): array
    {
        $cacheKey = self::CACHE_PREFIX . "account:{$account->id}:stats";

        return Cache::remember($cacheKey, self::CACHE_TTL * 24, function () use ($account) {
            return [
                'total_trades' => Trade::where('account_id', $account->id)->count(),
                'open_trades' => Trade::where('account_id', $account->id)->where('status', 'open')->count(),
                'avg_profit' => Trade::where('account_id', $account->id)->where('status', 'closed')->avg('profit'),
                'last_sync' => $account->last_balance_sync_at,
                'cached_at' => now()
            ];
        });
    }
}
