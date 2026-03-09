<?php

namespace App\Jobs;

use App\Events\TradeOpenedEvent;
use App\Models\Trade;
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
 * Position-Based Sync Job
 *
 * This job uses MT5 Position APIs directly to sync trades:
 * 1. PositionGetTotal - Get total number of positions
 * 2. PositionGetPage - Get positions with pagination
 * 3. Direct position data without complex deal reconstruction
 * 4. Much more efficient than order+deal combination
 */
class PositionSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $accounts;
    protected $syncOpenOnly;
    protected $syncClosedSince;
    public $timeout = 300;
    public $tries = 2;

    public function __construct(array $accounts, bool $syncOpenOnly = false, Carbon $syncClosedSince = null)
    {
        // Convert Account models to serializable array format
        $this->accounts = collect($accounts)->map(function ($account) {
            return [
                'id' => $account->id,
                'code' => $account->code,
                'demo' => $account->demo,
                'last_position_sync_at' => $account->last_position_sync_at ?? null,
            ];
        })->toArray();

        $this->syncOpenOnly = $syncOpenOnly;
        $this->syncClosedSince = $syncClosedSince;

        // Set timeout based on number of accounts
        $this->timeout = max(300, count($accounts) * 60 + 120);
    }

    public function handle(UniversalMT5Service $mt5Service)
    {
        $jobStartTime = microtime(true);
        $accountCodes = collect($this->accounts)->pluck('code')->join(', ');
        $accountCount = count($this->accounts);

        Log::info("Starting PositionSyncJob for {$accountCount} accounts: {$accountCodes}");

        $results = [
            'processed' => 0,
            'success' => 0,
            'errors' => 0,
            'no_changes' => 0,
            'skipped' => 0,
            'positions_synced' => 0,
            'open_positions' => 0,
            'closed_positions' => 0
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

                    $result = $this->syncAccountPositions($api, $account);

                    $results[$result['status']]++;
                    $results['positions_synced'] += $result['positions_count'];
                    $results['open_positions'] += $result['open_count'];
                    $results['closed_positions'] += $result['closed_count'];
                    $results['processed']++;

                    $accountTime = round((microtime(true) - $accountIterationStart) * 1000, 2);
                    Log::info("Account {$account->code}: {$result['status']} - {$result['positions_count']} positions ({$accountTime}ms)");
                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['processed']++;
                    Log::error("Error syncing positions for account {$accountData['code']}: " . $e->getMessage());
                    $mt5Service->reportError();
                }

                if ($index < count($this->accounts) - 1) {
                    usleep(100000); // 0.1 second delay between accounts
                }
            }
        } catch (\Exception $e) {
            Log::error("PositionSyncJob failed: " . $e->getMessage());
            throw $e;
        }

        $totalJobTime = round((microtime(true) - $jobStartTime) * 1000, 2);
        Log::info("PositionSyncJob COMPLETE: {$results['processed']} accounts in {$totalJobTime}ms. " .
            "Success: {$results['success']}, Errors: {$results['errors']}, " .
            "Total positions: {$results['positions_synced']} (Open: {$results['open_positions']}, Closed: {$results['closed_positions']})");
    }

    protected function syncAccountPositions($api, Account $account): array
    {
        $accountStartTime = microtime(true);
        $timings = [];

        if (!$account->code) {
            return ['status' => 'errors', 'positions_count' => 0, 'open_count' => 0, 'closed_count' => 0];
        }

        // Phase 1: MT5 User Check
        $phaseStart = microtime(true);
        $mt5_user = null;
        $error_code = $api->UserGet($account->code, $mt5_user);
        $timings['mt5_user_check'] = round((microtime(true) - $phaseStart) * 1000, 2);

        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::warning("MT5 user not found for account4 {$account->code}");
            return ['status' => 'errors', 'positions_count' => 0, 'open_count' => 0, 'closed_count' => 0];
        }

        try {
            $login = $account->code;
            $totalPositions = 0;

            // Phase 2: Get Open Positions using PositionGetTotal
            $phaseStart = microtime(true);
            $error_code = $api->PositionGetTotal($login, $totalPositions);
            $timings['mt5_position_total'] = round((microtime(true) - $phaseStart) * 1000, 2);

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("Failed to get total positions for account {$account->code}: " . MTRetCode::GetError($error_code));
                return ['status' => 'errors', 'positions_count' => 0, 'open_count' => 0, 'closed_count' => 0];
            }

            Log::info("Account {$account->code} has {$totalPositions} open positions");

            $openPositionsCount = 0;
            $closedPositionsCount = 0;
            $totalSynced = 0;

            // Phase 3: Sync Open Positions
            if ($totalPositions > 0) {
                $openResult = $this->syncOpenPositions($api, $account, $totalPositions, $timings);
                $openPositionsCount = $openResult['synced_count'];
                $totalSynced += $openPositionsCount;
            }

            // Phase 4: Sync Closed Positions (if not open-only mode)
            if (!$this->syncOpenOnly) {
                $closedResult = $this->syncClosedPositions($api, $account, $timings);
                $closedPositionsCount = $closedResult['synced_count'];
                $totalSynced += $closedPositionsCount;
            }

            // Phase 5: Update Account Status
            $this->updateAccountPositionSyncStatus($account, 'success', $totalSynced);

            $totalTime = round((microtime(true) - $accountStartTime) * 1000, 2);
            Log::info("POSITION_SYNC[{$account->code}]: {$totalTime}ms total | " .
                "Open: {$openPositionsCount}, Closed: {$closedPositionsCount}, Total: {$totalSynced} | " .
                "Breakdown: " . json_encode($timings));

            return [
                'status' => 'success',
                'positions_count' => $totalSynced,
                'open_count' => $openPositionsCount,
                'closed_count' => $closedPositionsCount
            ];
        } catch (\Exception $e) {
            Log::error("Error syncing positions for account {$account->code}: " . $e->getMessage());
            return ['status' => 'errors', 'positions_count' => 0, 'open_count' => 0, 'closed_count' => 0];
        }
    }

    protected function syncOpenPositions($api, Account $account, int $totalPositions, array &$timings): array
    {
        $phaseStart = microtime(true);
        $allPositions = [];
        $requestedPageSize = 1000;
        $pageCount = 0;

        Log::info("Syncing {$totalPositions} open positions for account {$account->code}");

        while (count($allPositions) < $totalPositions) {
            $startIndex = count($allPositions);
            $remainingPositions = $totalPositions - $startIndex;
            $pagePositions = [];
            $currentPageSize = min($requestedPageSize, $remainingPositions);

            $pageStart = microtime(true);
            $error_code = $api->PositionGetPage($account->code, $startIndex, $currentPageSize, $pagePositions);
            $pageTime = round((microtime(true) - $pageStart) * 1000, 2);
            $pageCount++;

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 PositionGetPage error for account {$account->code} page {$pageCount}: " . MTRetCode::GetError($error_code));
                break;
            }

            $allPositions = array_merge($allPositions, $pagePositions);

            if (count($pagePositions) === 0) {
                break;
            }

            if (count($allPositions) < $totalPositions) {
                usleep(50000); // 0.05 second delay between pages
            }
        }

        $timings['mt5_open_positions'] = round((microtime(true) - $phaseStart) * 1000, 2);

        // Process and store open positions
        $syncedCount = $this->processPositions($account, $allPositions, 'open');

        return ['synced_count' => $syncedCount];
    }

    protected function syncClosedPositions($api, Account $account, array &$timings): array
    {
        // For closed positions, we need to use HistoryGetTotal and HistoryGetPage
        // but with position-focused filtering
        $phaseStart = microtime(true);

        $fromDate = $this->syncClosedSince ?
            $this->syncClosedSince->format('F d, Y') :
            now()->subDays(7)->format('F d, Y');
        $toDate = now()->addHours(4)->format('F d, Y');

        $totalHistory = 0;
        $error_code = $api->HistoryGetTotal($account->code, $fromDate, $toDate, $totalHistory);

        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::error("Failed to get history total for closed positions: " . MTRetCode::GetError($error_code));
            return ['synced_count' => 0];
        }

        Log::info("Account {$account->code} has {$totalHistory} history items for closed positions");

        $allHistory = [];
        $requestedPageSize = 1000;

        while (count($allHistory) < $totalHistory) {
            $startIndex = count($allHistory);
            $remainingHistory = $totalHistory - $startIndex;
            $pageHistory = [];
            $currentPageSize = min($requestedPageSize, $remainingHistory);

            $error_code = $api->HistoryGetPage($account->code, $fromDate, $toDate, $startIndex, $currentPageSize, $pageHistory);

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("MT5 HistoryGetPage error for closed positions: " . MTRetCode::GetError($error_code));
                break;
            }

            $allHistory = array_merge($allHistory, $pageHistory);

            if (count($pageHistory) === 0) {
                break;
            }

            if (count($allHistory) < $totalHistory) {
                usleep(50000);
            }
        }

        $timings['mt5_closed_positions'] = round((microtime(true) - $phaseStart) * 1000, 2);

        // Filter and process closed positions from history
        $closedPositions = $this->extractClosedPositionsFromHistory($allHistory);
        $syncedCount = $this->processPositions($account, $closedPositions, 'closed');

        return ['synced_count' => $syncedCount];
    }

    protected function extractClosedPositionsFromHistory(array $historyItems): array
    {
        // Group history items by position to identify closed positions
        $positionGroups = [];

        foreach ($historyItems as $item) {
            $positionId = $item->ExpertPositionID ?? $item->PositionID ?? null;
            if ($positionId) {
                if (!isset($positionGroups[$positionId])) {
                    $positionGroups[$positionId] = [];
                }
                $positionGroups[$positionId][] = $item;
            }
        }

        $closedPositions = [];
        foreach ($positionGroups as $positionId => $items) {
            // Determine if this position is closed based on the items
            $closedPosition = $this->reconstructClosedPosition($positionId, $items);
            if ($closedPosition) {
                $closedPositions[] = $closedPosition;
            }
        }

        return $closedPositions;
    }

    protected function reconstructClosedPosition(string $positionId, array $items): ?object
    {
        if (empty($items)) {
            return null;
        }

        // Sort items by time
        usort($items, function ($a, $b) {
            return ($a->TimeDone ?? 0) <=> ($b->TimeDone ?? 0);
        });

        $openItem = $items[0];
        $closeItem = end($items);

        // Create a position object similar to what PositionGetPage would return
        $position = new \stdClass();
        $position->Position = $positionId;
        $position->Symbol = $openItem->Symbol ?? '';
        $position->Type = $openItem->Type ?? 0;
        $position->Volume = $openItem->VolumeInitial ?? 0;
        $position->VolumeExt = $openItem->VolumeInitialExt ?? 0;
        $position->PriceOpen = $openItem->PriceCurrent ?? 0;
        $position->PriceCurrent = $closeItem->PriceCurrent ?? $position->PriceOpen;
        $position->TimeCreate = $openItem->TimeDone ?? time();
        $position->TimeUpdate = $closeItem->TimeDone ?? $position->TimeCreate;
        $position->Comment = $openItem->Comment ?? '';
        $position->Magic = $openItem->Magic ?? 0;
        $position->Profit = 0; // Will be calculated from deals if needed
        $position->Storage = 0;
        $position->RateMargin = 1;
        $position->ExpertPositionID = $positionId;

        // Calculate profit if possible
        if (count($items) >= 2) {
            $multiplier = $position->Type ? -1 : 1;
            $priceDiff = $position->PriceCurrent - $position->PriceOpen;
            $volumeInLots = $position->VolumeExt / 100000000;
            $contractSize = 100000; // Default, should be from symbol info

            $position->Profit = round($priceDiff * $volumeInLots * $contractSize * $multiplier, 2);
        }

        return $position;
    }

    protected function processPositions(Account $account, array $positions, string $status): int
    {
        $positionsToUpsert = [];
        $syncedCount = 0;

        foreach ($positions as $positionData) {
            // Skip invalid positions
            if (!$positionData || !isset($positionData->Position) || empty($positionData->Position)) {
                continue;
            }

            $tradeData = $this->prepareTradeFromPosition($account, $positionData, $status);
            if ($tradeData) {
                $positionsToUpsert[] = $tradeData;
                $syncedCount++;

                // Batch insert for performance
                if (count($positionsToUpsert) >= 100) {
                    $this->processBatch($positionsToUpsert);
                    $this->fireTradeOpenedEventsForBatch($account, $positionsToUpsert);
                    $positionsToUpsert = [];
                }
            }
        }

        // Insert remaining positions
        if (!empty($positionsToUpsert)) {
            $this->processBatch($positionsToUpsert);
            $this->fireTradeOpenedEventsForBatch($account, $positionsToUpsert);
        }

        return $syncedCount;
    }

    /**
     * Fire TradeOpenedEvent for each open trade in the batch (upsert does not fire model events).
     */
    protected function fireTradeOpenedEventsForBatch(Account $account, array $tradesToUpsert): void
    {
        $openPositionIds = collect($tradesToUpsert)
            ->filter(fn ($t) => ($t['status'] ?? '') === 'open')
            ->pluck('position_id')
            ->unique()
            ->values();

        if ($openPositionIds->isEmpty()) {
            return;
        }

        $user = $account->user;
        if (!$user || empty($user->email)) {
            return;
        }

        $trades = Trade::where('account_id', $account->id)
            ->whereIn('position_id', $openPositionIds->all())
            ->where('status', 'open')
            ->get();

        foreach ($trades as $trade) {
            event(new TradeOpenedEvent($user, $trade));
        }
    }

    protected function prepareTradeFromPosition(Account $account, $positionData, string $status): ?array
    {
        $positionId = $positionData->Position ?? $positionData->ExpertPositionID ?? null;

        if (empty($positionId) || $positionId == 0 || $positionId === '0') {
            Log::warning("Invalid position ID for account {$account->code}: " . ($positionId ?? 'null'));
            return null;
        }

        $tradeData = [
            'account_id' => $account->id,
            'position_id' => $positionId,
            'order_id' => $positionData->Order ?? null,
            'symbol' => $positionData->Symbol ?? '',
            'type' => ($positionData->Type ?? 0) ? 'sell' : 'buy',
            'volume' => ($positionData->Volume ?? 0) / 10000,
            'volume_ext' => $positionData->VolumeExt ?? ($positionData->Volume ?? 0),
            'open_price' => $positionData->PriceOpen ?? $positionData->PriceCurrent ?? 0,
            'open_time' => date('Y-m-d H:i:s', $positionData->TimeCreate ?? $positionData->TimeDone ?? time()),
            'profit' => $positionData->Profit ?? 0,
            'swap' => $positionData->Storage ?? 0,
            'commission' => $positionData->Commission ?? 0,
            'status' => $status,
            'code' => $account->code,
            'comment' => $positionData->Comment ?? '',
            'magic' => $positionData->Magic ?? 0,
            'sl' => $positionData->PriceSL ?? 0,
            'tp' => $positionData->PriceTP ?? 0,
            'updated_at' => now(),
            'created_at' => now(),
        ];

        // Add close data for closed positions
        if ($status === 'closed') {
            $tradeData['close_price'] = $positionData->PriceCurrent ?? $tradeData['open_price'];
            $tradeData['close_time'] = date('Y-m-d H:i:s', $positionData->TimeUpdate ?? $positionData->TimeCreate ?? time());
        } else {
            $tradeData['close_price'] = null;
            $tradeData['close_time'] = null;
        }

        return $tradeData;
    }

    protected function processBatch(array $trades)
    {
        try {
            // Filter out invalid trades
            $validTrades = array_filter($trades, function ($trade) {
                return !empty($trade['position_id']) && $trade['position_id'] != 0 && $trade['position_id'] !== '0';
            });

            if (!empty($validTrades)) {
                Trade::upsert(
                    $validTrades,
                    ['account_id', 'position_id'],
                    ['close_price', 'close_time', 'status', 'profit', 'swap', 'commission', 'volume', 'volume_ext', 'type', 'updated_at']
                );
            }
        } catch (\Exception $e) {
            Log::error("Error processing position batch: " . $e->getMessage());
            throw $e;
        }
    }

    protected function updateAccountPositionSyncStatus(Account $account, string $status, int $positionsCount = 0): void
    {
        $account->update([
            'last_position_sync_at' => now(),
            'last_sync_attempt_at' => now(),
        ]);

        Log::info("Updated position sync status for account {$account->code}: {$status} (positions: {$positionsCount})");
    }

    public function failed(\Throwable $exception)
    {
        Log::error("PositionSyncJob permanently failed: " . $exception->getMessage(), [
            'accounts' => collect($this->accounts)->pluck('code')->toArray(),
            'exception' => $exception->getTraceAsString()
        ]);
    }
}
