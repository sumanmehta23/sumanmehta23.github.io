<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;

/**
 * Redis-Coordinated MT5 Connection Manager
 * 
 * This service coordinates MT5 connections across processes using Redis
 * while maintaining actual socket connections per-process.
 * 
 * Strategy:
 * 1. Use Redis to track global connection count
 * 2. Use Redis to coordinate connection creation/destruction
 * 3. Maintain actual socket connections in process memory
 * 4. Share connection metadata across processes
 */
class RedisCoordinatedMT5Service
{
    private $redis;
    private $localConnections = [];
    private $processId;
    private $serverId;
    private $maxGlobalConnections = 20; // Global limit across all processes
    private $maxLocalConnections = 5;   // Local limit per process

    private const REDIS_KEY_PREFIX = 'mt5_connections';
    private const REDIS_GLOBAL_COUNT = 'mt5_connections:global_count';
    private const REDIS_PROCESS_REGISTRY = 'mt5_connections:processes';
    private const REDIS_LOCK_KEY = 'mt5_connections:lock';

    public function __construct()
    {
        $this->redis = Redis::connection();
        $this->processId = getmypid();
        $this->serverId = gethostname() . ':' . $this->processId;

        // Register this process
        $this->registerProcess();

        // Cleanup on shutdown
        register_shutdown_function([$this, 'cleanup']);
    }

    /**
     * Get an MT5 connection with Redis coordination
     */
    public function getConnection(): ?MTWebAPI
    {
        // First try local pool
        $localConnection = $this->getLocalConnection();
        if ($localConnection) {
            return $localConnection;
        }

        // Check if we can create new connection globally
        if ($this->canCreateGlobalConnection()) {
            return $this->createCoordinatedConnection();
        }

        // Wait for available connection
        return $this->waitForAvailableConnection();
    }

    /**
     * Get connection from local pool
     */
    private function getLocalConnection(): ?MTWebAPI
    {
        foreach ($this->localConnections as $connectionId => $connectionData) {
            if ($this->isConnectionHealthy($connectionId)) {
                Log::debug("RedisCoordinated: Reusing local connection {$connectionId}");
                return $connectionData['api'];
            } else {
                $this->removeLocalConnection($connectionId);
            }
        }

        return null;
    }

    /**
     * Check if we can create a new connection globally
     */
    private function canCreateGlobalConnection(): bool
    {
        // Use Redis distributed lock
        $lockAcquired = $this->redis->set(
            self::REDIS_LOCK_KEY,
            $this->serverId,
            'EX',
            10,
            'NX' // 10 second expiry, only if not exists
        );

        if (!$lockAcquired) {
            return false;
        }

        try {
            $globalCount = (int) $this->redis->get(self::REDIS_GLOBAL_COUNT) ?: 0;
            $localCount = count($this->localConnections);

            $canCreate = $globalCount < $this->maxGlobalConnections &&
                $localCount < $this->maxLocalConnections;

            return $canCreate;
        } finally {
            // Release lock
            $this->redis->del(self::REDIS_LOCK_KEY);
        }
    }

