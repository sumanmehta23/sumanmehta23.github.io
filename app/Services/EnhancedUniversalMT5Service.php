<?php

namespace App\Services;

use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Services\MT5ConnectionManager;
use App\Services\RedisCoordinatedMT5Service;
use Illuminate\Support\Facades\Log;

/**
 * Enhanced Universal MT5 Service with Redis Coordination
 * 
 * This service provides unified MT5 operations with optional Redis coordination
 * for cross-process connection sharing.
 * 
 * Features:
 * - Local connection pooling (existing)
 * - Redis-coordinated global connection management (new)
 * - Automatic fallback between modes
 * - Cross-request connection coordination
 * - Process-aware connection limits
 */
class EnhancedUniversalMT5Service
{
    protected $connectionManager;
    protected $redisCoordinator;
    protected $api;
    protected $useRedisCoordination;

    public function __construct(bool $useRedisCoordination = true)
    {
        $this->useRedisCoordination = $useRedisCoordination && config('mt5.use_redis_coordination', true);

        if ($this->useRedisCoordination) {
            try {
                $this->redisCoordinator = new RedisCoordinatedMT5Service();
                Log::debug("EnhancedUniversal: Using Redis coordination");
            } catch (\Exception $e) {
                Log::warning("EnhancedUniversal: Redis coordination failed, falling back to local: " . $e->getMessage());
                $this->useRedisCoordination = false;
            }
        }

        if (!$this->useRedisCoordination) {
            $this->connectionManager = MT5ConnectionManager::getInstance();
            Log::debug("EnhancedUniversal: Using local connection management");
        }
    }

    /**
     * Get MT5 API connection (Redis-coordinated or local)
     */
    public function getApi(): ?MTWebAPI
    {
        if (!$this->api) {
            if ($this->useRedisCoordination) {
                $this->api = $this->redisCoordinator->getConnection();
            } else {
                $this->api = $this->connectionManager->getConnection();
            }
        }
        return $this->api;
    }

    /**
     * Connect to MT5 (backwards compatibility)
     */
    public function connect(): bool
    {
        $api = $this->getApi();
        return $api !== null;
    }

    /**
     * Dealer connect with Redis coordination
     */
    public function dealerConnect(): int
    {
        return $this->executeOperation(function ($api) {
            $settings = settings();
            return $api->DealerConnect(
                $settings['mt5_server_ip'],
                $settings['mt5_server_port'],
                30,
                $settings['mt5_server_web_login'],
                $settings['mt5_server_web_password']
            );
        });
    }

    /**
     * Execute any MT5 operation with Redis coordination or local pooling
     */
    public function executeOperation(callable $operation, int $maxRetries = 3): mixed
    {
        if ($this->useRedisCoordination) {
            return $this->redisCoordinator->executeOperation($operation, $maxRetries);
        } else {
            return $this->connectionManager->executeOperation($operation, $maxRetries);
        }
    }

    /**
     * Get account information
     */
    public function getAccount(int $login): ?array
    {
        return $this->executeOperation(function ($api) use ($login) {
            $user = null;
            $result = $api->UserGet($login, $user);
            if ($result === MTRetCode::MT_RET_OK && $user) {
                return $user;
            }
            return null;
        });
    }

    /**
     * Get account balance and equity
     */
    public function getAccountBalance(int $login): ?array
    {
        return $this->executeOperation(function ($api) use ($login) {
            $account = null;
            $result = $api->UserAccountGet($login, $account);
            if ($result === MTRetCode::MT_RET_OK && $account) {
                return [
                    'balance' => $account->Balance ?? 0,
                    'equity' => $account->Equity ?? 0,
                    'margin' => $account->Margin ?? 0,
                    'margin_free' => $account->MarginFree ?? 0,
                    'profit' => $account->Profit ?? 0
                ];
            }
            return null;
        });
    }

    /**
     * Get trade history for account
     */
    public function getTradeHistory(int $login, int $from, int $to): ?array
    {
        return $this->executeOperation(function ($api) use ($login, $from, $to) {
            $trades = null;
            $result = $api->TradeGet($login, $from, $to, $trades);
            if ($result === MTRetCode::MT_RET_OK && $trades) {
                return $trades;
            }
            return [];
        });
    }

