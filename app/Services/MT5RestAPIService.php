<?php

namespace App\Services;

use App\Models\Mt5User;
use App\Models\Account;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Exception;

/**
 * MT5 REST API Service
 *
 * Provides high-level interface for MT5 operations using REST API
 * with connection pooling for improved performance and reliability.
 */
class MT5RestAPIService
{
    private $settings;
    private $connectionPool;

    public function __construct()
    {
        $this->settings = settings();
        $this->connectionPool = MT5RestAPIConnectionPool::getInstance();
    }

    /**
     * Get multiple user balances using MT5 REST API batch endpoint with fallback to individual calls
     *
     * @param array $logins Array of MT5 login IDs
     * @return array Array of user balance data indexed by login
     */
    public function getBatchBalances(array $logins): array
    {
        if (empty($logins)) {
            return [];
        }

        // Log::info('MT5RestAPI: Starting batch balance sync', [
        //     'login_count' => count($logins),
        //     'logins' => $logins
        // ]);

        // Try batch REST API endpoint first
        $balances = $this->getBatchBalancesViaRestAPI($logins);

        // If batch API failed or returned no results, fall back to individual calls
        if (empty($balances)) {
            Log::info('MT5RestAPI: Batch REST API failed, falling back to individual calls');
            $balances = $this->getBatchBalancesViaIndividualCalls($logins);
        }

        return $balances;
    }

    /**
     * Get batch balances using the /api/user/get_batch REST API endpoint
     */
    private function getBatchBalancesViaRestAPI(array $logins): array
    {
        $apiRequest = $this->connectionPool->getConnection();
        if (!$apiRequest) {
            Log::error('MT5RestAPI: Failed to get connection from pool');
            return [];
        }

        try {
            // Convert logins to comma-separated string as per MT5 REST API documentation
            $loginString = implode(',', array_map('intval', $logins));

            // Log::info('MT5RestAPI: Making batch request', [
            //     'endpoint' => '/api/user/account/get_batch',
            //     'login_string' => $loginString,
            //     'login_count' => count($logins)
            // ]);

            // Make batch request using GET with query parameters
            $result = $apiRequest->Get('/api/user/account/get_batch?login=' . urlencode($loginString));

            if ($result === false) {
                Log::warning('MT5RestAPI: Batch balance request failed');
                $this->connectionPool->reportConnectionError($apiRequest);
                return [];
            }

            return $this->processBatchUsersResponse($result, $logins);
        } catch (Exception $e) {
            Log::error('MT5RestAPI: Exception in getBatchBalancesViaRestAPI', [
                'error' => $e->getMessage(),
                'logins' => $logins
            ]);
            $this->connectionPool->reportConnectionError($apiRequest);
            return [];
        }
    }

    /**
     * Get batch balances using individual REST API calls as fallback
     */
    private function getBatchBalancesViaIndividualCalls(array $logins): array
    {
        Log::info('MT5RestAPI: Using individual REST API calls for batch balances', [
            'login_count' => count($logins)
        ]);

        $balances = [];
        $errors = 0;

        foreach ($logins as $login) {
            $userBalance = $this->getSingleUserBalance($login);
            if ($userBalance !== null) {
                $balances[(string)$login] = $userBalance;
            } else {
                $errors++;
            }
        }

        Log::info('MT5RestAPI: Individual balance operation completed', [
            'total_requests' => count($logins),
            'successful' => count($balances),
            'errors' => $errors,
            'success_rate' => count($logins) > 0 ? round((count($balances) / count($logins)) * 100, 2) . '%' : '0%'
        ]);

        return $balances;
    }

    /**
     * Process the response from MT5 protocol batch API
     */
    private function processProtocolBatchResponse($users, array $requestedLogins): array
    {
        if (!is_array($users)) {
            Log::warning('MT5RestAPI: Invalid protocol batch response format', ['users' => $users]);
            return [];
        }

        $balances = [];
        $foundLogins = [];

        foreach ($users as $user) {
            if (isset($user->Login)) {
                $login = (string)$user->Login;
                $foundLogins[] = $login;

                $balances[$login] = [
                    'login' => $login,
                    'balance' => floatval($user->Balance ?? 0),
                    'credit' => floatval($user->Credit ?? 0),
                    'margin' => floatval($user->Margin ?? 0),
                    'margin_free' => floatval($user->MarginFree ?? 0),
                    'margin_level' => floatval($user->MarginLevel ?? 0),
                    'equity' => floatval($user->Equity ?? 0),
                ];
            }
        }

        // Log results
        $missingLogins = array_diff(array_map('strval', $requestedLogins), $foundLogins);
        if (!empty($missingLogins)) {
            Log::info('MT5RestAPI: Some users not found in protocol batch response', [
                'requested' => count($requestedLogins),
                'found' => count($foundLogins),
                'missing_logins' => $missingLogins
            ]);
        }

        Log::info('MT5RestAPI: Protocol batch processing completed', [
            'requested_count' => count($requestedLogins),
            'found_count' => count($balances),
            'success_rate' => count($requestedLogins) > 0 ? round((count($balances) / count($requestedLogins)) * 100, 2) . '%' : '0%'
        ]);

        return $balances;
    }

