<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncMonitorController extends Controller
{
    /**
     * Show the sync monitoring dashboard
     */
    public function dashboard()
    {
        // Get all sync data
        $syncData = $this->getAllSyncData();

        return view('admin.sync-monitor.dashboard', compact('syncData'));
    }

    /**
     * Get sync data via AJAX for real-time updates
     */
    public function getSyncData()
    {
        return response()->json($this->getAllSyncData());
    }

    /**
     * Get detailed account sync information
     */
    public function accountDetails($accountId)
    {
        $account = Account::with(['trades' => function ($query) {
            $query->latest()->limit(10);
        }])->findOrFail($accountId);

        $syncInfo = $this->getAccountSyncInfo($account);

        return response()->json([
            'account' => $account,
            'sync_info' => $syncInfo,
            'cache_status' => $this->getAccountCacheStatus($accountId),
            'recent_trades' => $account->trades
        ]);
    }

    /**
     * Force clear account sync cache
     */
    public function clearAccountCache($accountId)
    {
        $account = Account::findOrFail($accountId);

        // Clear all types of cache for this account
        Cache::forget("account_sync_in_progress:{$accountId}");
        Cache::forget("demo_account_sync_in_progress:{$accountId}");

        // Reset sync status if stuck
        if ($account->sync_status === 'pending') {
            $account->update([
                'sync_status' => 'needs_retry',
                'sync_error' => 'Cache manually cleared by admin'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Cache cleared for account {$account->code}"
        ]);
    }

    /**
     * Unflag a problematic account
     */
    public function unflagAccount($accountId)
    {
        $account = Account::findOrFail($accountId);

        if ($account->sync_status !== 'flagged') {
            return response()->json([
                'success' => false,
                'message' => "Account {$account->code} is not flagged"
            ]);
        }

        $account->update([
            'sync_status' => 'needs_retry',
            'sync_error' => 'Manually unflagged by admin',
            'sync_flag_reason' => null,
            'sync_flagged_at' => null,
            'sync_stuck_count' => 0
        ]);

        // Clear cache markers
        Cache::forget("account_sync_in_progress:{$accountId}");
        Cache::forget("demo_account_sync_in_progress:{$accountId}");

        return response()->json([
            'success' => true,
            'message' => "Account {$account->code} has been unflagged"
        ]);
    }

    /**
     * Get comprehensive sync data for all sync types
     */
    private function getAllSyncData()
    {
        return [
            'priority_sync' => $this->getPrioritySyncData(),
            'demo_sync' => $this->getDemoSyncData(),
            'optimized_sync' => $this->getOptimizedSyncData(),
            'queue_status' => $this->getQueueStatus(),
            'system_health' => $this->getSystemHealth(),
            'flagged_accounts' => $this->getFlaggedAccounts(),
            'stuck_accounts' => $this->getStuckAccounts(),
            'cache_stats' => $this->getCacheStats()
        ];
    }

    /**
     * Get priority sync statistics
     */
    private function getPrioritySyncData()
    {
        $totalAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->count();

        $retryAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->where('sync_status', 'needs_retry')
            ->count();

        $pendingAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->where('sync_status', 'pending')
            ->count();

        $syncedToday = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', false)
            ->where('last_sync_attempt_at', '>=', now()->subDay())
            ->count();

        return [
            'total_accounts' => $totalAccounts,
            'retry_accounts' => $retryAccounts,
            'pending_accounts' => $pendingAccounts,
            'synced_today' => $syncedToday,
            'sync_percentage' => $totalAccounts > 0 ? round(($syncedToday / $totalAccounts) * 100, 1) : 0,
            'queue_jobs' => $this->getQueueJobsCount('priority-sync-trades')
        ];
    }

    /**
     * Get demo sync statistics
     */
    private function getDemoSyncData()
    {
        $totalDemoAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', true)
            ->count();

        $retryDemoAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', true)
            ->where('sync_status', 'needs_retry')
            ->count();

        $syncedTodayDemo = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('demo', true)
            ->where('last_sync_attempt_at', '>=', now()->subDay())
            ->count();

        return [
            'total_accounts' => $totalDemoAccounts,
            'retry_accounts' => $retryDemoAccounts,
            'synced_today' => $syncedTodayDemo,
            'sync_percentage' => $totalDemoAccounts > 0 ? round(($syncedTodayDemo / $totalDemoAccounts) * 100, 1) : 0,
            'queue_jobs' => $this->getQueueJobsCount('demo-sync-trades')
        ];
    }

    /**
     * Get optimized sync statistics
     */
    private function getOptimizedSyncData()
    {
        return [
            'queue_jobs' => $this->getQueueJobsCount('optimized-sync-trades'),
            'high_volume_jobs' => $this->getQueueJobsCount('high-volume-sync')
        ];
    }

    /**
     * Get queue status for all queues
     */
    private function getQueueStatus()
    {
        return [
            'priority_sync' => $this->getQueueJobsCount('priority-sync-trades'),
            'demo_sync' => $this->getQueueJobsCount('demo-sync-trades'),
            'optimized_sync' => $this->getQueueJobsCount('optimized-sync-trades'),
            'high_volume_sync' => $this->getQueueJobsCount('high-volume-sync'),
            'balance_sync' => $this->getQueueJobsCount('balance-sync'),
            'default' => $this->getQueueJobsCount('default')
        ];
    }

    /**
     * Get system health indicators
     */
    private function getSystemHealth()
    {
        $totalJobs = array_sum($this->getQueueStatus());
        $flaggedCount = Account::where('sync_status', 'flagged')->count();
        $stuckCount = $this->getStuckAccountsCount();

        // Calculate health score (0-100)
        $healthScore = 100;
        if ($totalJobs > 1000) $healthScore -= 20;
        if ($flaggedCount > 10) $healthScore -= 30;
        if ($stuckCount > 5) $healthScore -= 25;

        $healthScore = max(0, $healthScore);

        return [
            'score' => $healthScore,
            'status' => $this->getHealthStatus($healthScore),
            'total_queue_jobs' => $totalJobs,
            'flagged_accounts' => $flaggedCount,
            'stuck_accounts' => $stuckCount
        ];
    }

    /**
     * Get flagged accounts
     */
    private function getFlaggedAccounts()
    {
        return Account::whereNotNull('code')
            ->where('sync_status', 'flagged')
            ->select('id', 'code', 'demo', 'sync_flag_reason', 'sync_flagged_at', 'sync_stuck_count', 'sync_error')
            ->orderBy('sync_flagged_at', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Get stuck accounts
     */
    private function getStuckAccounts()
    {
        $stuckThreshold = now()->subMinutes(45);

        return Account::whereNotNull('code')
            ->where('sync_status', 'pending')
            ->where('last_sync_attempt_at', '<', $stuckThreshold)
            ->select('id', 'code', 'demo', 'last_sync_attempt_at', 'sync_error')
            ->orderBy('last_sync_attempt_at', 'asc')
            ->limit(20)
            ->get();
    }

    /**
     * Get cache statistics
     */
    private function getCacheStats()
    {
        try {
            $liveKeys = Cache::getRedis()->keys("*account_sync_in_progress:*");
            $demoKeys = Cache::getRedis()->keys("*demo_account_sync_in_progress:*");

            return [
                'live_accounts_in_progress' => count($liveKeys),
                'demo_accounts_in_progress' => count($demoKeys),
                'total_cache_markers' => count($liveKeys) + count($demoKeys)
            ];
        } catch (\Exception $e) {
            return [
                'live_accounts_in_progress' => 0,
                'demo_accounts_in_progress' => 0,
                'total_cache_markers' => 0,
                'error' => 'Could not read cache stats'
            ];
        }
    }

    /**
     * Get queue jobs count for a specific queue
     */
    private function getQueueJobsCount(string $queueName): int
    {
        try {
            $fullQueueName = config('queue.connections.redis.queue') . ':' . $queueName;
            $pendingCount = Redis::llen($fullQueueName);
            $delayedCount = Redis::zcard($fullQueueName . ':delayed');
            $reservedCount = Redis::zcard($fullQueueName . ':reserved');

            return $pendingCount + $delayedCount + $reservedCount;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get stuck accounts count
     */
    private function getStuckAccountsCount(): int
    {
        $stuckThreshold = now()->subMinutes(45);

        return Account::where('sync_status', 'pending')
            ->where('last_sync_attempt_at', '<', $stuckThreshold)
            ->count();
    }

    /**
     * Get health status text
     */
    private function getHealthStatus(int $score): string
    {
        if ($score >= 80) return 'Excellent';
        if ($score >= 60) return 'Good';
        if ($score >= 40) return 'Fair';
        if ($score >= 20) return 'Poor';
        return 'Critical';
    }

    /**
     * Get detailed sync info for a specific account
     */
    private function getAccountSyncInfo(Account $account): array
    {
        return [
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
    }

    /**
     * Get account cache status
     */
    private function getAccountCacheStatus(string $accountId): array
    {
        return [
            'live_sync_in_progress' => Cache::has("account_sync_in_progress:{$accountId}"),
            'demo_sync_in_progress' => Cache::has("demo_account_sync_in_progress:{$accountId}"),
            'cache_expiry' => $this->getCacheExpiry("account_sync_in_progress:{$accountId}")
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
