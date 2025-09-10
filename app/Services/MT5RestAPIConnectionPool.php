<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * REST API Connection Pool for managing MT5APIRequest instances
 * 
 * This class manages a pool of authenticated MT5 REST API connections
 * to prevent overwhelming the server and improve performance through
 * connection reuse.
 */
class MT5RestAPIConnectionPool
{
    private static $instance = null;
    private $connections = [];
    private $connectionHealth = [];
    private $maxConnections = 5; // REST API connections limit
    private $healthCheckInterval = 300; // 5 minutes
    private $connectionTimeout = 30;

    private function __construct()
    {
        $this->maxConnections = config('mt5.rest_api_max_connections', 5);
        $this->healthCheckInterval = config('mt5.health_check_interval', 300);
        $this->connectionTimeout = config('mt5.rest_api_timeout', 30);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get a healthy REST API connection from the pool
     */
    public function getConnection(): ?MT5APIRequest
    {
        // First, try to get existing healthy connection
        foreach ($this->connections as $connectionId => $apiRequest) {
            if ($this->isConnectionHealthy($connectionId)) {
                // Log::debug("MT5RestPool: Reusing connection {$connectionId}");
                return $apiRequest;
            } else {
                // Remove unhealthy connection
                $this->removeConnection($connectionId);
            }
        }

        // Clean up stale connections before creating new ones
        $this->cleanupStaleConnections();

        // Check if we can create new connection within limits
        if (count($this->connections) >= $this->maxConnections) {
            Log::warning("MT5RestPool: Connection limit reached ({$this->maxConnections})");
            return null;
        }

        // Create new connection
        return $this->createNewConnection();
    }

    /**
     * Create a new authenticated REST API connection
     */
    private function createNewConnection(): ?MT5APIRequest
    {
        try {
            $settings = settings();
            $server = $settings['mt5_server_ip'] . ':' . ($settings['mt5_rest_port'] ?? '443');
            $login = $settings['mt5_server_web_login'];
            $password = $settings['mt5_server_web_password'];
            $build = $settings['mt5_build'] ?? 2025;
            $agent = $settings['mt5_agent'] ?? 'WebManager';

            $apiRequest = new MT5APIRequest();

            if (!$apiRequest->Init($server)) {
                Log::error("MT5RestPool: Failed to initialize connection to {$server}");
                return null;
            }

            if (!$apiRequest->Auth($login, $password, $build, $agent)) {
                Log::error("MT5RestPool: Authentication failed for login {$login}");
                $apiRequest->Shutdown();
                return null;
            }

            $connectionId = uniqid('mt5_rest_conn_');
            $this->connections[$connectionId] = $apiRequest;
            $this->connectionHealth[$connectionId] = [
                'created_at' => time(),
                'last_check' => time(),
                'is_healthy' => true,
                'error_count' => 0
            ];

            Log::info("MT5RestPool: Created new REST API connection {$connectionId}");
            return $apiRequest;
        } catch (\Exception $e) {
            Log::error("MT5RestPool: Exception creating connection: " . $e->getMessage());
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

        // Perform health check - try a simple API call
        try {
            $apiRequest = $this->connections[$connectionId];
            // Try ping endpoint or a simple call
            $result = $apiRequest->Get('/api/ping');

            $isHealthy = ($result !== false);

            $this->connectionHealth[$connectionId]['last_check'] = $now;
            $this->connectionHealth[$connectionId]['is_healthy'] = $isHealthy;

            if (!$isHealthy) {
                Log::warning("MT5RestPool: Connection {$connectionId} failed health check");
            }

            return $isHealthy;
        } catch (\Exception $e) {
            Log::warning("MT5RestPool: Health check exception for {$connectionId}: " . $e->getMessage());
            $this->connectionHealth[$connectionId]['last_check'] = $now;
            $this->connectionHealth[$connectionId]['is_healthy'] = false;
            return false;
        }
    }

    /**
     * Report connection error
     */
    public function reportConnectionError(MT5APIRequest $apiRequest): void
    {
        foreach ($this->connections as $connectionId => $connection) {
            if ($connection === $apiRequest) {
                $this->connectionHealth[$connectionId]['error_count']++;
                $this->connectionHealth[$connectionId]['is_healthy'] = false;

                Log::warning("MT5RestPool: Error reported for connection {$connectionId}");

                // Remove if too many errors
                if ($this->connectionHealth[$connectionId]['error_count'] > 3) {
                    $this->removeConnection($connectionId);
                }
                break;
            }
        }
    }

    /**
     * Remove connection from pool
     */
    private function removeConnection(string $connectionId): void
    {
        if (isset($this->connections[$connectionId])) {
            $this->connections[$connectionId]->Shutdown();
            unset($this->connections[$connectionId]);
            unset($this->connectionHealth[$connectionId]);
            Log::info("MT5RestPool: Removed connection {$connectionId}");
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
     * Get pool statistics
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
            'connection_utilization' => count($this->connections) / $this->maxConnections * 100
        ];
    }

    /**
     * Force cleanup for emergency situations
     */
    public function forceCleanup(): void
    {
        foreach ($this->connections as $connectionId => $apiRequest) {
            $apiRequest->Shutdown();
        }
        $this->connections = [];
        $this->connectionHealth = [];
        Log::info("MT5RestPool: Force cleanup completed");
    }
}
