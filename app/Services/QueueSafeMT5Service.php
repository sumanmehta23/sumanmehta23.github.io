<?php

namespace App\Services;

use App\MT5\MTRetCode;
use App\MT5\MTWebAPI;
use Illuminate\Support\Facades\Log;

/**
 * Enhanced MT5 Service for Queue Jobs
 * 
 * This service provides additional reliability features for long-running
 * queue jobs that may experience connection state issues:
 * 
 * - Connection health verification before operations
 * - Automatic reconnection on stale connections
 * - Operation retry with fresh connections
 * - Enhanced error reporting for debugging
 */
class QueueSafeMT5Service extends UniversalMT5Service
{
    private $lastConnectionCheck = 0;
    private $connectionCheckInterval = 60; // Check every minute
    private $maxRetries = 3;

    /**
     * Execute operations with enhanced reliability for queue jobs
     */
    public function executeQueueOperation(callable $operation, int $maxRetries = 3): mixed
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $maxRetries) {
            try {
                // Verify connection health before operation
                if (!$this->verifyConnectionHealth()) {
                    Log::warning("QueueSafeMT5Service: Connection health check failed, reconnecting...");
                    $this->forceReconnect();
                }

                // Ensure dealer connection is active
                $dealerResult = $this->dealerConnect();
                if ($dealerResult !== MTRetCode::MT_RET_OK) {
                    throw new \Exception("Dealer connection failed with code: {$dealerResult}");
                }

                // Execute the operation
                $result = $this->executeOperation($operation, 1); // Single retry at service level

                return $result;
            } catch (\Exception $e) {
                $lastError = $e;
                $attempt++;

                Log::warning("QueueSafeMT5Service: Operation failed (attempt {$attempt}): " . $e->getMessage());

                if ($attempt < $maxRetries) {
                    // Force reconnection before retry
                    $this->forceReconnect();
                    sleep(1); // Brief delay before retry
                }
            }
        }

        Log::error("QueueSafeMT5Service: Operation failed after {$maxRetries} attempts: " . $lastError->getMessage());
        throw $lastError;
    }

    /**
     * Verify connection is still healthy and active
     */
    private function verifyConnectionHealth(): bool
    {
        $now = time();

        // Skip frequent checks
        if (($now - $this->lastConnectionCheck) < $this->connectionCheckInterval) {
            return true;
        }

        $this->lastConnectionCheck = $now;

        try {
            $api = $this->getApi();
            if (!$api) {
                return false;
            }

            // Test the connection with a simple ping
            $result = $api->Ping();
            return ($result === MTRetCode::MT_RET_OK);
        } catch (\Exception $e) {
            Log::warning("QueueSafeMT5Service: Health check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Force reconnection by clearing current connection
     */
    private function forceReconnect(): void
    {
        try {
            // Clear current API reference
            $this->api = null;

            // Get fresh connection from pool
            $this->api = $this->getApi();

            Log::info("QueueSafeMT5Service: Forced reconnection successful");
        } catch (\Exception $e) {
            Log::error("QueueSafeMT5Service: Forced reconnection failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get trade history with queue-safe error handling
     */
    public function getTradeHistorySafe(int $login, int $from, int $to): ?array
    {
        return $this->executeQueueOperation(function ($api) use ($login, $from, $to) {
            $deals = null;
            $total = 0;
            $result = $api->DealGetPage($login, $from, $to, $deals, $total);

            if ($result === MTRetCode::MT_RET_OK && $deals) {
                return [
                    'deals' => $deals,
                    'total' => $total
                ];
            }

            return null;
        });
    }

    /**
     * Get account balance with queue-safe error handling
     */
    public function getAccountBalanceSafe(int $login): ?array
    {
        return $this->executeQueueOperation(function ($api) use ($login) {
            $account = null;
            $result = $api->UserAccountGet($login, $account);

            if ($result === MTRetCode::MT_RET_OK && $account) {
                return [
                    'balance' => $account->Balance ?? 0,
                    'equity' => $account->Equity ?? 0,
                    'margin' => $account->Margin ?? 0,
                    'margin_free' => $account->MarginFree ?? 0,
                    'margin_level' => $account->MarginLevel ?? 0,
                    'credit' => $account->Credit ?? 0,
                ];
            }

            return null;
        });
    }
}
