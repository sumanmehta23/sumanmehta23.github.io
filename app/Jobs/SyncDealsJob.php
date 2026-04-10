<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\Deal;
use App\Models\Trade;
use App\MT5\MTRetCode;
use App\Services\MT5RestAPIService;
use App\Services\QueueSafeMT5Service;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use function Symfony\Component\Clock\now;

class SyncDealsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $timeout = 600; // 10 minutes max per job
    public $tries = 2;
    public $maxExceptions = 2;
    public $uniqueFor = 300; // 5 min uniqueness per account set

    protected $accountIds;
    protected $maxPagesPerAccount;

    public function uniqueId(): string
    {
        return 'sync-deals-' . collect($this->accountIds)->sort()->join('-');
    }

    public function __construct(array $accountIds, int $maxPagesPerAccount = 20)
    {
        $this->accountIds = $accountIds;
        $this->maxPagesPerAccount = $maxPagesPerAccount;
        $this->onQueue('syncaccountstrades');
    }

    public function handle(): void
    {
        $jobStart = microtime(true);
        $totalDealsInserted = 0;
        $closedPositionsBatch = [];

        // ── Load accounts and build login → account map ──
        $accounts = Account::whereIn('id', $this->accountIds)->get()->keyBy('id');
        if ($accounts->isEmpty()) {
            return;
        }

        // Build per-account time windows (incremental sync)
        $accountWindows = [];
        foreach ($accounts as $acct) {
            $lastSync = $acct->deals_synced_to;
            $from = $lastSync
                ? Carbon::parse($lastSync)->subHour()->timestamp
                : Carbon::parse('2024-09-01')->timestamp;
            $to = Carbon::parse('2080-03-31')->timestamp;

            $accountWindows[$acct->id] = [
                'account' => $acct,
                'login' => (int) $acct->code,
                'from' => $from,
                'to' => $to,
            ];
        }

        // ── Try REST batch API first ──
        $restResult = $this->syncViaRestBatch($accountWindows);

        $totalDealsInserted += $restResult['inserted'];
        $closedPositionsBatch = array_merge($closedPositionsBatch, $restResult['closed_positions']);
        $failedAccountIds = $restResult['failed_account_ids'] ?? [];

        // ── Fallback: per-account socket API for any that failed ──
        if (!empty($failedAccountIds)) {
            Log::info('SyncDealsJob: Falling back to socket API', [
                'failed_accounts' => count($failedAccountIds),
                'failed_account_ids' => $failedAccountIds
            ]);
            $mt5 = app(QueueSafeMT5Service::class);
            foreach ($failedAccountIds as $accountId) {
                try {
                    $result = $this->syncAccountDealsViaSocket($mt5, $accountId);
                    $totalDealsInserted += $result['inserted'];
                    foreach ($result['closed_positions'] as $cp) {
                        $closedPositionsBatch[] = $cp;
                    }

                    if (count($closedPositionsBatch) >= 100) {
                        ProcessClosedDealCommissionJob::dispatch($closedPositionsBatch)
                            ->onQueue('distributeibcommission');
                        $closedPositionsBatch = [];
                    }
                } catch (Exception $e) {
                    Log::error("SyncDealsJob: Socket fallback failed for {$accountId}", [
                        'error' => $e->getMessage(),
                    ]);
                    Account::where('id', $accountId)->update([
                        'trade_sync_status' => 'error',
                        'sync_error' => substr($e->getMessage(), 0, 500),
                    ]);
                }
            }
        }

        // Dispatch remaining closed positions
        if (!empty($closedPositionsBatch)) {
            ProcessClosedDealCommissionJob::dispatch($closedPositionsBatch)
                ->onQueue('distributeibcommission');
        }

        Log::info("SyncDealsJob completed", [
            'accounts' => count($this->accountIds),
            'total_deals_inserted' => $totalDealsInserted,
            'rest_batch' => count($this->accountIds) - count($failedAccountIds),
            'socket_fallback' => count($failedAccountIds),
            'duration_seconds' => round(microtime(true) - $jobStart, 2),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  REST Batch Path
    // ─────────────────────────────────────────────────────────

    /**
     * Fetch deals for all accounts in one (or few) REST calls,
     * then upsert into DB and detect closed positions.
     */
    protected function syncViaRestBatch(array $accountWindows): array
    {
        $inserted = 0;
        $closedPositions = [];
        $failedAccountIds = [];

        try {
            $restService = app(MT5RestAPIService::class);
        } catch (Exception $e) {
            Log::warning('SyncDealsJob: REST service unavailable, all accounts will use socket', [
                'error' => $e->getMessage(),
            ]);
            return [
                'inserted' => 0,
                'closed_positions' => [],
                'failed_account_ids' => $failedAccountIds,
            ];
        }

        // Group accounts by similar time windows to minimize REST calls.
        // For simplicity, use the widest window (min $from, max $to).
        $globalFrom = PHP_INT_MAX;
        $globalTo = 0;
        $logins = [];
        $loginToAccountId = [];

        foreach ($accountWindows as $acctId => $w) {
            $globalFrom = min($globalFrom, $w['from']);
            $globalTo = max($globalTo, $w['to']);
            $logins[] = $w['login'];
            $loginToAccountId[$w['login']] = $acctId;
        }
        Log::info('SyncDealsJob: Fetching deals via REST batch', [
            'account_count' => count($accountWindows),
            'logins' => $accountWindows,
            'global_from' => Carbon::createFromTimestamp($globalFrom)->toDateTimeString(),
            'global_to' => Carbon::createFromTimestamp($globalTo)->toDateTimeString(),
        ]);
        $batchResult = $restService->getBatchDeals($logins, $globalFrom, $globalTo);

        $dealsByLogin = $batchResult['deals'];

        // Pre-load existing deal_ids for all accounts (one query)
        $allAccountIds = array_keys($accountWindows);
        $existingDealIds = Deal::whereIn('account_id', $allAccountIds)
            ->pluck('deal_id')
            ->flip()
            ->toArray();

        foreach ($accountWindows as $acctId => $w) {
            $login = (string) $w['login'];
            $account = $w['account'];
            $rawDeals = $dealsByLogin[$login] ?? null;

            if ($rawDeals === null) {
                // REST returned nothing for this login — mark for socket fallback
                $failedAccountIds[] = $acctId;
                continue;
            }

            // Per-account from filter: REST used global window, so trim deals
            // that are before this account's actual incremental cursor.
            $accountFrom = $w['from'];

            $result = $this->processRestDeals($rawDeals, $acctId, $account, $accountFrom, $existingDealIds);
            $inserted += $result['inserted'];
            $closedPositions = array_merge($closedPositions, $result['closed_positions']);

            // Sync trades table from newly inserted deals
            $this->syncTrades($acctId, $account, $result['new_deal_ids']);

            // Dispatch commission in batches of 100
            if (count($closedPositions) >= 100) {
                ProcessClosedDealCommissionJob::dispatch(array_splice($closedPositions, 0, 100))
                    ->onQueue('distributeibcommission');
            }
        }

        return [
            'inserted' => $inserted,
            'closed_positions' => $closedPositions,
            'failed_account_ids' => $failedAccountIds,
        ];
    }

    /**
     * Transform REST deal arrays into DB rows, upsert, and detect closes.
     */
    protected function processRestDeals(array $rawDeals, string $accountId, Account $account, int $accountFrom, array &$existingDealIds): array
    {
        $dealsToInsert = [];
        $closedPositions = [];
        $newDealIds = [];
        $latestTimeDone = null;

        foreach ($rawDeals as $deal) {
            $dealId = $deal['Deal'] ?? null;
            if (!$dealId) {
                continue;
            }

            // Only buy/sell (Action 0=buy, 1=sell)
            $action = (int) ($deal['Action'] ?? -1);
            if (!in_array($action, [0, 1])) {
                continue;
            }

            // Skip entries without symbol (deposits/withdrawals from MT5 API)
            $symbol = $deal['Symbol'] ?? '';
            if (empty(trim($symbol))) {
                continue;
            }

            $timeDone = Carbon::createFromTimestamp((int) $deal['Time']);

            // Always track latest time for cursor advancement (even for dupes)
            if (!$latestTimeDone || $timeDone->gt($latestTimeDone)) {
                $latestTimeDone = $timeDone;
            }

            // Skip already-synced deals
            if (isset($existingDealIds[$dealId])) {
                continue;
            }

            // Skip deals before this account's incremental cursor
            if ($timeDone->timestamp < $accountFrom) {
                continue;
            }

            $volume = ((int) ($deal['Volume'] ?? 0)) / 10000;
            $entry = (int) ($deal['Entry'] ?? 0);
            $positionId = (int) ($deal['PositionID'] ?? 0);

            $dealsToInsert[] = [
                'account_id' => $accountId,
                'deal_id' => $dealId,
                'order_id' => $deal['Order'] ?? null,
                'position_id' => $positionId,
                'symbol' => $deal['Symbol'] ?? '',
                'type' => $action,
                'action' => $action,
                'entry' => $entry,
                'volume' => $volume,
                'price' => (float) ($deal['Price'] ?? 0),
                'profit' => (float) ($deal['Profit'] ?? 0),
                'swap' => (float) ($deal['Storage'] ?? 0),
                'commission' => (float) ($deal['Commission'] ?? 0),
                'comment' => $deal['Comment'] ?? null,
                'reason' => $deal['Reason'] ?? null,
                'time_done' => $timeDone,
                'time_msc' => $deal['TimeMsc'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $existingDealIds[$dealId] = true;
            $newDealIds[] = $dealId;

            // Track close deals for commission
            if ($entry == 1 && $positionId > 0) {
                $closedPositions[] = [
                    'account_id' => $accountId,
                    'position_id' => $positionId,
                    'deal_id' => $dealId,
                    'order_id' => $deal['Order'] ?? null,
                    'symbol' => $deal['Symbol'] ?? '',
                    'volume' => $volume,
                    'time_done' => $timeDone->toDateTimeString(),
                ];
            }
        }

        if (!empty($dealsToInsert)) {
            // Chunk upserts to avoid packet-size limits
            foreach (array_chunk($dealsToInsert, 500) as $chunk) {
                Deal::upsert($chunk, ['deal_id'], [
                    'order_id',
                    'position_id',
                    'symbol',
                    'type',
                    'action',
                    'entry',
                    'volume',
                    'price',
                    'profit',
                    'swap',
                    'commission',
                    'comment',
                    'reason',
                    'time_done',
                    'time_msc',
                    'updated_at',
                ]);
            }
        }

        // Update sync cursor
        $syncUpdate = [
            'deals_last_fetch_at' => now(),
            'trade_sync_status' => 'success',
            'last_trade_sync_at' => now(),
        ];
        // if ($latestTimeDone) {
        //     $syncUpdate['deals_synced_to'] = $latestTimeDone;
        // }
        $syncUpdate['deals_synced_to'] = $latestTimeDone ?? now();
        if (!$account->deals_synced_from) {
            $syncUpdate['deals_synced_from'] = Carbon::parse('2024-09-01');
        }
        $syncUpdate['deals_sync_complete'] = true;
        Account::where('id', $accountId)->update($syncUpdate);
        Cache::forget("account:{$accountId}");

        return ['inserted' => count($dealsToInsert), 'closed_positions' => $closedPositions, 'new_deal_ids' => $newDealIds];
    }

    // ─────────────────────────────────────────────────────────
    //  Socket Fallback Path (per-account, paginated)
    // ─────────────────────────────────────────────────────────

    protected function syncAccountDealsViaSocket(QueueSafeMT5Service $mt5, string $accountId): array
    {
        $account = Cache::remember("account:{$accountId}", 600, fn() => Account::find($accountId));
        if (!$account) {
            return ['inserted' => 0, 'closed_positions' => []];
        }

        $login = $account->code;

        // INCREMENTAL: Use last sync timestamp or default start
        $lastSync = $account->deals_synced_to;
        if ($lastSync) {
            $from = Carbon::parse($lastSync)->subHour()->timestamp;
        } else {
            $from = Carbon::parse('2024-09-01')->timestamp;
        }
        $to = Carbon::parse('2080-03-31')->timestamp;

        // Get total deals count
        $total = 0;
        $errorCode = $mt5->executeOperation(function ($api) use ($login, $from, $to, &$total) {
            return $api->DealGetTotal($login, $from, $to, $total);
        });

        if ($errorCode != MTRetCode::MT_RET_OK) {
            if ($errorCode == MTRetCode::MT_RET_ERR_NOTFOUND) {
                $account->update(['deletion_type' => 'not_found_on_mt5']);
                $account->delete();
            }
            Log::warning("SyncDealsJob: DealGetTotal failed for {$login}", [
                'error_code' => $errorCode,
            ]);
            return ['inserted' => 0, 'closed_positions' => []];
        }

        if ($total == 0) {
            return ['inserted' => 0, 'closed_positions' => []];
        }

        // Pre-load existing deal_ids for this account to skip duplicates (O(1) lookup)
        $existingDealIds = Deal::where('account_id', $accountId)
            ->pluck('deal_id')
            ->flip()
            ->toArray();

        $pageSize = 100;
        $inserted = 0;
        $closedPositions = [];
        $newDealIds = [];
        $latestTimeDone = null;
        $pagesProcessed = 0;

        for ($offset = 0; $offset < $total; $offset += $pageSize) {
            if ($pagesProcessed >= $this->maxPagesPerAccount) {
                // Re-queue for continuation
                static::dispatch([$accountId], $this->maxPagesPerAccount)
                    ->onQueue('syncaccountstrades')
                    ->delay(now()->addSeconds(5));
                Log::info("SyncDealsJob: Page limit hit for {$login}, re-queuing", [
                    'pages_done' => $pagesProcessed,
                    'offset' => $offset,
                    'total' => $total,
                ]);
                break;
            }

            $deals = [];
            $errorCode = $mt5->executeOperation(function ($api) use ($login, $from, $to, $offset, $pageSize, &$deals) {
                return $api->DealGetPage($login, $from, $to, $offset, $pageSize, $deals);
            });

            if ($errorCode != MTRetCode::MT_RET_OK || empty($deals)) {
                break;
            }

            $dealsToInsert = [];
            foreach ($deals as $deal) {
                // Skip if already synced
                if (isset($existingDealIds[$deal->Deal])) {
                    continue;
                }

                // Only sync buy/sell deals (action 0=buy, 1=sell)
                if (!in_array($deal->Action, [0, 1])) {
                    continue;
                }

                // Skip entries without symbol (deposits/withdrawals from MT5 API)
                if (empty(trim($deal->Symbol ?? ''))) {
                    continue;
                }

                $timeDone = Carbon::createFromTimestamp($deal->Time);
                if (!$latestTimeDone || $timeDone->gt($latestTimeDone)) {
                    $latestTimeDone = $timeDone;
                }

                $dealsToInsert[] = [
                    'account_id' => $accountId,
                    'deal_id' => $deal->Deal,
                    'order_id' => $deal->Order,
                    'position_id' => $deal->PositionID,
                    'symbol' => $deal->Symbol,
                    'type' => $deal->Action, // 0=buy, 1=sell
                    'action' => $deal->Action,
                    'entry' => $deal->Entry, // 0=in, 1=out
                    'volume' => $deal->Volume / 10000, // MT5 volume is in lots * 10000
                    'price' => $deal->Price,
                    'profit' => $deal->Profit,
                    'swap' => $deal->Storage ?? 0,
                    'commission' => $deal->Commission ?? 0,
                    'comment' => $deal->Comment ?? null,
                    'reason' => $deal->Reason ?? null,
                    'time_done' => $timeDone,
                    'time_msc' => isset($deal->TimeMsc) ? $deal->TimeMsc : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Mark existing so subsequent pages don't re-insert
                $existingDealIds[$deal->Deal] = true;
                $newDealIds[] = $deal->Deal;

                // Track close deals for commission processing
                if ($deal->Entry == 1 && $deal->PositionID > 0) {
                    $closedPositions[] = [
                        'account_id' => $accountId,
                        'position_id' => $deal->PositionID,
                        'deal_id' => $deal->Deal,
                        'symbol' => $deal->Symbol,
                        'volume' => $deal->Volume / 10000,
                        'time_done' => $timeDone->toDateTimeString(),
                    ];
                }
            }

            if (!empty($dealsToInsert)) {
                // Use upsert to handle any race conditions
                Deal::upsert($dealsToInsert, ['deal_id'], [
                    'order_id',
                    'position_id',
                    'symbol',
                    'type',
                    'action',
                    'entry',
                    'volume',
                    'price',
                    'profit',
                    'swap',
                    'commission',
                    'comment',
                    'reason',
                    'time_done',
                    'time_msc',
                    'updated_at',
                ]);
                $inserted += count($dealsToInsert);
            }

            $pagesProcessed++;
        }

        // Update sync cursor
        $syncUpdate = [
            'deals_last_fetch_at' => now(),
            'trade_sync_status' => 'success',
            'last_trade_sync_at' => now(),
        ];
        if ($latestTimeDone) {
            $syncUpdate['deals_synced_to'] = $latestTimeDone;
        }
        if (!$account->deals_synced_from) {
            $syncUpdate['deals_synced_from'] = Carbon::parse('2024-09-01');
        }
        if ($pagesProcessed >= $this->maxPagesPerAccount) {
            // Partial sync — don't mark complete
        } elseif ($offset >= $total) {
            $syncUpdate['deals_sync_complete'] = true;
        }
        Account::where('id', $accountId)->update($syncUpdate);
        Cache::forget("account:{$accountId}");

        // Sync trades table from newly inserted deals
        $this->syncTrades($accountId, $account, $newDealIds);

        return ['inserted' => $inserted, 'closed_positions' => $closedPositions];
    }

    // ─────────────────────────────────────────────────────────
    //  Trades Table Sync (build position-level records from deals)
    // ─────────────────────────────────────────────────────────

    /**
     * Build/update trades table rows from newly inserted deals.
     * Groups deals by position_id: 1 deal = open trade, 2+ deals = closed trade.
     */
    protected function syncTrades(string $accountId, Account $account, array $newDealIds): void
    {
        if (empty($newDealIds)) {
            return;
        }

        // Get unique position_ids from the new deals
        $positionIds = Deal::where('account_id', $accountId)
            ->whereIn('deal_id', $newDealIds)
            ->where('position_id', '>', 0)
            ->distinct()
            ->pluck('position_id')
            ->toArray();

        if (empty($positionIds)) {
            return;
        }

        // Load ALL deals for these positions (need both open + close to build trades)
        $dealsByPosition = Deal::where('account_id', $accountId)
            ->whereIn('position_id', $positionIds)
            ->whereIn('action', [0, 1]) // buy/sell only
            ->orderBy('time_done', 'asc')
            ->get()
            ->groupBy('position_id');

        $tradesToUpsert = [];

        foreach ($dealsByPosition as $positionId => $deals) {
            if ($positionId == 0) {
                continue;
            }

            $openDeal = $deals->firstWhere('entry', 0);  // entry=in
            $closeDeal = $deals->firstWhere('entry', 1);  // entry=out

            if (!$openDeal) {
                continue;
            }

            $typeString = $openDeal->action == 0 ? 'buy' : 'sell';

            if ($closeDeal) {
                // Closed trade
                $tradesToUpsert[] = [
                    'account_id' => $accountId,
                    'code' => $account->code,
                    'order_id' => $openDeal->order_id,
                    'position_id' => $positionId,
                    'symbol' => $openDeal->symbol,
                    'type' => $typeString,
                    'volume' => $openDeal->volume,
                    'volume_ext' => 0,
                    'open_price' => $openDeal->price,
                    'close_price' => $closeDeal->price,
                    'profit' => $closeDeal->profit,
                    'swap' => $closeDeal->swap,
                    'commission' => $openDeal->commission + $closeDeal->commission,
                    'sl' => 0,
                    'tp' => 0,
                    'comment' => $openDeal->comment,
                    'status' => 'closed',
                    'open_time' => $openDeal->time_done,
                    'close_time' => $closeDeal->time_done,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                // Open trade (no close deal yet)
                $tradesToUpsert[] = [
                    'account_id' => $accountId,
                    'code' => $account->code,
                    'order_id' => $openDeal->order_id,
                    'position_id' => $positionId,
                    'symbol' => $openDeal->symbol,
                    'type' => $typeString,
                    'volume' => $openDeal->volume,
                    'volume_ext' => 0,
                    'open_price' => $openDeal->price,
                    'close_price' => null,
                    'profit' => 0,
                    'swap' => 0,
                    'commission' => $openDeal->commission,
                    'sl' => 0,
                    'tp' => 0,
                    'comment' => $openDeal->comment,
                    'status' => 'open',
                    'open_time' => $openDeal->time_done,
                    'close_time' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (count($tradesToUpsert) >= 500) {
                Trade::upsert($tradesToUpsert, ['account_id', 'position_id'], [
                    'order_id',
                    'symbol',
                    'type',
                    'volume',
                    'open_price',
                    'close_price',
                    'profit',
                    'swap',
                    'commission',
                    'comment',
                    'status',
                    'open_time',
                    'close_time',
                    'updated_at',
                ]);
                $tradesToUpsert = [];
            }
        }

        if (!empty($tradesToUpsert)) {
            Trade::upsert($tradesToUpsert, ['account_id', 'position_id'], [
                'order_id',
                'symbol',
                'type',
                'volume',
                'open_price',
                'close_price',
                'profit',
                'swap',
                'commission',
                'comment',
                'status',
                'open_time',
                'close_time',
                'updated_at',
            ]);
        }
    }
}
