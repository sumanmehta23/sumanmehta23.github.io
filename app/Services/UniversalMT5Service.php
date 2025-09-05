<?php

namespace App\Services;

use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Services\MT5ConnectionManager;
use Illuminate\Support\Facades\Log;

/**
 * Universal MT5 Service
 * 
 * This service provides a unified interface for all MT5 operations
 * and should be used throughout the application instead of direct
 * MTWebAPI instantiation.
 * 
 * Features:
 * - Uses centralized connection management
 * - Automatic retry logic
 * - Error handling and reporting
 * - Connection health monitoring
 * - Performance tracking
 */
class UniversalMT5Service
{
    protected $connectionManager;
    protected $api;

    public function __construct()
    {
        $this->connectionManager = MT5ConnectionManager::getInstance();
    }

    /**
     * Get MT5 API connection
     */
    public function getApi(): ?MTWebAPI
    {
        if (!$this->api) {
            $this->api = $this->connectionManager->getConnection();
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
     * Dealer connect with connection management
     */
    public function dealerConnect(): int
    {
        return $this->executeOperation(function ($api) {
            $settings = settings();
            $result = $api->DealerConnect(
                $settings['mt5_server_ip'],
                $settings['mt5_server_port'],
                30,
                $settings['mt5_server_web_login'],
                $settings['mt5_server_web_password']
            );

            // DealerConnect returns MTDealerConnect object on success or MTRetCode::MT_RET_ERROR on failure
            if ($result instanceof \App\MT5\MTDealerConnect) {
                Log::debug("UniversalMT5Service: DealerConnect successful");
                return MTRetCode::MT_RET_OK;
            } else {
                // Log the specific error for debugging
                $errorMsg = method_exists('App\MT5\MTRetCode', 'GetError')
                    ? \App\MT5\MTRetCode::GetError($result)
                    : "Error code: {$result}";
                Log::error("UniversalMT5Service: DealerConnect failed - {$errorMsg}");
                return $result;
            }
        });
    }

    /**
     * Execute any MT5 operation with automatic retry and error handling
     */
    public function executeOperation(callable $operation, int $maxRetries = 3): mixed
    {
        return $this->connectionManager->executeOperation($operation, $maxRetries);
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
            Log::error("UniversalMT5: No connection available for bulk operations");
            return $results;
        }

        foreach ($operations as $key => $operation) {
            try {
                $results[$key] = $operation($api);
            } catch (\Exception $e) {
                Log::warning("UniversalMT5: Bulk operation failed for key {$key}: " . $e->getMessage());
                $this->connectionManager->reportConnectionError($api);
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
            Log::debug("UniversalMT5: Ping failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Report connection error
     */
    public function reportError(): void
    {
        if ($this->api) {
            $this->connectionManager->reportConnectionError($this->api);
            $this->api = null; // Reset to get fresh connection next time
        }
    }

    /**
     * Get connection pool statistics
     */
    public function getStats(): array
    {
        return $this->connectionManager->getStats();
    }

    /**
     * Get health status
     */
    public function getHealth(): array
    {
        return $this->connectionManager->healthCheck();
    }

    /**
     * Force cleanup of connections
     */
    public function cleanup(): void
    {
        $this->connectionManager->cleanup();
    }
}