    /**
     * Create a new connection with Redis coordination
     */
    private function createCoordinatedConnection(): ?MTWebAPI
    {
        try {
            // Create actual MT5 connection
            $api = new MTWebAPI();
            $settings = settings();

            $api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
            $result = $api->Connect(
                $settings['mt5_server_ip'],
                $settings['mt5_server_port'],
                30,
                $settings['mt5_server_web_login'],
                $settings['mt5_server_web_password']
            );

            if ($result === MTRetCode::MT_RET_OK) {
                $connectionId = uniqid($this->serverId . '_');

                // Store locally
                $this->localConnections[$connectionId] = [
                    'api' => $api,
                    'created_at' => time(),
                    'last_used' => time(),
                    'health_check' => time()
                ];

                // Update Redis coordination
                $this->redis->incr(self::REDIS_GLOBAL_COUNT);
                $this->redis->hset(
                    self::REDIS_PROCESS_REGISTRY . ':' . $this->serverId,
                    $connectionId,
                    json_encode([
                        'created_at' => time(),
                        'process_id' => $this->processId,
                        'server_id' => $this->serverId
                    ])
                );

                // Set expiry for process registry
                $this->redis->expire(
                    self::REDIS_PROCESS_REGISTRY . ':' . $this->serverId,
                    3600 // 1 hour
                );

                Log::info("RedisCoordinated: Created new connection {$connectionId}");
                return $api;
            } else {
                Log::error("RedisCoordinated: Failed to create connection: " . MTRetCode::GetError($result));
                return null;
            }
        } catch (\Exception $e) {
            Log::error("RedisCoordinated: Exception creating connection: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Wait for available connection (with timeout)
     */
    private function waitForAvailableConnection(int $maxWaitSeconds = 30): ?MTWebAPI
    {
        $startTime = time();

        while ((time() - $startTime) < $maxWaitSeconds) {
            // Check if a local connection became available
            $connection = $this->getLocalConnection();
            if ($connection) {
                return $connection;
            }

            // Check if global capacity is available
            if ($this->canCreateGlobalConnection()) {
                return $this->createCoordinatedConnection();
            }

            // Short wait before retry
            usleep(100000); // 100ms
        }

        Log::warning("RedisCoordinated: Timeout waiting for available connection");
        return null;
    }

    /**
     * Check if local connection is healthy
     */
    private function isConnectionHealthy(string $connectionId): bool
    {
        if (!isset($this->localConnections[$connectionId])) {
            return false;
        }

        $connectionData = $this->localConnections[$connectionId];
        $now = time();

        // Check if health check is needed
        if (($now - $connectionData['health_check']) < 300) { // 5 minutes
            return true;
        }

        try {
            $api = $connectionData['api'];
            $result = $api->Ping();

            $isHealthy = ($result === MTRetCode::MT_RET_OK);

            $this->localConnections[$connectionId]['health_check'] = $now;

            if ($isHealthy) {
                $this->localConnections[$connectionId]['last_used'] = $now;
            }

            return $isHealthy;
        } catch (\Exception $e) {
            Log::warning("RedisCoordinated: Health check failed for {$connectionId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove local connection and update Redis
     */
    private function removeLocalConnection(string $connectionId): void
    {
        if (isset($this->localConnections[$connectionId])) {
            unset($this->localConnections[$connectionId]);

            // Update Redis
            $this->redis->decr(self::REDIS_GLOBAL_COUNT);
            $this->redis->hdel(
                self::REDIS_PROCESS_REGISTRY . ':' . $this->serverId,
                $connectionId
            );

            Log::info("RedisCoordinated: Removed connection {$connectionId}");
        }
    }

    /**
     * Register this process in Redis
     */
    private function registerProcess(): void
    {
        $this->redis->hset(
            self::REDIS_PROCESS_REGISTRY,
            $this->serverId,
            json_encode([
                'started_at' => time(),
                'process_id' => $this->processId,
                'hostname' => gethostname()
            ])
        );

        $this->redis->expire(self::REDIS_PROCESS_REGISTRY, 7200); // 2 hours
    }

    /**
     * Execute operation with coordinated connection
     */
    public function executeOperation(callable $operation, int $maxRetries = 3): mixed
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $maxRetries) {
            try {
                $connection = $this->getConnection();
                if (!$connection) {
                    throw new \Exception("No coordinated MT5 connection available");
                }

                $result = $operation($connection);

                // Check if result indicates connection error
                if (is_int($result) && in_array($result, [
                    MTRetCode::MT_RET_ERR_NETWORK,
                    MTRetCode::MT_RET_ERR_CONNECTION,
                    MTRetCode::MT_RET_ERR_TIMEOUT
                ])) {
                    throw new \Exception("MT5 connection error: " . $result);
                }

                return $result;
            } catch (\Exception $e) {
                $lastError = $e;
                $attempt++;

                Log::warning("RedisCoordinated: Operation failed (attempt {$attempt}): " . $e->getMessage());

                if ($attempt < $maxRetries) {
                    sleep(min($attempt, 5)); // Exponential backoff, max 5 seconds
                }
            }
        }

        throw new \Exception("Coordinated MT5 operation failed after {$maxRetries} attempts: " . $lastError->getMessage());
    }

    /**
     * Get global connection statistics
     */
    public function getGlobalStats(): array
    {
        $globalCount = (int) $this->redis->get(self::REDIS_GLOBAL_COUNT) ?: 0;
        $localCount = count($this->localConnections);

        $processes = $this->redis->hgetall(self::REDIS_PROCESS_REGISTRY);
        $activeProcesses = count($processes);

        return [
            'global_connections' => $globalCount,
            'local_connections' => $localCount,
            'max_global_connections' => $this->maxGlobalConnections,
            'max_local_connections' => $this->maxLocalConnections,
            'active_processes' => $activeProcesses,
            'current_process' => $this->serverId,
            'connection_utilization' => $globalCount > 0 ? ($globalCount / $this->maxGlobalConnections) * 100 : 0
        ];
    }

    /**
     * Cleanup connections on shutdown
     */
    public function cleanup(): void
    {
        // Remove all local connections
        foreach (array_keys($this->localConnections) as $connectionId) {
            $this->removeLocalConnection($connectionId);
        }

        // Remove process from registry
        $this->redis->hdel(self::REDIS_PROCESS_REGISTRY, $this->serverId);

        Log::info("RedisCoordinated: Cleaned up process {$this->serverId}");
    }

    /**
     * Force cleanup of stale processes (admin function)
     */
    public function forceCleanupStaleProcesses(): int
    {
        $processes = $this->redis->hgetall(self::REDIS_PROCESS_REGISTRY);
        $cleaned = 0;
        $now = time();

        foreach ($processes as $serverId => $processData) {
            $data = json_decode($processData, true);

            // Remove processes older than 2 hours
            if (isset($data['started_at']) && ($now - $data['started_at']) > 7200) {
                $this->redis->hdel(self::REDIS_PROCESS_REGISTRY, $serverId);
                $this->redis->del(self::REDIS_PROCESS_REGISTRY . ':' . $serverId);
                $cleaned++;
            }
        }

        return $cleaned;
    }
}
