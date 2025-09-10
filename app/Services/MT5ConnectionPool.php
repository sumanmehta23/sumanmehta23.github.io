<?php

namespace App\Services;

use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * MT5 Connection Pool Manager
 * 
 * Features:
 * - Connection pooling and reuse
 * - Health checking and auto-reconnection
 * - Connection limits to prevent MT5 overload
 * - Thread-safe connection management
 * - Graceful degradation under load
 */
class MT5ConnectionPool
{
    private static $instance = null;
    private $connections = [];
    private $connectionHealth = [];
    private $maxConnections = 10; // Configurable limit
    private $healthCheckInterval = 300; // 5 minutes
    private $connectionTimeout = 30;

    private function __construct()
    {
        $this->maxConnections = config('mt5.max_connections', 10);
        $this->healthCheckInterval = config('mt5.health_check_interval', 300);
        $this->connectionTimeout = config('mt5.connection_timeout', 30);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get a healthy MT5 connection from the pool
     */
    public function getConnection(): ?MTWebAPI
    {
        // First, try to get existing healthy connection (no global limit check needed for reuse)
        foreach ($this->connections as $connectionId => $api) {
            if ($this->isConnectionHealthy($connectionId)) {
                Log::debug("MT5Pool: Reusing connection {$connectionId}");
                return $api;
            } else {
                // Remove unhealthy connection
                $this->removeConnection($connectionId);
            }
        }

        // Clean up stale connections before creating new ones
        $this->cleanupStaleConnections();

        // Check if we can create new connection within local limits
        if (count($this->connections) >= $this->maxConnections) {
            Log::warning("MT5Pool: Local connection limit reached ({$this->maxConnections})");
            return null;
        }

        // Check global connection limit before creating new connection
        if (!$this->checkGlobalConnectionLimit()) {
            Log::warning("MT5Pool: Global connection limit reached, trying cleanup...");
            $this->forceGlobalCleanup();

            // Retry once after cleanup
            if (!$this->checkGlobalConnectionLimit()) {
                Log::warning("MT5Pool: Global limit still reached after cleanup");
                return null;
            }
        }

        // Create new connection
        return $this->createNewConnection();
    }

    /**
     * Check global connection limit using Redis
     */
    private function checkGlobalConnectionLimit(): bool
    {
        if (!config('mt5.use_redis_coordination', true)) {
            return true;
        }

        try {
            $globalConnections = Cache::get('mt5_global_connections', []);
            $activeConnections = 0;
            $currentTime = time();
            $cleaned = false;

            // Count active connections and cleanup stale ones (reduced threshold to 30 seconds)
            foreach ($globalConnections as $processId => $connectionData) {
                if (($currentTime - $connectionData['last_seen']) < 30) { // 30 second threshold
                    $activeConnections += $connectionData['count'];
                } else {
                    unset($globalConnections[$processId]);
                    $cleaned = true;
                    // Log::debug("MT5Pool: Cleaned stale process {$processId}");
                }
            }

            // Update our process info
            $processId = getmypid();
            $globalConnections[$processId] = [
                'count' => count($this->connections),
                'last_seen' => $currentTime
            ];

            // Save cleaned data back to cache
            Cache::put('mt5_global_connections', $globalConnections, 120); // 2 minutes TTL

            $maxGlobal = config('mt5.max_global_connections', 20);
            Log::debug("MT5Pool: Global connections: {$activeConnections}/{$maxGlobal}" . ($cleaned ? " (after cleanup)" : ""));

            return $activeConnections < $maxGlobal;
        } catch (\Exception $e) {
            Log::warning("MT5Pool: Redis coordination failed: " . $e->getMessage());
            return true; // Allow if Redis fails
        }
    }

    /**
     * Force cleanup of global connection tracking
     */
    private function forceGlobalCleanup(): void
    {
        if (!config('mt5.use_redis_coordination', true)) {
            return;
        }

        try {
            $globalConnections = Cache::get('mt5_global_connections', []);
            $currentTime = time();
            $originalCount = count($globalConnections);

            // More aggressive cleanup - remove entries older than 15 seconds
            foreach ($globalConnections as $processId => $connectionData) {
                if (($currentTime - $connectionData['last_seen']) > 15) {
                    unset($globalConnections[$processId]);
                }
            }

            // Verify processes are actually running
            $runningProcesses = [];
            exec('ps aux | grep "horizon:work\|artisan" | grep -v grep | awk \'{print $2}\'', $runningProcesses);
            $runningProcesses = array_map('intval', $runningProcesses);

            foreach ($globalConnections as $processId => $connectionData) {
                if (!in_array($processId, $runningProcesses)) {
                    unset($globalConnections[$processId]);
                }
            }

            Cache::put('mt5_global_connections', $globalConnections, 120);

            $cleanedCount = $originalCount - count($globalConnections);
            if ($cleanedCount > 0) {
                Log::info("MT5Pool: Force cleanup removed {$cleanedCount} stale process entries");
            }
        } catch (\Exception $e) {
            Log::warning("MT5Pool: Force cleanup failed: " . $e->getMessage());
        }
    }

    /**
     * Create a new MT5 connection
     */
    private function createNewConnection(): ?MTWebAPI
    {
        try {
            $api = new MTWebAPI();
            $settings = settings();

            $api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));

            $result = $api->Connect(
                $settings['mt5_server_ip'],
                $settings['mt5_server_port'],
                $this->connectionTimeout,
                $settings['mt5_server_web_login'],
                $settings['mt5_server_web_password']
            );

            if ($result === MTRetCode::MT_RET_OK) {
                $connectionId = uniqid('mt5_conn_');
                $this->connections[$connectionId] = $api;
                $this->connectionHealth[$connectionId] = [
                    'created_at' => time(),
                    'last_check' => time(),
                    'is_healthy' => true,
                    'error_count' => 0
                ];

                // Log::info("MT5Pool: Created new connection {$connectionId}");

                // Update global connection count
                $this->updateGlobalConnectionCount();

                return $api;
            } else {
                Log::error("MT5Pool: Failed to create connection. Error code: " . MTRetCode::GetError($result));
                return null;
            }
        } catch (\Exception $e) {
            Log::error("MT5Pool: Exception creating connection: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if connection is healthy
     */
    private function isConnectionHealthy(string $connectionId): bool
    {
        if (!isset($this->connectionHealth[$connectionId])) {
            return false;
        }

        $health = $this->connectionHealth[$connectionId];
        $now = time();

        // Skip health check if recently checked
        if (($now - $health['last_check']) < $this->healthCheckInterval) {
            return $health['is_healthy'];
        }

        // Perform health check
        try {
            $api = $this->connections[$connectionId];

            // Simple health check - try to get server info
            $result = $api->Ping();

            $isHealthy = ($result === MTRetCode::MT_RET_OK);

            $this->connectionHealth[$connectionId]['last_check'] = $now;
            $this->connectionHealth[$connectionId]['is_healthy'] = $isHealthy;

            if (!$isHealthy) {
                $this->connectionHealth[$connectionId]['error_count']++;
                Log::warning("MT5Pool: Connection {$connectionId} health check failed");
            } else {
                $this->connectionHealth[$connectionId]['error_count'] = 0;
            }

            return $isHealthy;
        } catch (\Exception $e) {
            Log::error("MT5Pool: Health check exception for {$connectionId}: " . $e->getMessage());
            $this->connectionHealth[$connectionId]['is_healthy'] = false;
            $this->connectionHealth[$connectionId]['error_count']++;
            return false;
        }
    }

    /**
     * Remove unhealthy connection
     */
    private function removeConnection(string $connectionId): void
    {
        try {
            if (isset($this->connections[$connectionId])) {
                $this->connections[$connectionId]->Disconnect();
                unset($this->connections[$connectionId]);
            }
        } catch (\Exception $e) {
            Log::error("MT5Pool: Error disconnecting {$connectionId}: " . $e->getMessage());
        }

        unset($this->connectionHealth[$connectionId]);
        Log::info("MT5Pool: Removed connection {$connectionId}");

        // Update global count
        $this->updateGlobalConnectionCount();
    }

    /**
     * Update global connection count in Redis
     */
    private function updateGlobalConnectionCount(): void
    {
        if (!config('mt5.use_redis_coordination', true)) {
            return;
        }

        try {
            $globalConnections = Cache::get('mt5_global_connections', []);
            $processId = getmypid();

            $globalConnections[$processId] = [
                'count' => count($this->connections),
                'last_seen' => time()
            ];

            Cache::put('mt5_global_connections', $globalConnections, 120);
        } catch (\Exception $e) {
            Log::warning("MT5Pool: Failed to update global count: " . $e->getMessage());
        }
    }

    /**
     * Clean up stale connections
     */
    private function cleanupStaleConnections(): void
    {
        $now = time();
        $maxAge = 3600; // 1 hour

        foreach ($this->connectionHealth as $connectionId => $health) {
            $age = $now - $health['created_at'];
            $errorCount = $health['error_count'];

            // Remove old or error-prone connections
            if ($age > $maxAge || $errorCount > 5 || !$health['is_healthy']) {
                $this->removeConnection($connectionId);
            }
        }
    }

    /**
     * Public method to force cleanup of stale connections
     */
    public function forceCleanup(): void
    {
        $this->cleanupStaleConnections();
    }

    /**
     * Get pool statistics for monitoring
     */
    public function getStats(): array
    {
        $healthy = 0;
        $unhealthy = 0;

        foreach ($this->connectionHealth as $health) {
            if ($health['is_healthy']) {
                $healthy++;
            } else {
                $unhealthy++;
            }
        }

        return [
            'total_connections' => count($this->connections),
            'healthy_connections' => $healthy,
            'unhealthy_connections' => $unhealthy,
            'max_connections' => $this->maxConnections,
            'pool_utilization' => round((count($this->connections) / $this->maxConnections) * 100, 2) . '%'
        ];
    }

    /**
     * Close all connections (for graceful shutdown)
     */
    public function closeAllConnections(): void
    {
        foreach (array_keys($this->connections) as $connectionId) {
            $this->removeConnection($connectionId);
        }
        Log::info("MT5Pool: Closed all connections");
    }

    /**
     * Report connection error for adaptive management
     */
    public function reportConnectionError(MTWebAPI $api): void
    {
        // Find connection ID and increment error count
        foreach ($this->connections as $connectionId => $connection) {
            if ($connection === $api) {
                $this->connectionHealth[$connectionId]['error_count']++;
                $this->connectionHealth[$connectionId]['is_healthy'] = false;

                // Remove if too many errors
                if ($this->connectionHealth[$connectionId]['error_count'] > 3) {
                    $this->removeConnection($connectionId);
                }
                break;
            }
        }
    }

    /**
     * Reset global connection tracking (for debugging/emergency use)
     */
    public function resetGlobalTracking(): void
    {
        if (config('mt5.use_redis_coordination', true)) {
            Cache::forget('mt5_global_connections');
            Log::info("MT5Pool: Global connection tracking reset");
        }
    }

    /**
     * Get global connection tracking info for debugging
     */
    public function getGlobalStats(): array
    {
        if (!config('mt5.use_redis_coordination', true)) {
            return ['redis_coordination' => false];
        }

        try {
            $globalConnections = Cache::get('mt5_global_connections', []);
            $currentTime = time();
            $stats = [
                'total_processes' => count($globalConnections),
                'total_global_connections' => 0,
                'max_global_connections' => config('mt5.max_global_connections', 20),
                'processes' => []
            ];

            foreach ($globalConnections as $processId => $connectionData) {
                $age = $currentTime - $connectionData['last_seen'];
                $stats['total_global_connections'] += $connectionData['count'];
                $stats['processes'][] = [
                    'pid' => $processId,
                    'connections' => $connectionData['count'],
                    'last_seen' => $connectionData['last_seen'],
                    'age_seconds' => $age,
                    'is_stale' => $age > 30
                ];
            }

            return $stats;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