    /**
     * Get user information
     */
    public function getUserInfo(int $login): ?array
    {
        return $this->executeOperation(function ($api) use ($login) {
            $user = null;
            $result = $api->UserGet($login, $user);
            if ($result === MTRetCode::MT_RET_OK && $user) {
                return [
                    'login' => $user->Login ?? $login,
                    'name' => $user->Name ?? '',
                    'email' => $user->Email ?? '',
                    'group' => $user->Group ?? '',
                    'leverage' => $user->Leverage ?? 0,
                    'registration' => $user->Registration ?? 0,
                    'last_access' => $user->LastAccess ?? 0
                ];
            }
            return null;
        });
    }

    /**
     * Execute bulk operations efficiently
     */
    public function executeBulkOperation(array $operations): array
    {
        $results = [];
        $api = $this->getApi();

        if (!$api) {
            Log::error("EnhancedUniversal: No connection available for bulk operations");
            return $results;
        }

        foreach ($operations as $key => $operation) {
            try {
                $results[$key] = $operation($api);
            } catch (\Exception $e) {
                Log::warning("EnhancedUniversal: Bulk operation failed for key {$key}: " . $e->getMessage());
                $this->reportError();
                $results[$key] = null;
            }
        }

        return $results;
    }

    /**
     * Ping MT5 server
     */
    public function ping(): bool
    {
        try {
            return $this->executeOperation(function ($api) {
                return $api->Ping() === MTRetCode::MT_RET_OK;
            });
        } catch (\Exception $e) {
            Log::debug("EnhancedUniversal: Ping failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Report connection error
     */
    public function reportError(): void
    {
        if ($this->api) {
            if ($this->useRedisCoordination) {
                // Redis coordinator handles error reporting internally
            } else {
                $this->connectionManager->reportConnectionError($this->api);
            }
            $this->api = null; // Reset to get fresh connection next time
        }
    }

    /**
     * Get connection statistics (local or global)
     */
    public function getStats(): array
    {
        if ($this->useRedisCoordination) {
            $stats = $this->redisCoordinator->getGlobalStats();
            $stats['coordination_mode'] = 'redis';
            return $stats;
        } else {
            $stats = $this->connectionManager->getStats();
            $stats['coordination_mode'] = 'local';
            return $stats;
        }
    }

    /**
     * Get health status
     */
    public function getHealth(): array
    {
        $baseHealth = [
            'coordination_mode' => $this->useRedisCoordination ? 'redis' : 'local',
            'service_healthy' => true
        ];

        if ($this->useRedisCoordination) {
            try {
                $stats = $this->redisCoordinator->getGlobalStats();
                return array_merge($baseHealth, [
                    'global_connections' => $stats['global_connections'],
                    'local_connections' => $stats['local_connections'],
                    'active_processes' => $stats['active_processes'],
                    'utilization' => $stats['connection_utilization']
                ]);
            } catch (\Exception $e) {
                return array_merge($baseHealth, [
                    'service_healthy' => false,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            return array_merge($baseHealth, $this->connectionManager->healthCheck());
        }
    }

    /**
     * Force cleanup of connections
     */
    public function cleanup(): void
    {
        if ($this->useRedisCoordination) {
            $this->redisCoordinator->cleanup();
        } else {
            $this->connectionManager->cleanup();
        }
    }

    /**
     * Administrative function to clean stale processes (Redis mode only)
     */
    public function forceCleanupStaleProcesses(): int
    {
        if ($this->useRedisCoordination) {
            return $this->redisCoordinator->forceCleanupStaleProcesses();
        }
        return 0;
    }

    /**
     * Check if Redis coordination is active
     */
    public function isUsingRedisCoordination(): bool
    {
        return $this->useRedisCoordination;
    }

    /**
     * Switch coordination mode (for testing/debugging)
     */
    public function switchCoordinationMode(bool $useRedis): void
    {
        if ($useRedis && !$this->useRedisCoordination) {
            try {
                $this->redisCoordinator = new RedisCoordinatedMT5Service();
                $this->useRedisCoordination = true;
                $this->api = null; // Reset connection
                Log::info("EnhancedUniversal: Switched to Redis coordination");
            } catch (\Exception $e) {
                Log::error("EnhancedUniversal: Failed to switch to Redis coordination: " . $e->getMessage());
            }
        } elseif (!$useRedis && $this->useRedisCoordination) {
            $this->redisCoordinator->cleanup();
            $this->redisCoordinator = null;
            $this->connectionManager = MT5ConnectionManager::getInstance();
            $this->useRedisCoordination = false;
            $this->api = null; // Reset connection
            Log::info("EnhancedUniversal: Switched to local coordination");
        }
    }
}
