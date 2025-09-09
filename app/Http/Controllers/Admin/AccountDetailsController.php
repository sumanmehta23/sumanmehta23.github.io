<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\RedisCoordinatedMT5Service;
use App\MT5\MTRetCode;
use Carbon\Carbon;

class AccountDetailsController extends Controller
{
    protected $mt5Service;

    public function __construct(RedisCoordinatedMT5Service $mt5Service)
    {
        $this->mt5Service = $mt5Service;
    }

    /**
     * Show detailed account information
     */
    public function show($accountId)
    {
        $account = Account::with(['trades' => function ($query) {
            $query->latest()->limit(20);
        }])->findOrFail($accountId);

        // Get comprehensive account data
        $accountData = $this->getAccountData($account);

        return view('admin.accounts.details', compact('account', 'accountData'));
    }

    /**
     * Get current account positions from MT5 (on-demand API call)
     */
    public function getCurrentPositions($accountId)
    {
        $account = Account::findOrFail($accountId);

        if (!$account->code) {
            return response()->json([
                'success' => false,
                'message' => 'Account has no MT5 code'
            ]);
        }

        try {
            $api = $this->mt5Service->getConnection();
            if (!$api) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to establish MT5 connection'
                ]);
            }

            $totalPositions = 0;

            // Get total positions
            $error_code = $api->PositionGetTotal($account->code, $totalPositions);

            if ($error_code !== MTRetCode::MT_RET_OK) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get positions count: ' . MTRetCode::GetError($error_code)
                ]);
            }

            $positions = [];
            if ($totalPositions > 0) {
                // Get positions data
                $error_code = $api->PositionGetPage($account->code, 0, $totalPositions, $positions);

                if ($error_code !== MTRetCode::MT_RET_OK) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to get positions: ' . MTRetCode::GetError($error_code)
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'total_positions' => $totalPositions,
                'positions' => $positions,
                'retrieved_at' => now()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to get current positions for account {$account->code}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'API connection failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get recent trade statistics from MT5 (on-demand API call)
     */
    public function getRecentTradeStats($accountId)
    {
        $account = Account::findOrFail($accountId);

        if (!$account->code) {
            return response()->json([
                'success' => false,
                'message' => 'Account has no MT5 code'
            ]);
        }

        try {
            $api = $this->mt5Service->getConnection();
            if (!$api) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to establish MT5 connection'
                ]);
            }

            // Get trades from last 7 days
            $fromDate = Carbon::now()->subDays(7)->timestamp;
            $toDate = Carbon::now()->timestamp;

            $totalDeals = 0;

            // Get total deals
            $error_code = $api->DealGetTotal($account->code, $fromDate, $toDate, $totalDeals);

            if ($error_code !== MTRetCode::MT_RET_OK) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get deals count: ' . MTRetCode::GetError($error_code)
                ]);
            }

            $deals = [];
            if ($totalDeals > 0) {
                // Limit to last 50 deals for performance
                $maxDeals = min($totalDeals, 50);
                $error_code = $api->DealGetPage($account->code, $fromDate, $toDate, 0, $maxDeals, $deals);

                if ($error_code !== MTRetCode::MT_RET_OK) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to get deals: ' . MTRetCode::GetError($error_code)
                    ]);
                }
            }

            // Calculate statistics
            $stats = $this->calculateTradeStats($deals);

            return response()->json([
                'success' => true,
                'period' => '7 days',
                'total_deals' => $totalDeals,
                'retrieved_deals' => count($deals),
                'stats' => $stats,
                'retrieved_at' => now()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to get recent trade stats for account {$account->code}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'API connection failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get account balance info from MT5 (on-demand API call)
     */
    public function getCurrentBalance($accountId)
    {
        $account = Account::findOrFail($accountId);

        if (!$account->code) {
            return response()->json([
                'success' => false,
                'message' => 'Account has no MT5 code'
            ]);
        }

        try {
            $api = $this->mt5Service->getConnection();
            if (!$api) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to establish MT5 connection'
                ]);
            }

            $userInfo = null;

            // Get user information
            $error_code = $api->UserGet($account->code, $userInfo);

            if ($error_code !== MTRetCode::MT_RET_OK) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get user info: ' . MTRetCode::GetError($error_code)
                ]);
            }

            return response()->json([
                'success' => true,
                'balance' => $userInfo->Balance ?? 0,
                'equity' => $userInfo->Equity ?? 0,
                'margin' => $userInfo->Margin ?? 0,
                'margin_free' => $userInfo->MarginFree ?? 0,
                'margin_level' => $userInfo->MarginLevel ?? 0,
                'profit' => $userInfo->Profit ?? 0,
                'currency' => $userInfo->Currency ?? 'USD',
                'leverage' => $userInfo->Leverage ?? 0,
                'retrieved_at' => now()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to get current balance for account {$account->code}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'API connection failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get comprehensive account data (non-API cached data)
     */
    private function getAccountData(Account $account): array
    {
        // Get sync information
        $syncInfo = [
            'sync_status' => $account->sync_status,
            'last_sync_attempt' => $account->last_sync_attempt_at,
            'last_balance_sync' => $account->last_balance_sync_at,
            'sync_error' => $account->sync_error,
            'stuck_count' => $account->sync_stuck_count ?? 0,
            'flagged_at' => $account->sync_flagged_at,
            'flag_reason' => $account->sync_flag_reason,
            'has_balance_activity' => $account->has_balance_activity,
            'last_balance_changed' => $account->last_balance_changed_at,
            'is_demo' => $account->demo
        ];

        // Get cache status
        $cacheStatus = [
            'live_sync_in_progress' => Cache::has("account_sync_in_progress:{$account->id}"),
            'demo_sync_in_progress' => Cache::has("demo_account_sync_in_progress:{$account->id}"),
            'cache_expiry' => $this->getCacheExpiry("account_sync_in_progress:{$account->id}")
        ];

        // Get database trade statistics
        $tradeStats = [
            'total_trades' => Trade::where('account_id', $account->id)->count(),
            'trades_last_30_days' => Trade::where('account_id', $account->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'last_trade_date' => Trade::where('account_id', $account->id)
                ->latest('created_at')
                ->value('created_at'),
            'profit_last_30_days' => Trade::where('account_id', $account->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('profit'),
            'volume_last_30_days' => Trade::where('account_id', $account->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('volume')
        ];

        return [
            'sync_info' => $syncInfo,
            'cache_status' => $cacheStatus,
            'trade_stats' => $tradeStats,
            'account_info' => [
                'id' => $account->id,
                'code' => $account->code,
                'demo' => $account->demo,
                'created_at' => $account->created_at,
                'updated_at' => $account->updated_at
            ]
        ];
    }

    /**
     * Calculate trade statistics from deals array
     */
    private function calculateTradeStats(array $deals): array
    {
        if (empty($deals)) {
            return [
                'total_volume' => 0,
                'total_profit' => 0,
                'winning_trades' => 0,
                'losing_trades' => 0,
                'win_rate' => 0,
                'largest_win' => 0,
                'largest_loss' => 0,
                'average_profit' => 0
            ];
        }

        $totalVolume = 0;
        $totalProfit = 0;
        $winningTrades = 0;
        $losingTrades = 0;
        $profits = [];

        foreach ($deals as $deal) {
            $volume = $deal->Volume ?? 0;
            $profit = $deal->Profit ?? 0;

            $totalVolume += $volume;
            $totalProfit += $profit;
            $profits[] = $profit;

            if ($profit > 0) {
                $winningTrades++;
            } elseif ($profit < 0) {
                $losingTrades++;
            }
        }

        $totalTrades = $winningTrades + $losingTrades;
        $winRate = $totalTrades > 0 ? round(($winningTrades / $totalTrades) * 100, 2) : 0;
        $largestWin = count($profits) > 0 ? max($profits) : 0;
        $largestLoss = count($profits) > 0 ? min($profits) : 0;
        $averageProfit = $totalTrades > 0 ? round($totalProfit / $totalTrades, 2) : 0;

        return [
            'total_volume' => round($totalVolume, 2),
            'total_profit' => round($totalProfit, 2),
            'winning_trades' => $winningTrades,
            'losing_trades' => $losingTrades,
            'win_rate' => $winRate,
            'largest_win' => round($largestWin, 2),
            'largest_loss' => round($largestLoss, 2),
            'average_profit' => $averageProfit
        ];
    }

    /**
     * Get cache expiry time
     */
    private function getCacheExpiry(string $key): ?string
    {
        try {
            $ttl = Cache::getRedis()->ttl($key);
            if ($ttl > 0) {
                return now()->addSeconds($ttl)->format('Y-m-d H:i:s');
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
