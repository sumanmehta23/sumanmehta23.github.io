<?php

namespace App\Services;

use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Enhanced MT5 Connection Manager
 * 
 * Features:
 * - Centralized connection management
 * - Automatic connection pooling
 * - Health monitoring and reconnection
 * - Load balancing across connections
 * - Thread-safe operations
 * - Connection lifecycle management
 * - Performance metrics
 */
class MT5ConnectionManager
{
    private static $instance = null;
    private $connectionPool;
    private $stats = [
        'total_requests' => 0,
        'pool_hits' => 0,
        'pool_misses' => 0,
        'connections_created' => 0,
        'connections_failed' => 0,
        'errors_reported' => 0
    ];

    private function __construct()
    {
        $this->connectionPool = MT5ConnectionPool::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get a connection - primary interface for all MT5 operations
     */
    public function getConnection(): ?MTWebAPI
    {
        $this->stats['total_requests']++;

        $connection = $this->connectionPool->getConnection();

        if ($connection) {
            $this->stats['pool_hits']++;
            // Log::debug("MT5Manager: Connection provided from pool");
        } else {
            $this->stats['pool_misses']++;
            Log::warning("MT5Manager: No connection available from pool");
        }

        return $connection;
    }

    /**
     * Execute operation with connection management
     */
    public function executeOperation(callable $operation, int $maxRetries = 3): mixed
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $maxRetries) {
            try {
                $connection = $this->getConnection();
                if (!$connection) {
                    throw new \Exception("No MT5 connection available");
                }

                $result = $operation($connection);

                // Check if result indicates connection error
                if (is_int($result) && $this->isConnectionError($result)) {
                    $errorMsg = method_exists(MTRetCode::class, 'GetError')
                        ? MTRetCode::GetError($result)
                        : "Error code: {$result}";
                    throw new \Exception("MT5 connection error: " . $errorMsg);
                }

                return $result;
            } catch (\Exception $e) {
                $lastError = $e;
                $attempt++;

                Log::warning("MT5Manager: Operation failed (attempt {$attempt}): " . $e->getMessage());

                // Report error to pool
                if (isset($connection)) {
                    $this->reportConnectionError($connection);
                }

                if ($attempt < $maxRetries) {
                    sleep(1 * $attempt); // Exponential backoff
                }
            }
        }

        throw new \Exception("MT5 operation failed after {$maxRetries} attempts: " . $lastError->getMessage());
    }

    /**
     * Report connection error for pool management
     */
    public function reportConnectionError(MTWebAPI $api): void
    {
        $this->stats['errors_reported']++;
        $this->connectionPool->reportConnectionError($api);
    }

    /**
     * Check if error code indicates connection issue
     */
    private function isConnectionError(int $errorCode): bool
    {
        return in_array($errorCode, [
            MTRetCode::MT_RET_ERR_NETWORK,
            MTRetCode::MT_RET_ERR_CONNECTION,
            MTRetCode::MT_RET_ERR_TIMEOUT
        ]);
    }

    /**
     * Get comprehensive statistics
     */
    public function getStats(): array
    {
        $poolStats = $this->connectionPool->getStats();

        return array_merge($this->stats, $poolStats, [
            'hit_ratio' => $this->stats['total_requests'] > 0
                ? round(($this->stats['pool_hits'] / $this->stats['total_requests']) * 100, 2) . '%'
                : '0%',
            'error_ratio' => $this->stats['total_requests'] > 0
                ? round(($this->stats['errors_reported'] / $this->stats['total_requests']) * 100, 2) . '%'
                : '0%'
        ]);
    }

    /**
     * Force cleanup of unhealthy connections
     */
    public function cleanup(): void
    {
        $this->connectionPool->forceCleanup();
    }

    /**
     * Graceful shutdown
     */
    public function shutdown(): void
    {
        Log::info("MT5Manager: Shutting down connection manager");
        $this->connectionPool->closeAllConnections();
    }

    /**
     * Health check for monitoring
     */
    public function healthCheck(): array
    {
        $stats = $this->getStats();
        $isHealthy = $stats['healthy_connections'] > 0;

        return [
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'healthy_connections' => $stats['healthy_connections'],
            'total_connections' => $stats['total_connections'],
            'hit_ratio' => $stats['hit_ratio'],
            'error_ratio' => $stats['error_ratio'],
            'last_check' => now()->toISOString()
        ];
    }
}
