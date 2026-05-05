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
        {--code=* : Only backfill for specific account code(s)}
        {--account-id= : Only backfill for a specific account UUID}';

    protected $description = 'Find closed deals missing IB commission and dispatch ProcessClosedDealCommissionJob to process them';

    public function handle(): int
    {
        $batchSize = max(1, (int) $this->option('batch-size'));
        $accountBatch = max(1, (int) $this->option('account-batch'));
        $dryRun = $this->option('dry-run');
        $sync = $this->option('sync');
        $codes = $this->normalizeCodes((array) $this->option('code'));
        $accountId = $this->option('account-id');
        $limit = max(1, (int) $this->option('limit'));
        $this->info('Scanning for closed deals without IB commission...');

        // Use fast subtraction approach instead of NOT EXISTS (which is slow)
        // Get: (1) all relevant deals, (2) deals with commissions, then compute diff in PHP
        $targetAccounts = $this->resolveTargetAccounts($codes, $accountId);

        if ($codes !== []) {
            $foundCodes = $targetAccounts->pluck('code')->unique()->all();
            $missingCodes = array_values(array_diff($codes, $foundCodes));

            if ($missingCodes !== []) {
                $this->warn('Skipping unknown or ineligible account code(s): ' . implode(', ', $missingCodes));
            }
        }

        if (($codes !== [] || $accountId) && $targetAccounts->isEmpty()) {
            $this->error('No eligible accounts found for the provided filters.');
            return self::FAILURE;
        }

        $accountIds = $targetAccounts->pluck('id')->toArray();
        $missingDeals = $this->collectMissingDeals($accountIds, $accountBatch, $limit);

        // Convert results to a queryable collection for consistency with rest of code
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
            'code_filters' => $codes,
            'account_id_filter' => $accountId,
            'total_missing' => $totalMissing,
            'stuck_commissions' => $stuckCommissions,
            'jobs_dispatched' => $jobsDispatched,
            'positions_queued' => $positionsQueued,
        ]);

        return self::SUCCESS;
    }

    protected function normalizeCodes(array $codes): array
    {
        return collect($codes)
            ->flatMap(fn($code) => explode(',', (string) $code))
            ->map(fn($code) => trim($code))
            ->filter(fn($code) => $code !== '')
            ->unique()
            ->values()
            ->all();
    }

    protected function resolveTargetAccounts(array $codes, ?string $accountId)
    {
        $query = $this->eligibleAccountsQuery()
            ->select('a.id', 'a.code');

        if ($codes !== []) {
            $query->whereIn('a.code', $codes);
        }

        if ($accountId) {
            $query->where('a.id', $accountId);
        }

        $accounts = $query->orderBy('a.id')->get();

        if ($codes === []) {
            return $accounts;
        }

        $codeOrder = array_flip($codes);

        return $accounts
            ->sortBy(fn($account) => $codeOrder[$account->code] ?? PHP_INT_MAX)
            ->values();
    }

    protected function eligibleAccountsQuery()
    {
        return DB::table('accounts as a')
            ->join('aspnetusers as u', 'u.id', '=', 'a.user_id')
            ->whereNull('a.deleted_at')
            ->where('a.demo', 0)
            ->where('u.status', 1)
            ->whereNotNull('u.ib1');
    }

    protected function collectMissingDeals(array $accountIds, int $accountBatch, int $limit): array
    {
        $allMissingDeals = [];

        $this->info('Processing ' . count($accountIds) . " accounts in batches of {$accountBatch}...");

        foreach (array_chunk($accountIds, $accountBatch) as $batchAccounts) {
            foreach ($batchAccounts as $acctId) {
                $missingDealsForAccount = $this->findMissingDealsForAccount($acctId);

                if ($missingDealsForAccount === []) {
                    continue;
                }

                $allMissingDeals = array_merge($allMissingDeals, $missingDealsForAccount);

                if (count($allMissingDeals) >= $limit) {
                    return array_slice($allMissingDeals, 0, $limit);
                }
            }
        }

        return array_slice($allMissingDeals, 0, $limit);
    }

    protected function findMissingDealsForAccount(string $accountId): array
    {
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

        if ($allDeals === []) {
            return [];
        }

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

        $missingDeals = collect($allDeals)->filter(function ($deal) use ($commissionIds) {
            return !isset($commissionIds[$deal->id]);
        })->values()->toArray();

        if ($missingDeals === []) {
            return [];
        }

        $positionIds = collect($missingDeals)
            ->pluck('position_id')
            ->unique()
            ->values()
            ->toArray();

        if ($positionIds === []) {
            return [];
        }

        $openDeals = DB::select("
            SELECT d.position_id, d.time_done
            FROM deals d
            WHERE d.account_id = ?
              AND d.entry = 0
              AND d.action IN (0, 1)
              AND d.position_id IN (" . implode(',', array_fill(0, count($positionIds), '?')) . ")
            ORDER BY d.time_done ASC
        ", array_merge([$accountId], $positionIds));

        $openTimes = collect($openDeals)
            ->keyBy('position_id')
            ->map(fn($deal) => strtotime($deal->time_done))
            ->toArray();

        return array_values(array_filter($missingDeals, function ($deal) use ($openTimes) {
            if (!isset($openTimes[$deal->position_id])) {
                return false;
            }

            $duration = abs(strtotime($deal->time_done) - $openTimes[$deal->position_id]);

            return $duration >= 10;
        }));
    }
}
