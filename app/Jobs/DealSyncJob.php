<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Models\Account;
use App\Services\UniversalMT5Service;
use App\Services\TradeCacheService;
use App\MT5\MTRetCode;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Carbon\Carbon;

/**
 * Deal Sync Job - Incremental Deal Syncing
 *
 * This job intelligently syncs deals by:
 * 1. Getting the last deal time from our database
 * 2. Only fetching new deals since that time
 * 3. Storing deals for future position reconstruction
 * 4. Maintaining deal data freshness without full re-sync
 */
class DealSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $accounts;
    protected $fromTimes;
    protected $fullSync;
    public $timeout = 600; // 10 minutes for deal sync
    public $tries = 3; // More retries for deal sync
    public $maxExceptions = 2;
    public $retryAfter = 300; // 5 minutes between retries

    public function __construct(array $accounts, array $fromTimes = [], bool $fullSync = false)
    {

        // Convert Account models to serializable array format
        $this->accounts = collect($accounts)->map(function ($account) {
            return [
                'id' => $account->id,
                'code' => $account->code,
                'demo' => $account->demo,
                'last_deal_sync_at' => $account->last_deal_sync_at ?? null,
            ];
        })->toArray();

        $this->fromTimes = $fromTimes;
        $this->fullSync = $fullSync;

        // Set queue explicitly for deal sync
        $this->onQueue('deal-sync');

        // Set timeout based on number of accounts (more generous for deal sync)
        $this->timeout = max(600, count($accounts) * 120 + 300);
    }

    public function handle(UniversalMT5Service $mt5Service, TradeCacheService $cacheService = null)
    {
        $jobStartTime = microtime(true);
        $accountCodes = collect($this->accounts)->pluck('code')->join(', ');
        $accountCount = count($this->accounts);
        $startMemory = memory_get_usage(true);

        // Log::info("Starting DealSyncJob for {$accountCount} accounts: {$accountCodes} (Full Sync: " . ($this->fullSync ? 'Yes' : 'No') . ")");

        $results = [
            'processed' => 0,
            'success' => 0,
            'errors' => 0,
            'no_changes' => 0,
            'skipped' => 0,
            'deals_synced' => 0
        ];

        try {
            if (!$mt5Service->connect()) {
                throw new \Exception("Failed to establish MT5 connection");
            }
            $api = $mt5Service->getApi();

            foreach ($this->accounts as $index => $accountData) {

                $accountIterationStart = microtime(true);
                try {
                    $account = Account::find($accountData['id']);
                    if (!$account) {
                        Log::warning("Account {$accountData['code']} not found in database");
                        $results['skipped']++;
                        $results['processed']++;
                        continue;
                    }
                    $fromTime = $this->determineFromTime($account, $index);

                    $result = $this->syncAccountDeals($api, $account, $fromTime, $cacheService);

                    $results[$result['status']]++;
                    $results['deals_synced'] += $result['deals_count'];
                    $results['processed']++;
                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['processed']++;
                    // Log::error("Error syncing deals for account {$accountData['code']}: " . $e->getMessage());

                    // Update account sync status to reflect the error
                    if (isset($account) && $account instanceof Account) {
                        $this->updateAccountDealSyncStatus($account, 'error');
                    }

                    // Check if it's a connection issue and report it
                    if (
                        strpos($e->getMessage(), 'Broken pipe') !== false ||
                        strpos($e->getMessage(), 'socket') !== false
                    ) {
                        // Log::warning("Connection issue detected, reporting error to MT5 service");
                        $mt5Service->reportError();

                        // Try to reconnect for remaining accounts
                        if ($index < count($this->accounts) - 1) {
                            // Log::info("Attempting reconnection for remaining accounts...");
                            usleep(2000000); // 2 second delay before reconnect attempt
                            if (!$mt5Service->connect()) {
                                // Log::error("Failed to reconnect MT5 service, aborting remaining accounts");
                                break;
                            }
                            $api = $mt5Service->getApi();
                        }
                    } else {
                        $mt5Service->reportError();
                    }
                }

                if ($index < count($this->accounts) - 1) {
                    usleep(100000); // 0.1 second delay between accounts
                }
            }
        } catch (\Exception $e) {
            // Log::error("DealSyncJob failed: " . $e->getMessage());
            // Ensure we report the error to release any held connections
            $mt5Service->reportError();
            throw $e;
        }

        $totalJobTime = round((microtime(true) - $jobStartTime) * 1000, 2);
        $endMemory = memory_get_usage(true);
        $memoryUsed = round(($endMemory - $startMemory) / 1024 / 1024, 2);

        // Log::info("DealSyncJob COMPLETE: {$results['processed']} accounts in {$totalJobTime}ms. " .
        //     "Success: {$results['success']}, Errors: {$results['errors']}, " .
        //     "No changes: {$results['no_changes']}, Total deals: {$results['deals_synced']}. " .
        //     "Memory: {$memoryUsed}MB");

        // Update trade profits for positions that had new deals synced
        if ($results['deals_synced'] > 0) {
            $this->updateTradeProfilesAfterDealSync();
        }
    }

    protected function determineFromTime(Account $account, int $index): Carbon
    {
        // Use provided fromTime if available
        if (isset($this->fromTimes[$index])) {
            return $this->fromTimes[$index];
        }

        // For full sync, go back 30 days
        if ($this->fullSync) {
            return now()->subDays(30);
        }

        // Get last deal time from database for incremental sync
        $lastDealTime = Deal::getLatestDealTime($account->id);

        if ($lastDealTime) {
            // Start from last deal time minus 1 hour for overlap safety
            return $lastDealTime->subHour();
        }

        // If no deals exist, sync last 7 days
        return now()->subDays(7);
    }

    protected function syncAccountDeals($api, Account $account, Carbon $fromTime, TradeCacheService $cacheService = null): array
    {
        $accountStartTime = microtime(true);
        $timings = [];

        if (!$account->code) {
            $this->updateAccountDealSyncStatus($account, 'error');
            return ['status' => 'errors', 'deals_count' => 0];
        }

        // Phase 1: MT5 User Check
        $phaseStart = microtime(true);
        $mt5_user = null;
        $error_code = $api->UserGet($account->code, $mt5_user);
        $timings['mt5_user_check'] = round((microtime(true) - $phaseStart) * 1000, 2);

        if ($error_code != MTRetCode::MT_RET_OK) {
            // Log::warning("MT5 user not found for account2 {$account->code}");
            $this->updateAccountDealSyncStatus($account, 'error');
            return ['status' => 'errors', 'deals_count' => 0];
        }

        try {
            $login = $account->code;
            $fromTimestamp = $fromTime->timestamp; // Unix timestamp for MT5 API
            $toTimestamp = now()->addHours(4)->timestamp; // Unix timestamp for MT5 API
            $fromDateDb = $fromTime->format('Y-m-d H:i:s'); // For database queries (SAME RANGE)
            $toDateDb = now()->addHours(4)->format('Y-m-d H:i:s'); // For database queries (SAME RANGE)
            $totalDeals = 0;

            // PRIORITY OPTIMIZATION: Check MT5 deal total count vs database count for ENTIRE date range FIRST
            // Log::info("DEBUG[{$account->code}]: Checking MT5 deal total count vs database count for entire requested range to avoid unnecessary processing...");
            $phaseStart = microtime(true);

            $error_code = $api->DealGetTotal($login, $fromTimestamp, $toTimestamp, $totalDeals);

            $timings['mt5_deal_total'] = round((microtime(true) - $phaseStart) * 1000, 2);

            if ($error_code != MTRetCode::MT_RET_OK) {
                // Log::error("Failed to get total deals for account {$account->code}: " . MTRetCode::GetError($error_code));
                $this->updateAccountDealSyncStatus($account, 'error');
                return ['status' => 'errors', 'deals_count' => 0];
            }

            // Count existing deals in our database for the EXACT SAME RANGE
            $dbDealCount = Deal::where('account_id', $account->id)
                ->whereBetween('time_done', [$fromDateDb, $toDateDb])
                ->count();

            // Log::info("DEBUG[{$account->code}]: MT5 deal total: {$totalDeals}, DB deal count: {$dbDealCount} (range: {$fromDateDb} to {$toDateDb}, check took {$timings['mt5_deal_total']}ms)");

            if ($totalDeals == $dbDealCount) {
                if ($totalDeals > 0) {
                    // Database is perfectly in sync with MT5 - no need to fetch from MT5!
                    // Log::info("DEBUG[{$account->code}]: Deal counts match perfectly! Using DATABASE OPTIMIZATION - no MT5 processing needed.");
                    $this->updateAccountDealSyncStatus($account, 'success', 0); // 0 new deals since we already have them
                    return ['status' => 'success', 'deals_count' => 0]; // 0 new deals fetched from MT5
                } else {
                    // Both MT5 and DB report 0 deals for this range
                    // Log::info("DEBUG[{$account->code}]: Both MT5 and DB report 0 deals for range. No activity to sync.");
                    $this->updateAccountDealSyncStatus($account, 'no_changes');
                    return ['status' => 'no_changes', 'deals_count' => 0];
                }
            }

            // Deal counts differ - need to sync the difference
            $dealDifference = $totalDeals - $dbDealCount;
            Log::info("DEBUG[{$account->code}]: Deal count mismatch! MT5: {$totalDeals}, DB: {$dbDealCount}, Difference: {$dealDifference}. Proceeding with MT5 sync...");

            if ($totalDeals == 0) {
                $this->updateAccountDealSyncStatus($account, 'no_changes');
                return ['status' => 'no_changes', 'deals_count' => 0];
            }

            // Phase 3: Get existing deals to avoid duplicates
            $phaseStart = microtime(true);
            $existingDealIds = Deal::where('account_id', $account->id)
                ->pluck('deal_id')
                ->toArray();
            $timings['db_existing_deals'] = round((microtime(true) - $phaseStart) * 1000, 2);

            // Log::info("Account {$account->code}: Found {$totalDeals} new deals, " . count($existingDealIds) . " existing deals in database");

            // Phase 4: MT5 DealGetPage with Adaptive Pagination
            $phaseStart = microtime(true);
            $allDeals = [];
            $requestedPageSize = 1000;
            $totalDealTime = 0;
            $pageCount = 0;

            while (count($allDeals) < $totalDeals) {
                $startIndex = count($allDeals);
                $remainingDeals = $totalDeals - $startIndex;
                $pageDeals = [];
                $currentPageSize = min($requestedPageSize, $remainingDeals);

                $pageStart = microtime(true);
                $error_code = $api->DealGetPage($login, $fromTimestamp, $toTimestamp, $startIndex, $totalDeals, $pageDeals);
                $pageTime = round((microtime(true) - $pageStart) * 1000, 2);
                $totalDealTime += $pageTime;
                $pageCount++;

                if ($error_code != MTRetCode::MT_RET_OK) {
                    // Log::error("MT5 DealGetPage error for account {$account->code} page {$pageCount}: " . MTRetCode::GetError($error_code));
                    $this->updateAccountDealSyncStatus($account, 'error');
                    return ['status' => 'errors', 'deals_count' => 0];
                }

                $allDeals = array_merge($allDeals, $pageDeals);

                if (count($pageDeals) === 0) {
                    break;
                }

                if (count($allDeals) < $totalDeals) {
                    usleep(50000); // 0.05 second delay between pages
                }
            }

            $timings['mt5_deal_page'] = $totalDealTime;

            // Phase 5: Process and Store New Deals
            $phaseStart = microtime(true);
            $dealsToInsert = [];
            $newDealsCount = 0;

            foreach ($allDeals as $dealData) {
                // Skip if deal data is invalid
                if (!$dealData || !is_object($dealData) || !property_exists($dealData, 'Deal')) {
                    continue;
                }

                // Skip if deal already exists
                if (in_array($dealData->Deal, $existingDealIds)) {
                    continue;
                }

                // Skip entries without symbol (deposits/withdrawals from MT5 API)
                if (empty(trim($dealData->Symbol ?? ''))) {
                    continue;
                }

                $dealRecord = $this->prepareDealData($account, $dealData);
                if ($dealRecord) {
                    $dealsToInsert[] = $dealRecord;
                    $newDealsCount++;
                }

                // Batch insert for performance
                if (count($dealsToInsert) >= 100) {
                    Deal::insert($dealsToInsert);

                    // Invalidate cache when new deals are inserted
                    if ($cacheService && count($dealsToInsert) > 0) {
                        $cacheService->invalidateAccountDeals($account);
                    }

                    $dealsToInsert = [];
                }
            }

            // Insert remaining deals
            if (!empty($dealsToInsert)) {
                Deal::insert($dealsToInsert);

                // Invalidate cache when new deals are inserted
                if ($cacheService && count($dealsToInsert) > 0) {
                    $cacheService->invalidateAccountDeals($account);
                }
            }

            $timings['deal_processing'] = round((microtime(true) - $phaseStart) * 1000, 2);

            // Phase 6: Update Account Status
            $this->updateAccountDealSyncStatus($account, 'success', $newDealsCount);

            $totalTime = round((microtime(true) - $accountStartTime) * 1000, 2);
            // Log::info("DEAL_SYNC[{$account->code}]: {$totalTime}ms total | " .
            //     "New deals: {$newDealsCount}/{$totalDeals} | " .
            //     "Existing: " . count($existingDealIds) . " | " .
            //     "Breakdown: " . json_encode($timings));

            return ['status' => 'success', 'deals_count' => $newDealsCount];
        } catch (\Exception $e) {
            // Log::error("Error syncing deals for account {$account->code}: " . $e->getMessage());
            $this->updateAccountDealSyncStatus($account, 'error');
            return ['status' => 'errors', 'deals_count' => 0];
        }
    }

    protected function prepareDealData(Account $account, $dealData): ?array
    {
        if (empty($dealData->Deal) || $dealData->Deal <= 0) {
            // Log::warning("Invalid deal ID for account {$account->code}: " . ($dealData->Deal ?? 'null'));
            return null;
        }

        return [
            'account_id' => $account->id,
            'deal_id' => $dealData->Deal,
            'order_id' => $dealData->Order ?? null,
            'position_id' => $dealData->PositionID ?? $dealData->ExpertPositionID ?? null,
            'symbol' => $dealData->Symbol ?? '',
            'type' => $dealData->Type ?? 0,
            'action' => $dealData->Action ?? null, // Deal operation type (DEAL_TYPE_*)
            'entry' => $dealData->Entry ?? null, // Deal direction: 0=in, 1=out, 2=inout
            'volume' => ($dealData->Volume ?? 0) / 10000, // Convert to lots
            'price' => $dealData->Price ?? 0,
            'profit' => $dealData->Profit ?? 0,
            'swap' => $dealData->Swap ?? 0,
            'commission' => $dealData->Commission ?? 0,
            'comment' => $dealData->Comment ?? null,
            'reason' => $dealData->Reason ?? null,
            'time_done' => date('Y-m-d H:i:s', $dealData->Time ?? time()),
            'time_msc' => $dealData->TimeMsc ?? null,
            'time_setup' => isset($dealData->TimeSetup) ? date('Y-m-d H:i:s', $dealData->TimeSetup) : null,
            'magic' => $dealData->Magic ?? null,
            'external_id' => $dealData->ExternalID ?? null,
            'rate_profit' => $dealData->RateProfit ?? 1,
            'rate_margin' => $dealData->RateMargin ?? 1,
            'raw_data' => json_encode($dealData),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    protected function updateAccountDealSyncStatus(Account $account, string $status, int $dealsCount = 0): void
    {
        // Update the account's deal sync tracking properly
        if ($status === 'success' || $status === 'no_changes') {
            // Get the actual time range of deals we just synced
            $dealTimeRange = Deal::where('account_id', $account->id)
                ->selectRaw('MIN(time_done) as earliest, MAX(time_done) as latest')
                ->first();

            $from = $dealTimeRange && $dealTimeRange->earliest ?
                Carbon::parse($dealTimeRange->earliest) : now()->subDays(30);
            $to = $dealTimeRange && $dealTimeRange->latest ?
                Carbon::parse($dealTimeRange->latest) : now();

            // Update the account's deal sync status with the actual time range
            $account->updateDealSyncStatus($from, $to, true);

            // Log::info("Updated deal sync status for account {$account->code}: {$status} (deals: {$dealsCount}) from {$from} to {$to}");
        } else {
            // For errors, update fetch time but mark sync as incomplete
            $account->update([
                'deals_last_fetch_at' => now(),
                'deals_sync_complete' => false
            ]);
            // Log::info("Updated deal sync status for account {$account->code}: {$status} (deals: {$dealsCount}) - marked as incomplete");
        }
    }

    /**
     * Update trade profits for positions that had new deals synced
     * This ensures trade profits are recalculated after deal sync completes
     */
    protected function updateTradeProfilesAfterDealSync(): void
    {
        $updatedCount = 0;
        // Log::info("updateTradeProfilesAfterDealSync: Starting trade profit updates for " . count($this->accounts) . " accounts");

        foreach ($this->accounts as $accountData) {
            $account = Account::find($accountData['id']);
            if (!$account) continue;

            // Get all trades for this account that might need profit updates
            // Handle both string ('closed') and numeric (4) status values
            $trades = \App\Models\Trade::where('account_id', $account->id)
                ->where(function ($query) {
                    $query->where('status', 'closed')     // String format (local)
                        ->orWhere('status', '4')         // Numeric format (production)
                        ->orWhere('status', 4);          // Integer format (just in case)
                })
                ->get();

            // Log::info("updateTradeProfilesAfterDealSync: Account {$account->code} has {$trades->count()} closed trades to check");

            // Also log the status values we found for debugging
            if ($trades->count() > 0) {
                $statusValues = $trades->pluck('status')->unique()->toArray();
                // Log::info("updateTradeProfilesAfterDealSync: Status values found for {$account->code}: " . json_encode($statusValues));
            }

            foreach ($trades as $trade) {
                // Get all deals for this position
                $deals = \App\Models\Deal::where('account_id', $account->id)
                    ->where('position_id', $trade->position_id)
                    ->get();

                if ($deals->isEmpty()) continue;

                // Calculate new profit from deals
                $newProfit = round($deals->sum('profit'), 2);

                // Only update if profit has changed significantly (avoid unnecessary updates)
                if (abs($trade->profit - $newProfit) >= 0.01) {
                    $oldProfit = $trade->profit;
                    $trade->update([
                        'profit' => $newProfit,
                        'updated_at' => now()
                    ]);

                    $updatedCount++;
                    // Log::info("Updated trade {$trade->position_id} profit: {$oldProfit} → {$newProfit} (account: {$account->code})");
                }
            }
        }

        if ($updatedCount > 0) {
            // Log::info("Updated {$updatedCount} trade profits after deal sync");
        } else {
            // Log::info("updateTradeProfilesAfterDealSync: No trade profits needed updating");
        }
    }
    public function failed(\Throwable $exception)
    {
        Log::error("DealSyncJob permanently failed: " . $exception->getMessage(), [
            'accounts' => collect($this->accounts)->pluck('code')->toArray(),
            'exception' => $exception->getTraceAsString()
        ]);
    }
}
