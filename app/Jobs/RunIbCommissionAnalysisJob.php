<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RunIbCommissionAnalysisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    protected ?string $code;
    protected ?string $referral;
    protected string $cacheKey;

    public function __construct(string $cacheKey, ?string $code = null, ?string $referral = null)
    {
        $this->cacheKey = $cacheKey;
        $this->code = $code;
        $this->referral = $referral;
    }

    public function handle(): void
    {
        Cache::put($this->cacheKey . ':status', 'running', 900);

        try {
            $data = [];
            $data['overview'] = $this->getOverview();
            Cache::put($this->cacheKey . ':progress', 'Overview complete', 900);

            $data['duplicate_wallets'] = $this->getDuplicateWallets($this->code, $this->referral);
            Cache::put($this->cacheKey . ':progress', 'Duplicate wallets complete', 900);

            $data['duplicate_commissions'] = $this->getDuplicateCommissions($this->code);
            Cache::put($this->cacheKey . ':progress', 'Duplicate commissions complete', 900);

            $data['orphaned_wallets'] = $this->getOrphanedWallets();
            Cache::put($this->cacheKey . ':progress', 'Orphaned wallets complete', 900);

            $data['missing_commissions'] = $this->getMissingCommissions($this->code);
            Cache::put($this->cacheKey . ':progress', 'Missing commissions complete', 900);

            $data['stuck_commissions'] = $this->getStuckCommissions();
            Cache::put($this->cacheKey . ':progress', 'Stuck commissions complete', 900);

            $data['overpaid_ibs'] = $this->getOverpaidIbs($this->referral);
            Cache::put($this->cacheKey . ':progress', 'Overpaid IBs complete', 900);

            $data['overpayment_audit'] = $this->getOverpaymentAudit($this->referral);
            Cache::put($this->cacheKey . ':progress', 'Overpayment audit complete', 900);

            $data['pipeline_health'] = $this->getPipelineHealth();

            Cache::put($this->cacheKey . ':result', $data, 900);
            Cache::put($this->cacheKey . ':status', 'completed', 900);
            Cache::put($this->cacheKey . ':progress', 'All sections complete', 900);
        } catch (\Throwable $e) {
            Cache::put($this->cacheKey . ':status', 'failed', 900);
            Cache::put($this->cacheKey . ':progress', 'Error: ' . $e->getMessage(), 900);
        }
    }

    protected function getOverview(): array
    {
        $totalDeals = DB::table('deals')
            ->where('entry', 1)
            ->whereIn('action', [0, 1])
            ->whereNotNull('symbol')
            ->where('symbol', '!=', '')
            ->count();
        $totalWallets = DB::table('ib_wallet')->count();

        $commissionsByStatus = DB::table('ib1_commission')
            ->whereNull('deleted_at')
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        $totalCommissions = array_sum($commissionsByStatus);
        $totalWalletAmount = DB::table('ib_wallet')->sum(DB::raw('CAST(ib_wallet AS DECIMAL(20,10))'));

        return [
            'total_deals' => $totalDeals,
            'total_commissions' => $totalCommissions,
            'commissions_unprocessed' => $commissionsByStatus[0] ?? 0,
            'commissions_processed' => $commissionsByStatus[1] ?? 0,
            'commissions_discarded' => $commissionsByStatus[10] ?? 0,
            'total_wallets' => $totalWallets,
            'total_wallet_amount' => round($totalWalletAmount, 2),
        ];
    }

    protected function getDuplicateWallets(?string $code, ?string $referral): array
    {
        // Query duplicates, excluding withdrawn entries
        $query = DB::table('ib1_commission as c')
            ->join('ib_wallet as w', 'w.ib1_commission_id', '=', 'c.id')
            ->select(
                'c.expert_position_id',
                'w.user_id',
                DB::raw('COUNT(DISTINCT w.order_id) as order_count'),
                DB::raw('COUNT(*) as cnt'),
                DB::raw('SUM(c.volume) as total_volume'),
                DB::raw('MAX(c.volume) as primary_volume'),
                DB::raw('SUM(CAST(w.ib_wallet AS DECIMAL(20,10))) as total_amount'),
                DB::raw('MIN(CAST(w.ib_wallet AS DECIMAL(20,10))) as expected_amount'),
                DB::raw('SUM(CAST(w.ib_wallet AS DECIMAL(20,10))) - MIN(CAST(w.ib_wallet AS DECIMAL(20,10))) as overpaid'),
                // Count non-withdrawn entries (can be fixed)
                DB::raw('COUNT(CASE WHEN w.ib_withdraw IS NULL THEN 1 END) as recoverable_count'),
                // Sum non-withdrawn amounts (can be fixed)
                DB::raw('SUM(CASE WHEN w.ib_withdraw IS NULL THEN CAST(w.ib_wallet AS DECIMAL(20,10)) ELSE 0 END) as recoverable_amount'),
                // Sum withdrawn amounts (cannot be fixed)
                DB::raw('SUM(CASE WHEN w.ib_withdraw IS NOT NULL THEN CAST(w.ib_wallet AS DECIMAL(20,10)) ELSE 0 END) as withdrawn_amount')
            )
            ->whereNull('c.deleted_at')
            ->whereNotNull('c.expert_position_id')
            ->groupBy('c.expert_position_id', 'w.user_id')
            ->havingRaw('COUNT(DISTINCT w.order_id) > 1');

        if ($code) {
            $query->where('w.code', $code);
        }
        if ($referral) {
            $query->where('w.email', $referral);
        }

        $duplicates = $query->orderByDesc('order_count')->limit(50)->get();

        return [
            'count' => $duplicates->count(),
            'total_overpaid' => round($duplicates->sum('overpaid'), 10),
            'total_recoverable' => round($duplicates->sum('recoverable_amount'), 10),
            'total_withdrawn' => round($duplicates->sum('withdrawn_amount'), 10),
            'items' => $duplicates->map(fn($r) => [
                'expert_position_id' => $r->expert_position_id ?? 'NULL',
                'user_id' => $r->user_id,
                'order_count' => $r->order_count,
                'wallet_count' => $r->cnt,
                'total_volume' => round($r->total_volume, 4),
                'primary_volume' => round($r->primary_volume, 4),
                'total_amount' => round($r->total_amount, 4),
                'expected_amount' => round($r->expected_amount, 4),
                'overpaid' => round($r->overpaid, 4),
                'recoverable_count' => (int)$r->recoverable_count,
                'recoverable_amount' => round($r->recoverable_amount, 4),
                'withdrawn_amount' => round($r->withdrawn_amount, 4),
                'can_fix' => (int)$r->recoverable_count > 0,
            ])->values()->toArray(),
        ];
    }

    protected function getDuplicateCommissions(?string $code): array
    {
        $query = DB::table('ib1_commission')
            ->whereNull('deleted_at')
            ->select('order_id', 'code', DB::raw('COUNT(*) as cnt'))
            ->groupBy('order_id', 'code')
            ->havingRaw('COUNT(*) > 1');

        if ($code) {
            $query->where('code', $code);
        }

        $totalCount = (clone $query)->count();
        $dupes = $query->orderByDesc('cnt')->limit(50)->get();

        return [
            'count' => $totalCount,
            'items' => $dupes->map(fn($r) => [
                'order_id' => $r->order_id,
                'code' => $r->code,
                'count' => $r->cnt,
            ])->values()->toArray(),
        ];
    }

    protected function getOrphanedWallets(): array
    {
        $orphaned = DB::table('ib_wallet as w')
            ->leftJoin('ib1_commission as c', 'w.ib1_commission_id', '=', 'c.id')
            ->whereNull('c.id')
            ->whereNotNull('w.ib1_commission_id')
            ->count();

        $nullCommission = DB::table('ib_wallet')
            ->whereNull('ib1_commission_id')
            ->count();

        return [
            'broken_fk' => $orphaned,
            'null_commission_id' => $nullCommission,
        ];
    }

    protected function getMissingCommissions(?string $code): array
    {
        $whereCode = $code ? "AND a.code = " . DB::connection()->getPdo()->quote($code) : '';

        // Use subtraction approach: total qualifying deals - deals with commissions
        // This is ~66x faster than NOT EXISTS for large tables
        // IMPORTANT: Filter by symbol IS NOT NULL to exclude deposits/withdrawals
        // NOTE: Duration filtering is applied in backfill, not here. Analysis reports database state.
        $totalDeals = DB::selectOne("
            SELECT COUNT(*) as cnt
            FROM deals d
            INNER JOIN accounts a ON a.id = d.account_id AND a.deleted_at IS NULL AND a.demo = 0
            INNER JOIN aspnetusers u ON u.id = a.user_id AND u.status = 1 AND u.ib1 IS NOT NULL
            WHERE d.entry = 1
              AND d.action IN (0, 1)
              AND d.position_id > 0
              AND d.symbol IS NOT NULL
              AND d.symbol != ''
              {$whereCode}
        ")->cnt;

        $withCommission = DB::selectOne("
            SELECT COUNT(DISTINCT d.id) as cnt
            FROM deals d
            INNER JOIN accounts a ON a.id = d.account_id AND a.deleted_at IS NULL AND a.demo = 0
            INNER JOIN aspnetusers u ON u.id = a.user_id AND u.status = 1 AND u.ib1 IS NOT NULL
            INNER JOIN ib1_commission c FORCE INDEX (ib1_commission_order_id_code_index)
                ON c.order_id = d.order_id AND c.code = a.code
            WHERE d.entry = 1
              AND d.action IN (0, 1)
              AND d.position_id > 0
              AND d.symbol IS NOT NULL
              AND d.symbol != ''
              {$whereCode}
        ")->cnt;

        $count = $totalDeals - $withCommission;

        // Note: We skip per-account breakdown by default to avoid expensive GROUP BY
        // The backfill scans all missing deals anyway, so top accounts isn't critical
        $topAccounts = [];

        return [
            'count' => $count,
            'top_accounts' => $topAccounts,
        ];
    }

    protected function getStuckCommissions(): array
    {
        $stuck = DB::selectOne("
            SELECT COUNT(*) as cnt
            FROM ib1_commission c
            WHERE c.status = 0
              AND c.deleted_at IS NULL
              AND NOT EXISTS (SELECT 1 FROM ib_wallet w WHERE w.ib1_commission_id = c.id)
        ")->cnt;

        $unmarked = DB::selectOne("
            SELECT COUNT(DISTINCT c.id) as cnt
            FROM ib1_commission c
            INNER JOIN ib_wallet w ON w.ib1_commission_id = c.id
            WHERE c.status = 0
              AND c.deleted_at IS NULL
        ")->cnt;

        return [
            'no_wallets' => $stuck,
            'has_wallets_wrong_status' => $unmarked,
        ];
    }

    protected function getOverpaidIbs(?string $referral): array
    {
        $query = DB::table('ib_wallet')
            ->select(
                'email as referral_code',
                DB::raw('COUNT(*) as total_wallets'),
                DB::raw('COUNT(DISTINCT order_id) as unique_orders'),
                DB::raw('COUNT(*) - COUNT(DISTINCT CONCAT(order_id, "_", user_id)) as duplicate_entries'),
                DB::raw('SUM(CAST(ib_wallet AS DECIMAL(20,10))) as total_paid')
            )
            ->groupBy('email')
            ->havingRaw('COUNT(*) > COUNT(DISTINCT CONCAT(order_id, "_", user_id))');

        if ($referral) {
            $query->where('email', $referral);
        }

        $overpaid = $query->orderByDesc('duplicate_entries')->limit(20)->get();

        $topIBs = DB::table('ib_wallet')
            ->select(
                'email as referral_code',
                DB::raw('COUNT(*) as wallet_count'),
                DB::raw('COUNT(DISTINCT order_id) as unique_orders'),
                DB::raw('SUM(CAST(ib_wallet AS DECIMAL(20,10))) as total_amount'),
                DB::raw('AVG(CAST(ib_wallet AS DECIMAL(20,10))) as avg_per_entry')
            )
            ->groupBy('email')
            ->orderByDesc(DB::raw('SUM(CAST(ib_wallet AS DECIMAL(20,10)))'))
            ->limit(20);

        if ($referral) {
            $topIBs->where('email', $referral);
        }

        return [
            'overpaid_ibs' => $overpaid->map(fn($r) => [
                'referral_code' => $r->referral_code,
                'total_wallets' => $r->total_wallets,
                'unique_orders' => $r->unique_orders,
                'duplicate_entries' => $r->duplicate_entries,
                'total_paid' => round($r->total_paid, 4),
            ])->values()->toArray(),
            'top_ibs' => $topIBs->get()->map(fn($r) => [
                'referral_code' => $r->referral_code,
                'wallet_count' => $r->wallet_count,
                'unique_orders' => $r->unique_orders,
                'total_amount' => round($r->total_amount, 2),
                'avg_per_entry' => round($r->avg_per_entry, 6),
            ])->values()->toArray(),
        ];
    }

    protected function getPipelineHealth(): array
    {
        $statusLabels = [0 => 'Unprocessed', 1 => 'Processed', 10 => 'Discarded (<10s)'];

        $pipeline = DB::table('ib1_commission')
            ->whereNull('deleted_at')
            ->select(
                'status',
                DB::raw('COUNT(*) as cnt'),
                DB::raw('MIN(created_at) as oldest'),
                DB::raw('MAX(created_at) as newest')
            )
            ->groupBy('status')
            ->get()
            ->map(fn($r) => [
                'status' => $r->status,
                'label' => $statusLabels[$r->status] ?? 'Unknown',
                'count' => $r->cnt,
                'oldest' => $r->oldest,
                'newest' => $r->newest,
            ]);

        $daily = DB::table('ib_wallet')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(*) as entries'),
                DB::raw('SUM(CAST(ib_wallet AS DECIMAL(20,10))) as total_amount')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->get()
            ->map(fn($r) => [
                'day' => $r->day,
                'entries' => $r->entries,
                'total_amount' => round($r->total_amount, 2),
            ]);

        return [
            'pipeline' => $pipeline->values()->toArray(),
            'daily_wallets' => $daily->values()->toArray(),
        ];
    }

    protected function getOverpaymentAudit(?string $referral): array
    {
        // Step 1: Find all duplicate commission IDs (non-first entries) using a single query
        // Subquery: for each (order_id, code), get the MIN(id) as the "original"
        $dupeCommissions = DB::select("
            SELECT c.id as dupe_id, c.order_id, c.code, c.created_at as dupe_created_at,
                   first_c.id as original_id, first_c.created_at as original_created_at
            FROM ib1_commission c
            INNER JOIN (
                SELECT order_id, code, MIN(id) as id, MIN(created_at) as created_at
                FROM ib1_commission
                WHERE deleted_at IS NULL
                GROUP BY order_id, code
                HAVING COUNT(*) > 1
            ) first_c ON first_c.order_id = c.order_id AND first_c.code = c.code AND first_c.id != c.id
            WHERE c.deleted_at IS NULL
        ");

        $totalDuplicateGroups = DB::table('ib1_commission')
            ->whereNull('deleted_at')
            ->select('order_id', 'code')
            ->groupBy('order_id', 'code')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if (empty($dupeCommissions)) {
            return [
                'total_duplicate_groups' => 0,
                'total_overpaid_amount' => 0,
                'total_recovered' => 0,
                'total_outstanding' => 0,
                'ibs_affected' => [],
                'overpayment_details' => [],
                'new_records_created' => 0,
            ];
        }

        // Step 2: Get all wallets linked to duplicate commission IDs (batch)
        $dupeIds = array_map(fn($d) => $d->dupe_id, $dupeCommissions);
        $originalIds = array_map(fn($d) => $d->original_id, $dupeCommissions);

        $dupeWallets = DB::table('ib_wallet')
            ->whereIn('ib1_commission_id', $dupeIds)
            ->get()
            ->groupBy('ib1_commission_id');

        // Also batch-load original wallets for matching
        $originalWallets = DB::table('ib_wallet')
            ->whereIn('ib1_commission_id', array_unique($originalIds))
            ->get()
            ->groupBy('ib1_commission_id');

        // Batch-load already tracked overpayments
        $existingOverpayments = DB::table('ib_overpayments')
            ->whereIn('duplicate_wallet_id', $dupeWallets->flatten()->pluck('id')->toArray())
            ->pluck('id', 'duplicate_wallet_id')
            ->toArray();

        // Step 3: Process all duplicates
        $overpayments = [];
        $ibSummary = [];
        $newRecords = 0;
        $walletsToInsert = [];

        foreach ($dupeCommissions as $dupe) {
            $wallets = $dupeWallets->get($dupe->dupe_id, collect());
            if ($wallets->isEmpty()) {
                continue;
            }

            $origWallets = $originalWallets->get($dupe->original_id, collect());

            foreach ($wallets as $dupeWallet) {
                $overpaidAmount = (float) $dupeWallet->ib_wallet;
                if ($overpaidAmount <= 0) {
                    continue;
                }

                $ibCode = $dupeWallet->email;
                if ($referral && $ibCode !== $referral) {
                    continue;
                }

                $origWallet = $origWallets->first(fn($w) => $w->email === $ibCode);

                // Track for ib_overpayments table (will batch insert)
                if (!isset($existingOverpayments[$dupeWallet->id])) {
                    $walletsToInsert[] = [
                        'wallet' => $dupeWallet,
                        'dupe' => $dupe,
                        'origWallet' => $origWallet,
                        'overpaidAmount' => $overpaidAmount,
                    ];
                }

                // Aggregate by IB
                if (!isset($ibSummary[$ibCode])) {
                    $ibSummary[$ibCode] = [
                        'referral_code' => $ibCode,
                        'user_id' => $dupeWallet->user_id,
                        'overpaid_amount' => 0,
                        'affected_orders' => 0,
                        'duplicate_wallets' => 0,
                    ];
                }
                $ibSummary[$ibCode]['overpaid_amount'] += $overpaidAmount;
                $ibSummary[$ibCode]['affected_orders']++;
                $ibSummary[$ibCode]['duplicate_wallets']++;

                $overpayments[] = [
                    'order_id' => $dupe->order_id,
                    'account_code' => $dupe->code,
                    'ib_code' => $ibCode,
                    'overpaid_amount' => $overpaidAmount,
                    'original_amount' => $origWallet ? (float) $origWallet->ib_wallet : null,
                    'original_created' => $dupe->original_created_at,
                    'duplicate_created' => $dupe->dupe_created_at,
                ];
            }
        }

        // Step 4: Batch insert new overpayment records
        if (!empty($walletsToInsert)) {
            // Get IB balances in batch
            $userIds = array_unique(array_map(fn($i) => $i['wallet']->user_id, $walletsToInsert));
            $balances = DB::table('ib_wallet')
                ->whereIn('user_id', $userIds)
                ->groupBy('user_id')
                ->selectRaw('user_id, SUM(CAST(ib_wallet AS DECIMAL(20,10))) - SUM(CAST(ib_withdraw AS DECIMAL(20,10))) as balance')
                ->pluck('balance', 'user_id')
                ->toArray();

            $inserts = [];
            foreach ($walletsToInsert as $item) {
                $inserts[] = [
                    'id' => (string) Str::orderedUuid(),
                    'ib_user_id' => $item['wallet']->user_id,
                    'referral_code' => $item['wallet']->email,
                    'order_id' => $item['dupe']->order_id,
                    'account_code' => $item['dupe']->code,
                    'duplicate_commission_id' => $item['dupe']->dupe_id,
                    'duplicate_wallet_id' => $item['wallet']->id,
                    'original_commission_id' => $item['dupe']->original_id,
                    'original_wallet_id' => $item['origWallet']?->id,
                    'overpaid_amount' => $item['overpaidAmount'],
                    'recovered_amount' => 0,
                    'balance_at_detection' => (float) ($balances[$item['wallet']->user_id] ?? 0),
                    'status' => 'detected',
                    'detected_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert in chunks to avoid query size limits
            foreach (array_chunk($inserts, 100) as $chunk) {
                DB::table('ib_overpayments')->insert($chunk);
            }
            $newRecords = count($inserts);
        }

        // Step 5: Enrich IB summaries with balance and withdrawal info (batch)
        $summaryUserIds = array_unique(array_column($ibSummary, 'user_id'));

        $balanceData = [];
        if (!empty($summaryUserIds)) {
            $balanceData = DB::table('ib_wallet')
                ->whereIn('user_id', $summaryUserIds)
                ->groupBy('user_id')
                ->selectRaw('
                    user_id,
                    SUM(CAST(ib_wallet AS DECIMAL(20,10))) as total_earned,
                    SUM(CAST(ib_withdraw AS DECIMAL(20,10))) as total_withdrawn
                ')
                ->get()
                ->keyBy('user_id')
                ->toArray();

            // Batch-load recent withdrawals
            $withdrawalData = DB::table('ib_wallet')
                ->whereIn('user_id', $summaryUserIds)
                ->where(DB::raw('CAST(ib_withdraw AS DECIMAL(20,10))'), '>', 0)
                ->orderBy('created_at', 'desc')
                ->get(['user_id', 'ib_withdraw', 'remark', 'created_at'])
                ->groupBy('user_id');

            // Batch-load recovery status
            $recoveryData = DB::table('ib_overpayments')
                ->whereIn('referral_code', array_keys($ibSummary))
                ->groupBy('referral_code')
                ->selectRaw('referral_code, SUM(overpaid_amount) as total_detected, SUM(recovered_amount) as total_recovered')
                ->get()
                ->keyBy('referral_code')
                ->toArray();
        }

        foreach ($ibSummary as $ibCode => &$summary) {
            $bd = $balanceData[$summary['user_id']] ?? null;
            $summary['total_earned'] = round((float) ($bd->total_earned ?? 0), 4);
            $summary['total_withdrawn'] = round((float) ($bd->total_withdrawn ?? 0), 4);
            $summary['current_balance'] = round($summary['total_earned'] - $summary['total_withdrawn'], 4);
            $summary['overpaid_amount'] = round($summary['overpaid_amount'], 10);
            $summary['overpaid_likely_withdrawn'] = $summary['total_withdrawn'] > 0;
            $summary['can_recover_from_balance'] = $summary['current_balance'] >= $summary['overpaid_amount'];

            $withdrawals = $withdrawalData[$summary['user_id']] ?? collect();
            $summary['recent_withdrawals'] = $withdrawals->take(10)->map(fn($w) => [
                'amount' => (float) $w->ib_withdraw,
                'remark' => $w->remark,
                'date' => $w->created_at,
            ])->values()->toArray();

            $recovery = $recoveryData[$ibCode] ?? null;
            $summary['total_recovered'] = round((float) ($recovery->total_recovered ?? 0), 10);
            $summary['outstanding'] = round($summary['overpaid_amount'] - $summary['total_recovered'], 10);
        }
        unset($summary);

        $totalOverpaid = array_sum(array_column($ibSummary, 'overpaid_amount'));
        $totalRecovered = array_sum(array_column($ibSummary, 'total_recovered'));

        usort($ibSummary, fn($a, $b) => $b['overpaid_amount'] <=> $a['overpaid_amount']);

        return [
            'total_duplicate_groups' => $totalDuplicateGroups,
            'total_overpaid_amount' => round($totalOverpaid, 4),
            'total_recovered' => round($totalRecovered, 4),
            'total_outstanding' => round($totalOverpaid - $totalRecovered, 4),
            'ibs_affected' => array_values($ibSummary),
            'overpayment_details' => array_slice($overpayments, 0, 100),
            'new_records_created' => $newRecords,
        ];
    }
}
