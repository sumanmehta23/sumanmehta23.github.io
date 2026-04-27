<?php

namespace App\Console\Commands;

use App\Jobs\ProcessClosedDealCommissionJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackfillDealCommissions extends Command
{
    protected $signature = 'app:backfill-deal-commissions
        {--batch-size=100 : Positions per commission job}
        {--limit=1000 : Number of positions to process }
        {--account-batch=10 : Number of accounts to process per iteration (incremental mode)}
        {--dry-run : Show counts only, do not dispatch jobs}
        {--sync : Run jobs synchronously instead of dispatching to queue}
        {--code= : Only backfill for a specific account code}
        {--account-id= : Only backfill for a specific account UUID}';

    protected $description = 'Find closed deals missing IB commission and dispatch ProcessClosedDealCommissionJob to process them';

    public function handle(): int
    {
        $batchSize = max(1, (int) $this->option('batch-size'));
        $accountBatch = max(1, (int) $this->option('account-batch'));
        $dryRun = $this->option('dry-run');
        $sync = $this->option('sync');
        $code = $this->option('code');
        $accountId = $this->option('account-id');
        $limit = max(1, (int) $this->option('limit'));
        $this->info('Scanning for closed deals without IB commission...');

        // Use fast subtraction approach instead of NOT EXISTS (which is slow)
        // Get: (1) all relevant deals, (2) deals with commissions, then compute diff in PHP

        if ($code) {
            // Optimized path: fetch by account code first
            $account = DB::table('accounts')
                ->where('code', $code)
                ->whereNull('deleted_at')
                ->where('demo', 0)
                ->first(['id']);

            if (!$account) {
                $this->error("Account code {$code} not found.");
                return self::FAILURE;
            }

            $accountId = $account->id;

            // Get all close deals for this account
            $allDeals = DB::select("
                SELECT d.id, d.account_id, d.position_id, d.deal_id, d.order_id, d.symbol, d.volume, d.time_done, a.code
                FROM deals d
                INNER JOIN accounts a ON a.id = d.account_id AND a.deleted_at IS NULL AND a.demo = 0
                INNER JOIN aspnetusers u ON u.id = a.user_id AND u.status = 1 AND u.ib1 IS NOT NULL
                WHERE d.account_id = ?
                  AND d.entry = 1
                  AND d.action IN (0, 1)
                  AND d.position_id > 0
                  AND d.symbol IS NOT NULL
                  AND d.symbol != ''
                ORDER BY d.time_done
            ", [$accountId]);

            // Get deals WITH commissions
            $dealsWithComm = DB::select("
                SELECT DISTINCT d.id
                FROM deals d
                INNER JOIN ib1_commission c FORCE INDEX (ib1_commission_order_id_code_index)
                    ON c.order_id = d.order_id
                INNER JOIN accounts a ON a.id = d.account_id AND a.code = c.code
                WHERE d.account_id = ?
                  AND d.entry = 1
                  AND d.action IN (0, 1)
                  AND d.position_id > 0
                  AND c.deleted_at IS NULL
            ", [$accountId]);

            $commissionIds = collect($dealsWithComm)->pluck('id')->flip()->toArray();

            // Compute missing
            $missingDeals = collect($allDeals)->filter(function ($d) use ($commissionIds) {
                return !isset($commissionIds[$d->id]);
            })->values()->toArray();

            // Now filter by duration >= 10 seconds
            // Fetch open deals to check duration
            $positionIds = collect($allDeals)->pluck('position_id')->toArray();
            $openDeals = DB::select("
                SELECT d.position_id, d.time_done
                FROM deals d
                WHERE d.account_id = ?
                  AND d.entry = 0
                  AND d.action IN (0, 1)
                  AND d.position_id IN (" . implode(',', array_fill(0, count($positionIds), '?')) . ")
                ORDER BY d.time_done ASC
            ", array_merge([$accountId], $positionIds));

            $openTimes = collect($openDeals)->keyBy('position_id')->map(fn($o) => strtotime($o->time_done))->toArray();

            // Filter out short trades
            $missingDeals = array_filter($missingDeals, function ($d) use ($openTimes) {
                if (!isset($openTimes[$d->position_id])) {
                    return false; // No open deal found, skip
                }
                $duration = abs(strtotime($d->time_done) - $openTimes[$d->position_id]);
                return $duration >= 10; // Must be at least 10 seconds
            });

            // Maintain original keys for slice
            $missingDeals = array_slice(array_values($missingDeals), 0, $limit);
        } else {
            // General path: Process accounts incrementally to avoid expensive global NOT EXISTS
            // Fetch active accounts in batches and process each batch
            $allMissingDeals = [];
            $accountIds = DB::table('accounts as a')
                ->join('aspnetusers as u', 'u.id', '=', 'a.user_id')
                ->where('a.deleted_at', null)
                ->where('a.demo', 0)
                ->where('u.status', 1)
                ->whereNotNull('u.ib1')
                ->select('a.id')
                ->orderBy('a.id')
                ->pluck('a.id')
                ->toArray();

            $this->info("Processing " . count($accountIds) . " accounts in batches of {$accountBatch}...");

            foreach (array_chunk($accountIds, $accountBatch) as $batchAccounts) {
                foreach ($batchAccounts as $acctId) {
                    // Get all close deals for this account
                    $allDeals = DB::select("
                        SELECT d.id, d.account_id, d.position_id, d.deal_id, d.order_id, d.symbol, d.volume, d.time_done, a.code
                        FROM deals d
                        INNER JOIN accounts a ON a.id = d.account_id AND a.deleted_at IS NULL AND a.demo = 0
                        INNER JOIN aspnetusers u ON u.id = a.user_id AND u.status = 1 AND u.ib1 IS NOT NULL
                        WHERE d.account_id = ?
                          AND d.entry = 1
                          AND d.action IN (0, 1)
                          AND d.position_id > 0
                          AND d.symbol IS NOT NULL
                          AND d.symbol != ''
                        ORDER BY d.time_done
                    ", [$acctId]);

                    // Get deals WITH commissions
                    $dealsWithComm = DB::select("
                        SELECT DISTINCT d.id
                        FROM deals d
                        INNER JOIN ib1_commission c FORCE INDEX (ib1_commission_order_id_code_index)
                            ON c.order_id = d.order_id
                        INNER JOIN accounts a ON a.id = d.account_id AND a.code = c.code
                        WHERE d.account_id = ?
                          AND d.entry = 1
                          AND d.action IN (0, 1)
                          AND d.position_id > 0
                          AND c.deleted_at IS NULL
                    ", [$acctId]);

                    $commissionIds = collect($dealsWithComm)->pluck('id')->flip()->toArray();

                    // Compute missing
                    $missingDealsForAccount = collect($allDeals)->filter(function ($d) use ($commissionIds) {
                        return !isset($commissionIds[$d->id]);
                    })->values()->toArray();

                    if (count($missingDealsForAccount) > 0) {
                        // Filter by duration >= 10 seconds
                        // Fetch open deals to check duration
                        $positionIds = collect($allDeals)->pluck('position_id')->toArray();
                        if (count($positionIds) > 0) {
                            $openDeals = DB::select("
                                SELECT d.position_id, d.time_done
                                FROM deals d
                                WHERE d.account_id = ?
                                  AND d.entry = 0
                                  AND d.action IN (0, 1)
                                  AND d.position_id IN (" . implode(',', array_fill(0, count($positionIds), '?')) . ")
                                ORDER BY d.time_done ASC
                            ", array_merge([$acctId], $positionIds));

                            $openTimes = collect($openDeals)->keyBy('position_id')->map(fn($o) => strtotime($o->time_done))->toArray();

                            // Filter out short trades
                            $missingDealsForAccount = array_filter($missingDealsForAccount, function ($d) use ($openTimes) {
                                if (!isset($openTimes[$d->position_id])) {
                                    return false; // No open deal found, skip
                                }
                                $duration = abs(strtotime($d->time_done) - $openTimes[$d->position_id]);
                                return $duration >= 10; // Must be at least 10 seconds
                            });
                        }

                        $allMissingDeals = array_merge($allMissingDeals, array_values($missingDealsForAccount));

                        // Early exit if we've hit the limit
                        if (count($allMissingDeals) >= $limit) {
                            break 2; // Break out of both loops
                        }
                    }
                }
            }

            $missingDeals = array_slice($allMissingDeals, 0, $limit);
        }

        # Convert results to a queryable collection for consistency with rest of code
        $query = collect($missingDeals);

        $totalMissing = $query->count();
        $this->info("Found {$totalMissing} closed deals without commission.");

        if ($totalMissing === 0) {
            $this->info('Nothing to backfill.');
            return self::SUCCESS;
        }

        // Also check: commissions with status=0 that have no wallets
        $stuckCommissions = DB::table('ib1_commission as c')
            ->leftJoin('ib_wallet as w', 'w.ib1_commission_id', '=', 'c.id')
            ->whereNull('w.id')
            ->where('c.status', 0)
            ->whereNull('c.deleted_at')
            ->count();

        $this->info("Also found {$stuckCommissions} commissions with status=0 and no wallets (will be picked up during reprocessing).");

        if ($dryRun) {
            $this->warn('Dry run mode — no jobs dispatched.');

            // Show breakdown by account
            $breakdown = $query->groupBy('code')
                ->map(fn($group) => ['code' => $group->first()->code ?? 'unknown', 'cnt' => $group->count()])
                ->values()
                ->sortByDesc('cnt')
                ->take(20);

            $this->table(['Account Code', 'Missing Commissions'], $breakdown->map(fn($r) => [$r['code'], $r['cnt']])->toArray());
            return self::SUCCESS;
        }

        $mode = $sync ? 'Running' : 'Dispatching';
        $this->info("{$mode} ProcessClosedDealCommissionJob in batches of {$batchSize}...");

        $jobsDispatched = 0;
        $positionsQueued = 0;

        // Chunk collection manually
        foreach ($query->chunk($batchSize) as $rows) {
            $positions = $rows->map(fn($r) => [
                'account_id' => $r->account_id,
                'position_id' => $r->position_id,
                'deal_id' => $r->deal_id,
                'order_id' => $r->order_id, // MT5 Order ID - used for matching commissions
                'symbol' => $r->symbol,
                'volume' => (float) $r->volume,
                'time_done' => (string) $r->time_done,
            ])->toArray();

            if ($sync) {
                ProcessClosedDealCommissionJob::dispatchSync($positions);
            } else {
                ProcessClosedDealCommissionJob::dispatch($positions)
                    ->onQueue('distributeibcommission');
            }

            $jobsDispatched++;
            $positionsQueued += count($positions);
        }

        $label = $sync ? 'Processed' : 'Dispatched';
        $this->info("{$label} {$jobsDispatched} jobs for {$positionsQueued} positions.");

        Log::info('BackfillDealCommissions completed', [
            'total_missing' => $totalMissing,
            'stuck_commissions' => $stuckCommissions,
            'jobs_dispatched' => $jobsDispatched,
            'positions_queued' => $positionsQueued,
        ]);

        return self::SUCCESS;
    }
}
