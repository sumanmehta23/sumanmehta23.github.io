<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\Trade;
use App\Models\Account;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Deal Analysis Service
 * 
 * Provides intelligent analysis of deal data for position reconstruction
 * and trade synchronization optimization.
 */
class DealAnalysisService
{
    /**
     * Analyze account's deal data freshness and recommend sync strategy
     */
    public function analyzeSyncStrategy(Account $account): array
    {
        $analysis = [
            'strategy' => 'order_based', // Default fallback
            'reason' => 'No deal data available',
            'last_deal_time' => null,
            'deal_count_24h' => 0,
            'deal_count_7d' => 0,
            'missing_positions' => 0,
            'incomplete_positions' => 0,
            'recommendation' => 'full_sync'
        ];

        // Get deal data statistics
        $lastDealTime = Deal::getLatestDealTime($account->id);
        $analysis['last_deal_time'] = $lastDealTime;

        if (!$lastDealTime) {
            $analysis['recommendation'] = 'deal_sync_first';
            return $analysis;
        }

        // Count recent deals
        $analysis['deal_count_24h'] = Deal::where('account_id', $account->id)
            ->where('time_done', '>=', now()->subDay())
            ->count();

        $analysis['deal_count_7d'] = Deal::where('account_id', $account->id)
            ->where('time_done', '>=', now()->subDays(7))
            ->count();

        // Analyze position completeness
        $positionAnalysis = $this->analyzePositionCompleteness($account);
        $analysis['missing_positions'] = $positionAnalysis['missing'];
        $analysis['incomplete_positions'] = $positionAnalysis['incomplete'];

        // Determine strategy based on data quality
        if ($analysis['deal_count_24h'] > 0 && $analysis['incomplete_positions'] < 5) {
            $analysis['strategy'] = 'deal_based';
            $analysis['reason'] = 'Fresh deal data available with good position coverage';
            $analysis['recommendation'] = 'incremental_sync';
        } elseif ($analysis['deal_count_7d'] > 10) {
            $analysis['strategy'] = 'hybrid';
            $analysis['reason'] = 'Some deal data available but may need order verification';
            $analysis['recommendation'] = 'hybrid_sync';
        } else {
            $analysis['strategy'] = 'order_based';
            $analysis['reason'] = 'Insufficient or stale deal data';
            $analysis['recommendation'] = 'full_order_sync';
        }

        return $analysis;
    }

    /**
     * Analyze how complete our position data is based on deals vs trades
     */
    public function analyzePositionCompleteness(Account $account): array
    {
        // Get unique positions from deals
        $dealPositions = Deal::where('account_id', $account->id)
            ->whereNotNull('position_id')
            ->where('position_id', '>', 0)
            ->distinct('position_id')
            ->pluck('position_id');

        // Get unique positions from trades
        $tradePositions = Trade::where('account_id', $account->id)
            ->whereNotNull('position_id')
            ->where('position_id', '>', 0)
            ->distinct('position_id')
            ->pluck('position_id');

        $missingInTrades = $dealPositions->diff($tradePositions)->count();
        $missingInDeals = $tradePositions->diff($dealPositions)->count();

        // Check for incomplete positions (positions that should be closed but aren't)
        $incompletePositions = 0;
        foreach ($dealPositions as $positionId) {
            if (!$this->isPositionComplete($account->id, $positionId)) {
                $incompletePositions++;
            }
        }

        return [
            'deal_positions' => $dealPositions->count(),
            'trade_positions' => $tradePositions->count(),
            'missing' => $missingInTrades,
            'orphaned' => $missingInDeals,
            'incomplete' => $incompletePositions
        ];
    }

    /**
     * Check if a position has complete deal data (both entry and exit)
     */
    protected function isPositionComplete(int $accountId, string $positionId): bool
    {
        $deals = Deal::where('account_id', $accountId)
            ->where('position_id', $positionId)
            ->get();

        if ($deals->isEmpty()) {
            return false;
        }

        // Calculate net volume to determine if position is closed
        $buyVolume = $deals->where('type', 0)->sum('volume');
        $sellVolume = $deals->where('type', 1)->sum('volume');
        $netVolume = abs($buyVolume - $sellVolume);

        // Position is complete if net volume is close to zero (closed)
        return $netVolume < 0.01;
    }

