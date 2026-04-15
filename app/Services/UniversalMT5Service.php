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
            // dump($account);
            if ($result === MTRetCode::MT_RET_OK && $account) {
                return [
                    'balance' => $account->Balance ?? 0,
                    'equity' => $account->Equity ?? 0,
                    'margin' => $account->Margin ?? 0,
                    'margin_level' => $account->MarginLevel ?? 0,
                    'margin_free' => $account->MarginFree ?? 0,
                    'profit' => $account->Profit ?? 0,
                    'credit' => $account->Credit ?? 0,
                    'total_commission' => $account->Commission ?? 0,
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
     * Execute trade balance operation
     */
    public function tradeBalance(int $login, int $type, float $amount, string $comment = '', ?int &$ticket = null, bool $marginCheck = true): int
    {
        return $this->executeOperation(function ($api) use ($login, $type, $amount, $comment, &$ticket, $marginCheck) {
            $result = $api->TradeBalance($login, $type, $amount, $comment, $ticket, $marginCheck);

            if ($result !== MTRetCode::MT_RET_OK) {
                $errorMsg = MTRetCode::GetError($result);
                Log::warning("UniversalMT5Service: TradeBalance failed for login {$login} - {$errorMsg}");
            }

            return $result;
        });
    }

    /**
     * Update user information
     */
    public function userUpdate(object $user, ?string &$result = null): int
    {
        return $this->executeOperation(function ($api) use ($user, &$result) {
            return $api->UserUpdate($user, $result);
        });
    }

    //  /**
    //  * Update user details (direct API wrapper)
    //  */
    // public function userUpdate($user, &$updated_user)
    // {
    //     return $this->executeOperation(function ($api) use ($user, &$updated_user) {
    //         return $api->UserUpdate($user, $updated_user);
    //     });
    // }

    /**
     * Get user object by login
     */
    public function userGet(int $login, ?object &$user = null): int
    {
        return $this->executeOperation(function ($api) use ($login, &$user) {
            return $api->UserGet($login, $user);
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
     * Get MT5 server common information (REST API pattern)
     *
     * Returns an array with server configuration and limits
     */
    public function getServerCommon(): ?array
    {
        try {
            $restService = new MT5RestAPIService;

            return $restService->getServerCommon();
        } catch (\Exception $e) {
            Log::error('UniversalMT5: Failed to get server common info: '.$e->getMessage());

            return null;
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
     * Get user account details (direct API wrapper)
     */
    public function userAccountGet(int $login, &$account)
    {
        return $this->executeOperation(function ($api) use ($login, &$account) {
            return $api->UserAccountGet($login, $account);
        });
    }


    /**
     * Add user to MT5 server (direct API wrapper)
     */
    public function userAdd($user, &$user_server)
    {
        return $this->executeOperation(function ($api) use ($user, &$user_server) {
            return $api->UserAdd($user, $user_server);
        });
    }


    /**
     * Get position total count (direct API wrapper)
     */
    public function positionGetTotal(int $login, &$total)
    {
        return $this->executeOperation(function ($api) use ($login, &$total) {
            return $api->PositionGetTotal($login, $total);
        });
    }

    /**
     * Create new user (direct API wrapper)
     */
    public function userCreate()
    {
        return $this->executeOperation(function ($api) {
            return $api->UserCreate();
        });
    }

    /**
     * Delete user from MT5 server (direct API wrapper)
     */
    public function userDelete(int $login)
    {
        return $this->executeOperation(function ($api) use ($login) {
            return $api->UserDelete($login);
        });
    }

    /**
     * Change user password (direct API wrapper)
     *
     * Note: MT protocol password type constants are strings (e.g. "MAIN", "INVESTOR").
     */
    public function userPasswordChange(int $login, string $password, string $passwordType)
    {
        return $this->executeOperation(function ($api) use ($login, $password, $passwordType) {
            return $api->UserPasswordChange($login, $password, $passwordType);
        });
    }

    /**
     * Get health status
     */
    public function getHealth(): array
    {
        return $this->connectionManager->healthCheck();
    }

    /**
     * Get batch account balances using native MT5 GetBatch method
     */
    public function getBatchAccountBalances(array $logins): array
    {
        if (empty($logins)) {
            return [];
        }

        Log::info("getBatchAccountBalances: Starting batch balance retrieval for " . count($logins) . " accounts");

        return $this->executeOperation(function ($api) use ($logins) {
            $results = [];

            // Try native UserGetBatch method first
            $batchResults = $this->tryUserGetBatch($api, $logins);
            if ($batchResults !== null) {
                Log::info("getBatchAccountBalances: Native UserGetBatch successful, processing results");
                return $batchResults;
            }

            // Fallback to individual calls
            Log::info("getBatchAccountBalances: Falling back to individual balance calls");
            foreach ($logins as $login) {
                $account = null;
                $result = $api->UserAccountGet($login, $account);

                if ($result === MTRetCode::MT_RET_OK && $account) {
                    $results[$login] = [
                        'balance' => $account->Balance ?? 0,
                        'equity' => $account->Equity ?? 0,
                        'margin' => $account->Margin ?? 0,
                        'margin_free' => $account->MarginFree ?? 0,
                        'profit' => $account->Profit ?? 0
                    ];
                } else {
                    Log::warning("getBatchAccountBalances: Failed to get balance for login {$login}, result: {$result}");
                    $results[$login] = null;
                }
            }

            return $results;
        });
    }

    /**
     * Try to use native UserGetBatch method
     */
    private function tryUserGetBatch($api, array $logins): ?array
    {
        try {
            Log::info("tryUserGetBatch: Attempting native batch call with " . count($logins) . " logins");
            Log::debug("tryUserGetBatch: Login array: " . implode(', ', $logins));

            // Convert array to comma-separated string as expected by MT5 protocol
            $loginsString = implode(',', $logins);

            $users = null;
            $result = $api->UserGetBatch($loginsString, $users);

            Log::info("tryUserGetBatch: UserGetBatch returned result code: {$result}");

            if ($result === MTRetCode::MT_RET_OK) {
                Log::info("tryUserGetBatch: Success! Processing batch response");
                Log::debug("tryUserGetBatch: Raw response type: " . gettype($users));

                if (is_array($users)) {
                    Log::debug("tryUserGetBatch: Response is array with " . count($users) . " elements");
                } else if (is_object($users)) {
                    Log::debug("tryUserGetBatch: Response is object of class: " . get_class($users));
                    Log::debug("tryUserGetBatch: Object properties: " . json_encode(get_object_vars($users)));
                } else {
                    Log::debug("tryUserGetBatch: Response content: " . json_encode($users));
                }

                return $this->parseBatchResponse($users, $logins);
            } else {
                $errorMsg = MTRetCode::GetError($result);
                Log::warning("tryUserGetBatch: UserGetBatch failed with code {$result}: {$errorMsg}");
                return null;
            }
        } catch (\Exception $e) {
            Log::error("tryUserGetBatch: Exception occurred: " . $e->getMessage());
            Log::error("tryUserGetBatch: Stack trace: " . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Parse the batch response from UserGetBatch
     */
    private function parseBatchResponse($users, array $requestedLogins): array
    {
        $results = [];

        Log::info("parseBatchResponse: Starting to parse batch response");
        Log::debug("parseBatchResponse: Requested logins: " . implode(', ', $requestedLogins));

        if (is_array($users)) {
            Log::info("parseBatchResponse: Processing array response with " . count($users) . " users");

            foreach ($users as $index => $user) {
                Log::debug("parseBatchResponse: Processing user at index {$index}");

                if (is_object($user)) {
                    $login = $user->Login ?? null;
                    Log::debug("parseBatchResponse: User object login: {$login}");

                    if ($login && in_array($login, $requestedLogins)) {
                        $results[$login] = [
                            'balance' => $user->Balance ?? 0,
                            'equity' => $user->Equity ?? 0,
                            'margin' => $user->Margin ?? 0,
                            'margin_free' => $user->MarginFree ?? 0,
                            'profit' => $user->Profit ?? 0
                        ];
                        Log::debug("parseBatchResponse: Added balance data for login {$login}");
                    }
                } else {
                    Log::warning("parseBatchResponse: User at index {$index} is not an object: " . gettype($user));
                }
            }
        } else if (is_object($users)) {
            Log::info("parseBatchResponse: Processing single object response");
            $login = $users->Login ?? null;

            if ($login && in_array($login, $requestedLogins)) {
                $results[$login] = [
                    'balance' => $users->Balance ?? 0,
                    'equity' => $users->Equity ?? 0,
                    'margin' => $users->Margin ?? 0,
                    'margin_free' => $users->MarginFree ?? 0,
                    'profit' => $users->Profit ?? 0
                ];
                Log::debug("parseBatchResponse: Added balance data for single login {$login}");
            }
        } else {
            Log::warning("parseBatchResponse: Unexpected response format: " . gettype($users));
        }

        $foundCount = count($results);
        $requestedCount = count($requestedLogins);
        Log::info("parseBatchResponse: Successfully parsed {$foundCount}/{$requestedCount} user balances");

        // Fill in null for missing logins
        foreach ($requestedLogins as $login) {
            if (!isset($results[$login])) {
                $results[$login] = null;
                Log::debug("parseBatchResponse: Set null for missing login {$login}");
            }
        }

        return $results;
    }

    /**
     * Force cleanup of connections
     */
    public function cleanup(): void
    {
        $this->connectionManager->cleanup();
    }

    /**
     * Get batch account balances using REST API (alternative method)
     */
    public function getBatchAccountBalancesREST(array $logins): array
    {
        if (empty($logins)) {
            return [];
        }

        Log::info("UniversalMT5Service: Using REST API for batch balance retrieval of " . count($logins) . " accounts");

        try {
            $restAPIService = new \App\Services\MT5RestAPIService();
            return $restAPIService->getBatchAccountBalances($logins);
        } catch (\Exception $e) {
            Log::error("UniversalMT5Service: REST API batch request failed - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compare performance between protocol-based and REST API approaches
     */
    public function compareAPIPerformance(array $logins): array
    {
        if (empty($logins)) {
            return [];
        }

        $results = [
            'protocol_based' => [],
            'rest_api' => [],
            'comparison' => []
        ];

        // Test protocol-based approach
        Log::info("Testing protocol-based API...");
        $startTime = microtime(true);
        $protocolResults = $this->getBatchAccountBalances($logins);
        $endTime = microtime(true);
        $protocolDuration = round(($endTime - $startTime) * 1000, 2);

        $results['protocol_based'] = [
            'duration_ms' => $protocolDuration,
            'success_count' => count(array_filter($protocolResults)),
            'total_accounts' => count($logins),
            'results' => $protocolResults
        ];

        // Test REST API approach
        Log::info("Testing REST API...");
        $startTime = microtime(true);
        $restResults = $this->getBatchAccountBalancesREST($logins);
        $endTime = microtime(true);
        $restDuration = round(($endTime - $startTime) * 1000, 2);

        $results['rest_api'] = [
            'duration_ms' => $restDuration,
            'success_count' => count(array_filter($restResults)),
            'total_accounts' => count($logins),
            'results' => $restResults
        ];

        // Performance comparison
        $improvement = $protocolDuration > 0 ? round((($protocolDuration - $restDuration) / $protocolDuration) * 100, 2) : 0;

        $results['comparison'] = [
            'protocol_duration_ms' => $protocolDuration,
            'rest_duration_ms' => $restDuration,
            'performance_improvement_percent' => $improvement,
            'faster_method' => $restDuration < $protocolDuration ? 'REST API' : 'Protocol-based',
            'data_consistency' => $this->compareResultConsistency($protocolResults, $restResults)
        ];

        return $results;
    }

    /**
     * Compare consistency between protocol and REST API results
     */
    private function compareResultConsistency(array $protocolResults, array $restResults): array
    {
        $matches = 0;
        $total = 0;
        $mismatches = [];

        foreach ($protocolResults as $login => $protocolData) {
            $total++;
            $restData = $restResults[$login] ?? null;

            if ($protocolData === null && $restData === null) {
                $matches++;
            } elseif ($protocolData !== null && $restData !== null) {
                $balanceDiff = abs($protocolData['balance'] - $restData['balance']);
                if ($balanceDiff < 0.01) { // Allow small floating point differences
                    $matches++;
                } else {
                    $mismatches[$login] = [
                        'protocol_balance' => $protocolData['balance'],
                        'rest_balance' => $restData['balance'],
                        'difference' => $balanceDiff
                    ];
                }
            } else {
                $mismatches[$login] = [
                    'protocol_result' => $protocolData !== null ? 'success' : 'failed',
                    'rest_result' => $restData !== null ? 'success' : 'failed'
                ];
            }
        }

        return [
            'matches' => $matches,
            'total' => $total,
            'consistency_percent' => $total > 0 ? round(($matches / $total) * 100, 2) : 0,
            'mismatches' => $mismatches
        ];
    }
}