    /**
     * Get individual user balance via REST API
     *
     * @param int|string $login MT5 login ID
     * @return array|null User balance data or null on error
     */
    private function getSingleUserBalance($login): ?array
    {
        $apiRequest = $this->connectionPool->getConnection();
        if (!$apiRequest) {
            Log::error('MT5RestAPI: Failed to get connection from pool');
            return null;
        }

        try {
            // Make individual user request
            $result = $apiRequest->Post('/api/user/account/get', json_encode(['login' => (int)$login]));

            if ($result === false) {
                Log::warning('MT5RestAPI: User balance request failed', ['login' => $login]);
                $this->connectionPool->reportConnectionError($apiRequest);
                return null;
            }

            return $this->processSingleUserResponse($result, $login);
        } catch (Exception $e) {
            Log::error('MT5RestAPI: Exception in getSingleUserBalance', [
                'error' => $e->getMessage(),
                'login' => $login
            ]);
            $this->connectionPool->reportConnectionError($apiRequest);
            return null;
        }
    }

    /**
     * Process single user response from REST API
     */
    private function processSingleUserResponse($response, $login): ?array
    {
        // Decode JSON if needed
        if (is_string($response)) {
            $response = json_decode($response, true);
            if (!$response) {
                Log::warning('MT5RestAPI: Invalid JSON response for user', [
                    'login' => $login,
                    'raw_response' => substr($response, 0, 500)
                ]);
                return null;
            }
        }

        // Check for API error
        if (isset($response['retcode']) && ($response['retcode'] !== "0 Done")) {
            Log::warning('MT5RestAPI: User API error', [
                'login' => $login,
                'retcode' => $response['retcode'],
                'retmsg' => $response['retmsg'] ?? 'Unknown error'
            ]);
            return null;
        }

        // Extract user data from answer field
        if (!isset($response['answer']) || !is_array($response['answer'])) {
            Log::warning('MT5RestAPI: Invalid single user response format', [
                'login' => $login,
                'response_keys' => is_array($response) ? array_keys($response) : 'not_array'
            ]);
            return null;
        }

        $userData = $response['answer'];

        if (!isset($userData['Login'])) {
            Log::warning('MT5RestAPI: User data missing login field', [
                'login' => $login,
                'userData_keys' => array_keys($userData)
            ]);
            return null;
        }

        return [
            'login' => (string)$userData['Login'],
            'balance' => floatval($userData['Balance'] ?? 0),
            'credit' => floatval($userData['Credit'] ?? 0),
            'margin' => floatval($userData['Margin'] ?? 0),
            'margin_free' => floatval($userData['MarginFree'] ?? 0),
            'margin_level' => floatval($userData['MarginLevel'] ?? 0),
            'equity' => floatval($userData['Equity'] ?? 0),
        ];
    }

    /**
     * Process the response from batch users API endpoint
     */
    private function processBatchUsersResponse($response, array $requestedLogins): array
    {
        // Decode JSON if needed
        $originalResponse = $response;
        if (is_string($response)) {
            $response = json_decode($response, true);
            if (!$response) {
                Log::warning('MT5RestAPI: Invalid JSON in batch response', [
                    'raw_response' => substr($originalResponse, 0, 500)
                ]);
                return [];
            }
        }

        // Log::info('MT5RestAPI: Processing batch users response', [
        //     'response_type' => gettype($response),
        //     'response_keys' => is_array($response) ? array_keys($response) : 'not_array'
        // ]);

        // Check for API error first
        if (isset($response['retcode']) && $response['retcode'] !== "0 Done") {
            Log::warning('MT5RestAPI: Batch API error', [
                'retcode' => $response['retcode'],
                'retmsg' => $response['retmsg'] ?? 'Unknown error',
                'requested_logins' => $requestedLogins
            ]);
            return [];
        }

        // Extract users array from different possible response formats
        $users = null;
        if (isset($response['answer']) && is_array($response['answer'])) {
            $users = $response['answer'];
        } elseif (isset($response['data']) && is_array($response['data'])) {
            $users = $response['data'];
        } elseif (is_array($response) && !isset($response['retcode'])) {
            // Direct array response
            $users = $response;
        } else {
            Log::warning('MT5RestAPI: Invalid batch users response format', [
                'response' => $response,
                'requested_logins' => $requestedLogins
            ]);
            return [];
        }

        if (!is_array($users)) {
            Log::warning('MT5RestAPI: Users data is not an array', [
                'users_type' => gettype($users),
                'requested_logins' => $requestedLogins
            ]);
            return [];
        }

        $balances = [];
        $foundLogins = [];

        foreach ($users as $userData) {
            if (isset($userData['Login'])) {
                $login = (string)$userData['Login'];
                $foundLogins[] = $login;

                $balances[$login] = [
                    'login' => $login,
                    'balance' => floatval($userData['Balance'] ?? 0),
                    'credit' => floatval($userData['Credit'] ?? 0),
                    'margin' => floatval($userData['Margin'] ?? 0),
                    'margin_free' => floatval($userData['MarginFree'] ?? 0),
                    'margin_level' => floatval($userData['MarginLevel'] ?? 0),
                    'equity' => floatval($userData['Equity'] ?? 0),
                ];
            }
        }

        // Log missing logins for debugging
        $missingLogins = array_diff(array_map('strval', $requestedLogins), $foundLogins);
        if (!empty($missingLogins)) {
            Log::info('MT5RestAPI: Some users not found in batch response', [
                'requested' => count($requestedLogins),
                'found' => count($foundLogins),
                'missing_logins' => $missingLogins
            ]);
        }

        // Log::info('MT5RestAPI: Batch users processing completed', [
        //     'requested_count' => count($requestedLogins),
        //     'found_count' => count($balances),
        //     'success_rate' => count($requestedLogins) > 0 ? round((count($balances) / count($requestedLogins)) * 100, 2) . '%' : '0%'
        // ]);

        return $balances;
    }

