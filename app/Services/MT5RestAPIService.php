<?php

namespace App\Services;

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
     * Get MT5 server common information via REST API
     *
     * @return array|null Server configuration and limits, or null on error
     */
    public function getServerCommon(): ?array
    {
        $apiRequest = $this->connectionPool->getConnection();
        if (! $apiRequest) {
            Log::error('MT5RestAPI: Failed to get connection from pool for server common info');

            return null;
        }

        try {
            // Make request to server common info endpoint
            $result = $apiRequest->Get('/api/common/get');

            if ($result === false) {
                Log::warning('MT5RestAPI: Server common info request failed');
                $this->connectionPool->reportConnectionError($apiRequest);

                return null;
            }

            return $this->processServerCommonResponse($result);
        } catch (Exception $e) {
            Log::error('MT5RestAPI: Exception in getServerCommon', [
                'error' => $e->getMessage(),
            ]);
            $this->connectionPool->reportConnectionError($apiRequest);

            return null;
        }
    }

    /**
     * Process server common response from REST API
     */
    private function processServerCommonResponse($response): ?array
    {
        // Decode JSON if needed
        if (is_string($response)) {
            $response = json_decode($response, true);
            if (! $response) {
                Log::warning('MT5RestAPI: Invalid JSON in server common response');
                return null;
            }
        }

        // Check for API error
        if (isset($response['retcode']) && $response['retcode'] !== '0 Done') {
            Log::warning('MT5RestAPI: Server common API error', [
                'retcode' => $response['retcode'],
                'retmsg' => $response['retmsg'] ?? 'Unknown error',
            ]);
            return null;
        }

        // Extract server data from different possible response formats
        $serverData = null;
        if (isset($response['answer']) && is_array($response['answer'])) {
            $serverData = $response['answer'];
        } elseif (isset($response['data']) && is_array($response['data'])) {
            $serverData = $response['data'];
        } elseif (is_array($response) && ! isset($response['retcode'])) {
            // Direct array response
            $serverData = $response;
        } else {
            Log::warning('MT5RestAPI: Invalid server common response format', [
                'response' => $response,
            ]);

            return null;
        }

        if (! is_array($serverData)) {
            Log::warning('MT5RestAPI: Server data is not an array', [
                'server_data_type' => gettype($serverData),
            ]);

            return null;
        }

        // Return normalized server common data
        return [
            'name' => $serverData['Name'] ?? $serverData['name'] ?? '',
            'owner' => $serverData['Owner'] ?? $serverData['owner'] ?? '',
            'owner_id' => $serverData['OwnerID'] ?? $serverData['owner_id'] ?? '',
            'owner_host' => $serverData['OwnerHost'] ?? $serverData['owner_host'] ?? '',
            'owner_email' => $serverData['OwnerEmail'] ?? $serverData['owner_email'] ?? '',
            'product' => $serverData['Product'] ?? $serverData['product'] ?? '',
            'expiration_license' => (int) ($serverData['ExpirationLicense'] ?? $serverData['expiration_license'] ?? 0),
            'expiration_support' => (int) ($serverData['ExpirationSupport'] ?? $serverData['expiration_support'] ?? 0),
            'limit_trade_servers' => (int) ($serverData['LimitTradeServers'] ?? $serverData['limit_trade_servers'] ?? 0),
            'limit_web_servers' => (int) ($serverData['LimitWebServers'] ?? $serverData['limit_web_servers'] ?? 0),
            'limit_accounts' => (int) ($serverData['LimitAccounts'] ?? $serverData['limit_accounts'] ?? 0),
            'limit_deals' => (int) ($serverData['LimitDeals'] ?? $serverData['limit_deals'] ?? 0),
            'limit_symbols' => (int) ($serverData['LimitSymbols'] ?? $serverData['limit_symbols'] ?? 0),
            'limit_groups' => (int) ($serverData['LimitGroups'] ?? $serverData['limit_groups'] ?? 0),
            'live_update_mode' => (int) ($serverData['LiveUpdateMode'] ?? $serverData['live_update_mode'] ?? 0),
            'total_users' => (int) ($serverData['TotalUsers'] ?? $serverData['total_users'] ?? 0),
            'total_users_real' => (int) ($serverData['TotalUsersReal'] ?? $serverData['total_users_real'] ?? 0),
            'total_deals' => (int) ($serverData['TotalDeals'] ?? $serverData['total_deals'] ?? 0),
            'total_orders' => (int) ($serverData['TotalOrders'] ?? $serverData['total_orders'] ?? 0),
            'total_orders_history' => (int) ($serverData['TotalOrdersHistory'] ?? $serverData['total_orders_history'] ?? 0),
            'total_positions' => (int) ($serverData['TotalPositions'] ?? $serverData['total_positions'] ?? 0),
            'account_url' => $serverData['AccountURL'] ?? $serverData['account_url'] ?? '',
            'account_auto' => (int) ($serverData['AccountAuto'] ?? $serverData['account_auto'] ?? 0),
        ];
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
            // Log::warning('MT5RestAPI: User API error', [
            //     'login' => $login,
            //     'retcode' => $response['retcode'],
            //     'retmsg' => $response['retmsg'] ?? 'Unknown error'
            // ]);
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

    // ──────────────────────────────────────────────────────────
    //  Deal Batch API  (GET /api/deal/get_batch)
    //
    //  Benchmarks (empirical):
    //    ~2,500 deals/sec throughput regardless of login count.
    //    5 logins × 90 days ≈ 68K deals in 25s.
    //    Target: keep each HTTP round-trip under 20s to avoid
    //    connection timeouts, so we cap at ~50K deals per call.
    // ──────────────────────────────────────────────────────────

    /** Target max deals per single REST call (~20s at 2,500 d/s) */
    private const DEAL_BATCH_TARGET_DEALS = 50000;

    /** Absolute max logins we ever send in one GET request */
    private const DEAL_BATCH_MAX_LOGINS = 50;

    /** Fallback avg deals/account when we have no history */
    private const DEAL_BATCH_DEFAULT_AVG = 500;

    /**
     * Fetch deals for multiple logins in one REST call.
     *
     * @param  array  $logins  Array of MT5 login IDs
     * @param  int    $from    Unix timestamp start
     * @param  int    $to      Unix timestamp end
     * @return array  ['deals' => [...], 'meta' => [...]]
     *                deals grouped by login: [ login => [ deal, ... ] ]
     */
    public function getBatchDeals(array $logins, int $from, int $to): array
    {
        if (empty($logins)) {
            return ['deals' => [], 'meta' => ['total' => 0, 'time' => 0, 'batches' => 0]];
        }

        $logins = array_values(array_unique(array_map('intval', $logins)));
        $chunks = $this->buildDealBatchChunks($logins, $from, $to);

        $allDeals = [];
        $totalFetched = 0;
        $batchCount = 0;
        $startTime = microtime(true);

        foreach ($chunks as $chunk) {
            $result = $this->fetchDealBatchChunk($chunk, $from, $to);
            // Log::info('MT5RestAPI: Deal batch chunk data is ', [$result,$chunk, $from, $to]);
            if ($result === null) {
                continue;
            }

            foreach ($result as $deal) {
                $login = (string)($deal['Login'] ?? '');
                if ($login === '') {
                    continue;
                }
                $allDeals[$login][] = $deal;
                $totalFetched++;
            }
            $batchCount++;
        }

        $elapsed = round(microtime(true) - $startTime, 3);

        Log::info('MT5RestAPI: getBatchDeals completed', [
            'logins_requested' => count($logins),
            'logins_returned' => count($allDeals),
            'total_deals' => $totalFetched,
            'batches' => $batchCount,
            'time' => $elapsed,
        ]);

        return [
            'deals' => $allDeals,
            'meta' => [
                'total' => $totalFetched,
                'time' => $elapsed,
                'batches' => $batchCount,
            ],
        ];
    }

    /**
     * Intelligently partition logins into chunks that each stay
     * under the target deal count per REST call.
     *
     * Strategy:
     *  1. Look up recent average deals/account from the deals table
     *     (cheap aggregate, cached 10 min).
     *  2. Compute how many logins fit under DEAL_BATCH_TARGET_DEALS.
     *  3. Clamp between 1 and DEAL_BATCH_MAX_LOGINS.
     *  4. Chunk the login list accordingly.
     */
    private function buildDealBatchChunks(array $logins, int $from, int $to): array
    {
        $avgDeals = $this->estimateAvgDealsPerAccount($from, $to);
        $loginsPerChunk = (int) floor(self::DEAL_BATCH_TARGET_DEALS / max($avgDeals, 1));
        $loginsPerChunk = max(1, min($loginsPerChunk, self::DEAL_BATCH_MAX_LOGINS));

        Log::info('MT5RestAPI: Deal batch sizing', [
            'avg_deals_per_account' => $avgDeals,
            'logins_per_chunk' => $loginsPerChunk,
            'total_logins' => count($logins),
            'chunks' => (int) ceil(count($logins) / $loginsPerChunk),
        ]);

        return array_chunk($logins, $loginsPerChunk);
    }

    /**
     * Estimate average deals per account for the given time window.
     * Uses a lightweight DB aggregate, cached for 10 minutes.
     */
    private function estimateAvgDealsPerAccount(int $from, int $to): float
    {
        $cacheKey = "mt5:avg_deals_per_account:{$from}:{$to}";

        return (float) \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($from, $to) {
            $fromDate = date('Y-m-d H:i:s', $from);
            $toDate = date('Y-m-d H:i:s', $to);

            $stats = \App\Models\Deal::selectRaw('COUNT(*) as total_deals, COUNT(DISTINCT account_id) as accounts')
                ->whereBetween('time_done', [$fromDate, $toDate])
                ->first();

            if (!$stats || $stats->accounts == 0) {
                return self::DEAL_BATCH_DEFAULT_AVG;
            }

            return round($stats->total_deals / $stats->accounts, 1);
        });
    }

    /**
     * Execute a single REST call for a chunk of logins.
     *
     * @return array|null  Raw deal arrays on success, null on failure
     */
    private function fetchDealBatchChunk(array $logins, int $from, int $to): ?array
    {
        $apiRequest = $this->connectionPool->getConnection();
        if (!$apiRequest) {
            Log::error('MT5RestAPI: No connection for deal batch chunk');
            return null;
        }

        try {
            $loginString = implode(',', $logins);
            $url = "/api/deal/get_batch?login={$loginString}&from={$from}&to={$to}";

            $start = microtime(true);
            $raw = $apiRequest->Get($url);
            $elapsed = round(microtime(true) - $start, 3);

            if ($raw === false) {
                Log::warning('MT5RestAPI: Deal batch chunk failed', [
                    'logins' => count($logins),
                    'time' => $elapsed,
                ]);
                $this->connectionPool->reportConnectionError($apiRequest);
                return null;
            }

            $decoded = is_string($raw) ? json_decode($raw, true) : $raw;

            if (!is_array($decoded)) {
                Log::warning('MT5RestAPI: Deal batch invalid JSON', [
                    'raw_preview' => substr($raw, 0, 200),
                ]);
                return null;
            }

            if (isset($decoded['retcode']) && $decoded['retcode'] !== '0 Done') {
                Log::warning('MT5RestAPI: Deal batch API error', [
                    'retcode' => $decoded['retcode'],
                    'logins' => $logins,
                ]);
                return null;
            }

            $deals = $decoded['answer'] ?? [];
            if (!is_array($deals)) {
                return null;
            }

            Log::info('MT5RestAPI: Deal batch chunk OK', [
                'logins' => count($logins),
                'deals' => count($deals),
                'time' => $elapsed,
            ]);

            return $deals;
        } catch (Exception $e) {
            Log::error('MT5RestAPI: Deal batch chunk exception', [
                'error' => $e->getMessage(),
                'logins' => $logins,
            ]);
            $this->connectionPool->reportConnectionError($apiRequest);
            return null;
        }
    }

    /**
     * Get total deal counts for multiple logins via REST API.
     *
     * @param  array  $logins
     * @param  int    $from  Unix timestamp
     * @param  int    $to    Unix timestamp
     * @return array  [ login => total_count, ... ]
     */
    public function getDealTotals(array $logins, int $from, int $to): array
    {
        $totals = [];
        $apiRequest = $this->connectionPool->getConnection();
        if (!$apiRequest) {
            return $totals;
        }

        foreach ($logins as $login) {
            try {
                $raw = $apiRequest->Get("/api/deal/get_total?login=" . intval($login) . "&from={$from}&to={$to}");
                $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
                if (isset($decoded['answer']['total'])) {
                    $totals[(string)$login] = (int)$decoded['answer']['total'];
                }
            } catch (Exception $e) {
                Log::warning('MT5RestAPI: getDealTotals failed', ['login' => $login, 'error' => $e->getMessage()]);
            }
        }

        return $totals;
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
     * Get all open positions for a specific login (for trade verification)
     *
     * @param int|string $login MT5 login ID
     * @return array Array of open position IDs from MT5
     */
    public function getOpenPositions($login): array
    {
        $apiRequest = $this->connectionPool->getConnection();
        if (!$apiRequest) {
            Log::error('MT5RestAPI: Failed to get connection from pool', ['login' => $login]);
            return [];
        }

        try {
            // Request open positions using /api/position/get_page
            // Start from position 0, request up to 1000 positions (typically much less)
            $requestData = [
                'login' => (int)$login,
                'from' => 0,
                'total' => 1000
            ];

            $result = $apiRequest->Post('/api/position/get_page', json_encode($requestData));

            if ($result === false) {
                Log::warning('MT5RestAPI: Open positions request failed', ['login' => $login]);
                $this->connectionPool->reportConnectionError($apiRequest);
                return [];
            }

            return $this->processOpenPositionsResponse($result, $login);
        } catch (Exception $e) {
            Log::error('MT5RestAPI: Exception in getOpenPositions', [
                'error' => $e->getMessage(),
                'login' => $login
            ]);
            $this->connectionPool->reportConnectionError($apiRequest);
            return [];
        }
    }

    /**
     * Process open positions response from REST API
     */
    private function processOpenPositionsResponse($response, $login): array
    {
        // Decode JSON if needed
        if (is_string($response)) {
            $response = json_decode($response, true);
            if (!$response) {
                Log::warning('MT5RestAPI: Invalid JSON in open positions response', ['login' => $login]);
                return [];
            }
        }

        // Check for API error
        if (isset($response['retcode']) && $response['retcode'] !== "0 Done") {
            Log::warning('MT5RestAPI: Open positions API error', [
                'login' => $login,
                'retcode' => $response['retcode'],
                'retmsg' => $response['retmsg'] ?? 'Unknown error',
                'response' => $response
            ]);
            return [];
        }

        // Extract positions array from response
        $positions = [];
        if (isset($response['answer']) && is_array($response['answer'])) {
            $positionsData = $response['answer'];
        } elseif (isset($response['data']) && is_array($response['data'])) {
            $positionsData = $response['data'];
        } elseif (is_array($response) && !isset($response['retcode'])) {
            $positionsData = $response;
        } else {
            Log::warning('MT5RestAPI: Invalid open positions response format', [
                'login' => $login,
                'response_keys' => is_array($response) ? array_keys($response) : 'not_array'
            ]);
            return [];
        }

        if (!is_array($positionsData)) {
            return [];
        }

        // Extract position IDs
        foreach ($positionsData as $positionData) {
            if (isset($positionData['ID'])) {
                $positions[] = (int)$positionData['ID'];
            } elseif (isset($positionData['Ticket'])) {
                $positions[] = (int)$positionData['Ticket'];
            }
        }

        Log::info('MT5RestAPI: Open positions retrieved', [
            'login' => $login,
            'open_position_count' => count($positions)
        ]);

        return $positions;
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
