<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Models\Account;
use App\Services\UniversalMT5Service;
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
    public $timeout = 300; // 5 minutes
    public $tries = 2;

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

        // Set timeout based on number of accounts
        $this->timeout = max(300, count($accounts) * 60 + 120);
    }

    public function handle(UniversalMT5Service $mt5Service)
    {
        $jobStartTime = microtime(true);
        $accountCodes = collect($this->accounts)->pluck('code')->join(', ');
        $accountCount = count($this->accounts);
        $startMemory = memory_get_usage(true);

        Log::info("Starting DealSyncJob for {$accountCount} accounts: {$accountCodes} (Full Sync: " . ($this->fullSync ? 'Yes' : 'No') . ")");

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
                    $result = $this->syncAccountDeals($api, $account, $fromTime);

                    $results[$result['status']]++;
                    $results['deals_synced'] += $result['deals_count'];
                    $results['processed']++;

                    $accountTime = round((microtime(true) - $accountIterationStart) * 1000, 2);
                    Log::info("Account {$account->code}: {$result['status']} - {$result['deals_count']} deals ({$accountTime}ms)");
                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['processed']++;
                    Log::error("Error syncing deals for account {$accountData['code']}: " . $e->getMessage());
                    $mt5Service->reportError();
                }

                if ($index < count($this->accounts) - 1) {
                    usleep(100000); // 0.1 second delay between accounts
                }
            }
        } catch (\Exception $e) {
            Log::error("DealSyncJob failed: " . $e->getMessage());
            throw $e;
        }

        $totalJobTime = round((microtime(true) - $jobStartTime) * 1000, 2);
        $endMemory = memory_get_usage(true);
        $memoryUsed = round(($endMemory - $startMemory) / 1024 / 1024, 2);

        Log::info("DealSyncJob COMPLETE: {$results['processed']} accounts in {$totalJobTime}ms. " .
            "Success: {$results['success']}, Errors: {$results['errors']}, " .
            "No changes: {$results['no_changes']}, Total deals: {$results['deals_synced']}. " .
            "Memory: {$memoryUsed}MB");
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

    protected function syncAccountDeals($api, Account $account, Carbon $fromTime): array
    {
        $accountStartTime = microtime(true);
        $timings = [];

        if (!$account->code) {
            return ['status' => 'errors', 'deals_count' => 0];
        }

        // Phase 1: MT5 User Check
        $phaseStart = microtime(true);
        $mt5_user = null;
        $error_code = $api->UserGet($account->code, $mt5_user);
        $timings['mt5_user_check'] = round((microtime(true) - $phaseStart) * 1000, 2);

        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::warning("MT5 user not found for account {$account->code}");
            return ['status' => 'errors', 'deals_count' => 0];
        }

        try {
            $login = $account->code;
            $fromDate = $fromTime->format('F d, Y');
            $toDate = now()->addHours(4)->format('F d, Y');
            $totalDeals = 0;

            // Phase 2: MT5 DealGetTotal
            $phaseStart = microtime(true);
            $error_code = $api->DealGetTotal($login, $fromDate, $toDate, $totalDeals);
            $timings['mt5_deal_total'] = round((microtime(true) - $phaseStart) * 1000, 2);

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("Failed to get total deals for account {$account->code}: " . MTRetCode::GetError($error_code));
                return ['status' => 'errors', 'deals_count' => 0];
            }

            if ($totalDeals == 0) {
                $this->updateAccountDealSyncStatus($account, 'no_changes');
                return ['status' => 'no_changes', 'deals_count' => 0];
            }

            Log::info("Account {$account->code} has {$totalDeals} deals from {$fromDate} to {$toDate}");

            // Phase 3: Get existing deals to avoid duplicates
            $phaseStart = microtime(true);
            $existingDealIds = Deal::where('account_id', $account->id)
                ->where('time_done', '>=', $fromTime)
                ->pluck('deal_id')
                ->toArray();
            $timings['db_existing_deals'] = round((microtime(true) - $phaseStart) * 1000, 2);

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
                $error_code = $api->DealGetPage($login, $fromDate, $toDate, $startIndex, $totalDeals, $pageDeals);
                $pageTime = round((microtime(true) - $pageStart) * 1000, 2);
                $totalDealTime += $pageTime;
                $pageCount++;

                if ($error_code != MTRetCode::MT_RET_OK) {
                    Log::error("MT5 DealGetPage error for account {$account->code} page {$pageCount}: " . MTRetCode::GetError($error_code));
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

                $dealRecord = $this->prepareDealData($account, $dealData);
                if ($dealRecord) {
                    $dealsToInsert[] = $dealRecord;
                    $newDealsCount++;
                }

                // Batch insert for performance
                if (count($dealsToInsert) >= 100) {
                    Deal::insert($dealsToInsert);
                    $dealsToInsert = [];
                }
            }

            // Insert remaining deals
            if (!empty($dealsToInsert)) {
                Deal::insert($dealsToInsert);
            }

            $timings['deal_processing'] = round((microtime(true) - $phaseStart) * 1000, 2);

            // Phase 6: Update Account Status
            $this->updateAccountDealSyncStatus($account, 'success', $newDealsCount);

            $totalTime = round((microtime(true) - $accountStartTime) * 1000, 2);
            Log::info("DEAL_SYNC[{$account->code}]: {$totalTime}ms total | " .
                "New deals: {$newDealsCount}/{$totalDeals} | " .
                "Existing: " . count($existingDealIds) . " | " .
                "Breakdown: " . json_encode($timings));

            return ['status' => 'success', 'deals_count' => $newDealsCount];
        } catch (\Exception $e) {
            Log::error("Error syncing deals for account {$account->code}: " . $e->getMessage());
            return ['status' => 'errors', 'deals_count' => 0];
        }
    }

    protected function prepareDealData(Account $account, $dealData): ?array
    {
        if (empty($dealData->Deal) || $dealData->Deal <= 0) {
            Log::warning("Invalid deal ID for account {$account->code}: " . ($dealData->Deal ?? 'null'));
            return null;
        }

        return [
            'account_id' => $account->id,
            'deal_id' => $dealData->Deal,
            'order_id' => $dealData->Order ?? null,
            'position_id' => $dealData->PositionID ?? $dealData->ExpertPositionID ?? null,
            'symbol' => $dealData->Symbol ?? '',
            'type' => $dealData->Type ?? 0,
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
        $account->update([
            'last_deal_sync_at' => now(),
            'last_sync_attempt_at' => now(),
        ]);

        Log::info("Updated deal sync status for account {$account->code}: {$status} (deals: {$dealsCount})");
    }

    public function failed(\Throwable $exception)
    {
        Log::error("DealSyncJob permanently failed: " . $exception->getMessage(), [
            'accounts' => collect($this->accounts)->pluck('code')->toArray(),
            'exception' => $exception->getTraceAsString()
        ]);
    }
}