    /**
     * Update balances for multiple MT5 users efficiently
     *
     * @param Collection|array $mt5Users Collection of Mt5User models or array of logins
     * @return array Summary of update results
     */
    public function updateMultipleUserBalances($mt5Users): array
    {
        $startTime = microtime(true);

        // Convert to logins array if needed
        if ($mt5Users instanceof Collection) {
            $logins = $mt5Users->pluck('login')->toArray();
            $usersByLogin = $mt5Users->keyBy('login');
        } elseif (is_array($mt5Users) && !empty($mt5Users) && is_object($mt5Users[0])) {
            // Array of Mt5User objects
            $logins = array_map(fn($user) => $user->login, $mt5Users);
            $usersByLogin = collect($mt5Users)->keyBy('login');
        } else {
            // Array of login IDs
            $logins = $mt5Users;
            try {
                // Get Account objects instead of Mt5User
                $usersByLogin = Account::whereIn('code', $logins)->get()->keyBy('code');
            } catch (Exception $e) {
                Log::error('MT5RestAPI: Failed to fetch accounts', ['error' => $e->getMessage()]);
                return ['updated' => 0, 'errors' => count($logins), 'time' => 0];
            }
        }

        if (empty($logins)) {
            return ['updated' => 0, 'errors' => 0, 'time' => 0];
        }

        Log::info('MT5RestAPI: Starting batch balance update', [
            'user_count' => count($logins)
        ]);

        // Get balances in batch
        $balances = $this->getBatchBalances($logins);

        $updated = 0;
        $errors = 0;

        // Update each user's balance
        foreach ($balances as $login => $balanceData) {
            if (!isset($usersByLogin[$login])) {
                Log::warning('MT5RestAPI: User not found for login', ['login' => $login]);
                $errors++;
                continue;
            }

            try {
                $user = $usersByLogin[$login];
                $user->balance = $balanceData['balance'];
                $user->credit = $balanceData['credit'];
                $user->margin = $balanceData['margin'];
                $user->margin_free = $balanceData['margin_free'];
                $user->margin_level = $balanceData['margin_level'];
                $user->equity = $balanceData['equity'];
                $user->save();

                $updated++;
            } catch (Exception $e) {
                Log::error('MT5RestAPI: Failed to update user balance', [
                    'login' => $login,
                    'error' => $e->getMessage()
                ]);
                $errors++;
            }
        }

        $executionTime = microtime(true) - $startTime;

        $summary = [
            'updated' => $updated,
            'errors' => $errors,
            'time' => round($executionTime, 3),
            'total_requested' => count($logins),
            'balances_received' => count($balances)
        ];

        Log::info('MT5RestAPI: Batch balance update completed', $summary);

        return $summary;
    }

    /**
     * Get single user balance (falls back to batch for consistency)
     */
    public function getUserBalance(string $login): ?array
    {
        $balances = $this->getBatchBalances([$login]);
        return $balances[$login] ?? null;
    }

    /**
     * Get connection pool statistics
     */
    public function getConnectionStats(): array
    {
        return $this->connectionPool->getStats();
    }

    /**
     * Force cleanup connections (for maintenance)
     */
    public function cleanupConnections(): void
    {
        $this->connectionPool->forceCleanup();
    }

    /**
     * Health check for the service
     */
    public function healthCheck(): bool
    {
        try {
            $apiRequest = $this->connectionPool->getConnection();
            if (!$apiRequest) {
                return false;
            }

            // Try a simple API call
            $result = $apiRequest->Get('/api/ping');
            return $result !== false;
        } catch (Exception $e) {
            Log::error('MT5RestAPI: Health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