    /**
     * Reconstruct positions from deal data
     */
    public function reconstructPositionsFromDeals(Account $account, Carbon $fromTime = null): Collection
    {
        $cacheKey = "positions_from_deals:{$account->id}:" . ($fromTime ? $fromTime->timestamp : 'all');

        return Cache::remember($cacheKey, 300, function () use ($account, $fromTime) {
            $query = Deal::where('account_id', $account->id)
                ->whereNotNull('position_id')
                ->where('position_id', '>', 0);

            if ($fromTime) {
                $query->where('time_done', '>=', $fromTime);
            }

            $dealsByPosition = $query->orderBy('time_done')
                ->get()
                ->groupBy('position_id');

            $reconstructedPositions = collect();

            foreach ($dealsByPosition as $positionId => $positionDeals) {
                $position = $this->reconstructSinglePosition($account, $positionId, $positionDeals);
                if ($position) {
                    $reconstructedPositions->push($position);
                }
            }

            return $reconstructedPositions;
        });
    }

    /**
     * Reconstruct a single position from its deals
     */
    protected function reconstructSinglePosition(Account $account, string $positionId, Collection $positionDeals): ?array
    {
        if ($positionDeals->isEmpty()) {
            return null;
        }

        $sortedDeals = $positionDeals->sortBy('time_done');
        $firstDeal = $sortedDeals->first();
        $lastDeal = $sortedDeals->last();

        // Calculate position metrics
        $totalProfit = $positionDeals->sum('profit');
        $totalCommission = $positionDeals->sum('commission');
        $totalSwap = $positionDeals->sum('swap');

        // Determine position type and status
        $buyVolume = $positionDeals->where('type', 0)->sum('volume');
        $sellVolume = $positionDeals->where('type', 1)->sum('volume');
        $netVolume = $buyVolume - $sellVolume;
        $isOpen = abs($netVolume) > 0.01;

        // Determine primary direction
        $primaryType = $buyVolume >= $sellVolume ? 'buy' : 'sell';
        $totalVolume = max($buyVolume, $sellVolume);

        return [
            'account_id' => $account->id,
            'position_id' => $positionId,
            'symbol' => $firstDeal->symbol,
            'type' => $primaryType,
            'volume' => $totalVolume,
            'open_price' => $firstDeal->price,
            'close_price' => $isOpen ? null : $lastDeal->price,
            'open_time' => $firstDeal->time_done,
            'close_time' => $isOpen ? null : $lastDeal->time_done,
            'profit' => $totalProfit,
            'commission' => $totalCommission,
            'swap' => $totalSwap,
            'status' => $isOpen ? 'open' : 'closed',
            'deal_count' => $positionDeals->count(),
            'net_volume' => $netVolume,
            'buy_volume' => $buyVolume,
            'sell_volume' => $sellVolume
        ];
    }

    /**
     * Get recommended sync intervals based on account activity
     */
    public function getRecommendedSyncInterval(Account $account): int
    {
        $recentActivity = Deal::where('account_id', $account->id)
            ->where('time_done', '>=', now()->subHours(6))
            ->count();

        if ($recentActivity > 100) {
            return 5; // High activity: sync every 5 minutes
        } elseif ($recentActivity > 20) {
            return 15; // Medium activity: sync every 15 minutes
        } elseif ($recentActivity > 5) {
            return 60; // Low activity: sync every hour
        } else {
            return 240; // Very low activity: sync every 4 hours
        }
    }

    /**
     * Identify positions that need order verification
     */
    public function getPositionsNeedingVerification(Account $account): Collection
    {
        // Find positions where deal data suggests completion but trade status doesn't match
        $dealPositions = $this->reconstructPositionsFromDeals($account);
        $tradePositions = Trade::where('account_id', $account->id)->get()->keyBy('position_id');

        $needVerification = collect();

        foreach ($dealPositions as $dealPosition) {
            $tradePosition = $tradePositions->get($dealPosition['position_id']);

            if (!$tradePosition) {
                // Position exists in deals but not in trades
                $needVerification->push([
                    'position_id' => $dealPosition['position_id'],
                    'reason' => 'missing_trade_record',
                    'deal_status' => $dealPosition['status'],
                    'deal_profit' => $dealPosition['profit']
                ]);
            } elseif ($tradePosition->status !== $dealPosition['status']) {
                // Status mismatch between deals and trades
                $needVerification->push([
                    'position_id' => $dealPosition['position_id'],
                    'reason' => 'status_mismatch',
                    'deal_status' => $dealPosition['status'],
                    'trade_status' => $tradePosition->status,
                    'profit_difference' => abs($dealPosition['profit'] - $tradePosition->profit)
                ]);
            }
        }

        return $needVerification;
    }
}
