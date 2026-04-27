<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AnalyzeIbCommissions extends Command
{
    protected $signature = 'app:analyze-ib-commissions
        {--code= : Analyze specific account code}
        {--referral= : Analyze specific IB referral code}
        {--fix-duplicates : Actually delete duplicate wallet entries (default: report only)}';

    protected $description = 'Analyze IB commission distribution: find duplicates, overpayments, and missing commissions';

    public function handle(): int
    {
        $code = $this->option('code');
        $referral = $this->option('referral');
        $fixDuplicates = $this->option('fix-duplicates');

        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('  IB Commission Distribution Analysis');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->newLine();

        $this->analyzeOverview();
        $this->analyzeDuplicateWallets($code, $referral, $fixDuplicates);
        $this->analyzeDuplicateCommissions($code);
        $this->analyzeOrphanedWallets();
        $this->analyzeMissingCommissions($code);
        $this->analyzeStuckCommissions();
        $this->analyzeOverpaidIBs($referral);
        $this->analyzeCommissionsByStatus();

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('  Analysis complete');
        $this->info('═══════════════════════════════════════════════════════════════');

        return self::SUCCESS;
    }

    protected function analyzeOverview(): void
    {
        $this->info('── 1. Overview ──');

        $totalCommissions = DB::table('ib1_commission')->whereNull('deleted_at')->count();
        $totalWallets = DB::table('ib_wallet')->count();
        $totalDeals = DB::table('deals')->where('entry', 1)->whereIn('action', [0, 1])->count();

        $commissionsByStatus = DB::table('ib1_commission')
            ->whereNull('deleted_at')
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        $totalWalletAmount = DB::table('ib_wallet')->sum(DB::raw('CAST(ib_wallet AS DECIMAL(20,10))'));

        $this->table(['Metric', 'Value'], [
            ['Total close deals in DB', number_format($totalDeals)],
            ['Total ib1_commission records', number_format($totalCommissions)],
            ['  status=0 (unprocessed)', number_format($commissionsByStatus[0] ?? 0)],
            ['  status=1 (processed)', number_format($commissionsByStatus[1] ?? 0)],
            ['  status=10 (discarded)', number_format($commissionsByStatus[10] ?? 0)],
            ['Total ib_wallet entries', number_format($totalWallets)],
            ['Total wallet amount distributed', number_format($totalWalletAmount, 2)],
        ]);

        $this->newLine();
    }

    protected function analyzeDuplicateWallets(?string $code, ?string $referral, bool $fix): void
    {
        $this->info('── 2. Duplicate Wallet Entries ──');
        $this->comment('   Partially closed positions (multiple order_ids) for same expert_position_id');

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
                DB::raw('SUM(CAST(w.ib_wallet AS DECIMAL(20,10))) - MIN(CAST(w.ib_wallet AS DECIMAL(20,10))) as overpaid')
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

        $duplicates = $query->get();

        if ($duplicates->isEmpty()) {
            $this->info('   No duplicate wallet entries found.');
            $this->newLine();
            return;
        }

        $totalOverpaid = $duplicates->sum('overpaid');

        $this->warn("   Found {$duplicates->count()} positions with multiple partial closes");
        $this->warn("   Total overpaid amount: " . number_format($totalOverpaid, 10));
        $this->newLine();

        // Show top duplicates
        $top = $duplicates->sortByDesc('order_count')->take(20);
        $this->table(
            ['expert_position_id', 'user_id', 'orders', 'total_vol', 'primary_vol', 'total_amt', 'expected', 'overpaid'],
            $top->map(fn($r) => [
                $r->expert_position_id ?? 'NULL',
                substr($r->user_id, 0, 8) . '...',
                $r->order_count,
                $r->total_volume,
                $r->primary_volume,
                number_format($r->total_amount, 4),
                number_format($r->expected_amount, 4),
                number_format($r->overpaid, 4),
            ])
        );

        if ($fix) {
            $this->fixDuplicateWallets($duplicates);
        } else {
            $this->comment('   Run with --fix-duplicates to remove the extra rows.');
        }

        $this->newLine();
    }

    protected function fixDuplicateWallets($duplicates): void
    {
        if (!$this->confirm('This will consolidate wallet entries for partially closed positions, keeping only the primary close (max volume). Continue?')) {
            return;
        }

        $deleted = 0;

        foreach ($duplicates as $dup) {
            // Find all wallet entries for this expert_position_id + user_id combination
            $wallets = DB::table('ib_wallet as w')
                ->join('ib1_commission as c', 'c.id', '=', 'w.ib1_commission_id')
                ->where('c.expert_position_id', $dup->expert_position_id)
                ->where('w.user_id', $dup->user_id)
                ->select('w.id', 'c.volume', 'w.created_at')
                ->orderBy('c.volume', 'desc')
                ->get();

            if ($wallets->count() > 1) {
                // Keep the entry with the largest volume (primary close), delete the rest
                $keepId = $wallets->first()->id;
                foreach ($wallets->skip(1) as $wallet) {
                    DB::table('ib_wallet')->where('id', $wallet->id)->delete();
                    $deleted++;
                }
            }
        }

        $this->info("   Deleted {$deleted} duplicate wallet rows from partial closes.");
    }

    protected function analyzeDuplicateCommissions(?string $code): void
    {
        $this->info('── 3. Duplicate Commission Records ──');
        $this->comment('   Same (order_id, code) in ib1_commission');

        $query = DB::table('ib1_commission')
            ->whereNull('deleted_at')
            ->select('order_id', 'code', DB::raw('COUNT(*) as cnt'))
            ->groupBy('order_id', 'code')
            ->havingRaw('COUNT(*) > 1');

        if ($code) {
            $query->where('code', $code);
        }

        $dupes = $query->get();

        if ($dupes->isEmpty()) {
            $this->info('   No duplicate commission records found.');
        } else {
            $this->warn("   Found {$dupes->count()} order_id+code pairs with duplicates");
            $top = $dupes->sortByDesc('cnt')->take(10);
            $this->table(
                ['order_id', 'code', 'count'],
                $top->map(fn($r) => [$r->order_id, $r->code, $r->cnt])
            );
        }

        $this->newLine();
    }

    protected function analyzeOrphanedWallets(): void
    {
        $this->info('── 4. Orphaned Wallet Entries ──');
        $this->comment('   Wallet entries with no matching ib1_commission record');

        $orphaned = DB::table('ib_wallet as w')
            ->leftJoin('ib1_commission as c', 'w.ib1_commission_id', '=', 'c.id')
            ->whereNull('c.id')
            ->whereNotNull('w.ib1_commission_id')
            ->count();

        $nullCommission = DB::table('ib_wallet')
            ->whereNull('ib1_commission_id')
            ->count();

        $this->table(['Issue', 'Count'], [
            ['Wallets with invalid ib1_commission_id (FK broken)', number_format($orphaned)],
            ['Wallets with NULL ib1_commission_id', number_format($nullCommission)],
        ]);

        $this->newLine();
    }

    protected function analyzeMissingCommissions(?string $code): void
    {
        $this->info('── 5. Closed Deals Missing Commission ──');
        $this->comment('   Close deals in deals table with no ib1_commission record');

        // Use subtraction approach (total deals - deals with commission)
        // This is ~66x faster than NOT EXISTS method for large tables (~32s vs 931s)
        // IMPORTANT: Filter by symbol IS NOT NULL to exclude deposits/withdrawals (no commission)
        // NOTE: Duration filtering is applied in backfill, not here. Analysis reports database state.
        $whereCode = $code ? "AND a.code = " . DB::connection()->getPdo()->quote($code) : '';

        // Total qualifying deals
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

        // Deals with commissions (use FORCE INDEX for optimal performance)
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

        $this->table(['Metric', 'Value'], [
            ['Close deals without ib1_commission', number_format($count)],
        ]);

        if ($count > 0 && $count < 10000) {
            // Only compute top accounts for small counts (quick operation)
            // For large counts, skip to avoid expensive GROUP BY

            // Per-account totals (simple count, no DISTINCT needed)
            $totalPerAccount = collect(DB::select("
                SELECT a.code, COUNT(*) as total
                FROM deals d
                INNER JOIN accounts a ON a.id = d.account_id AND a.deleted_at IS NULL AND a.demo = 0
                INNER JOIN aspnetusers u ON u.id = a.user_id AND u.status = 1 AND u.ib1 IS NOT NULL
                WHERE d.entry = 1 AND d.action IN (0, 1) AND d.position_id > 0
                  AND d.symbol IS NOT NULL AND d.symbol != ''
                {$whereCode}
                GROUP BY a.code
            "))->keyBy('code');

            // Per-account with commission (only these need DISTINCT)
            $withPerAccount = collect(DB::select("
                SELECT a.code, COUNT(DISTINCT d.id) as with_comm
                FROM deals d
                INNER JOIN accounts a ON a.id = d.account_id AND a.deleted_at IS NULL AND a.demo = 0
                INNER JOIN aspnetusers u ON u.id = a.user_id AND u.status = 1 AND u.ib1 IS NOT NULL
                INNER JOIN ib1_commission c FORCE INDEX (ib1_commission_order_id_code_index)
                    ON c.order_id = d.order_id AND c.code = a.code
                WHERE d.entry = 1 AND d.action IN (0, 1) AND d.position_id > 0
                  AND d.symbol IS NOT NULL AND d.symbol != ''
                {$whereCode}
                GROUP BY a.code
            "))->keyBy('code');

            // Compute missing per account
            $missing = [];
            foreach ($totalPerAccount as $code => $row) {
                $withComm = $withPerAccount->get($code);
                $missingCount = $row->total - ($withComm ? $withComm->with_comm : 0);
                if ($missingCount > 0) {
                    $missing[] = ['code' => $code, 'Missing Count' => $missingCount];
                }
            }

            // Sort by missing count desc, take top 10
            usort($missing, fn($a, $b) => $b['Missing Count'] <=> $a['Missing Count']);
            $topAccounts = array_slice($missing, 0, 10);

            if ($topAccounts) {
                $this->comment('   Top accounts with missing commissions:');
                $this->table(
                    ['Account Code', 'Missing Count'],
                    array_map(fn($a) => [$a['code'], $a['Missing Count']], $topAccounts)
                );
            }

            $this->comment('   Use `app:backfill-deal-commissions` to process these.');
        } elseif ($count >= 10000 && $count < 1000000) {
            $this->comment('   Too many results for per-account breakdown.');
            $this->comment('   Use `app:backfill-deal-commissions` to process these.');
        } elseif ($count >= 1000000) {
            $this->comment('   Too many results for per-account breakdown. Use --code= to filter.');
            $this->comment('   Use `app:backfill-deal-commissions` to process these.');
        }

        $this->newLine();
    }

    protected function analyzeStuckCommissions(): void
    {
        $this->info('── 6. Stuck Commissions (status=0, no wallets) ──');
        $this->comment('   Commissions created but never distributed to wallets');

        $stuck = DB::selectOne("
            SELECT COUNT(*) as cnt
            FROM ib1_commission c
            WHERE c.status = 0
              AND c.deleted_at IS NULL
              AND NOT EXISTS (SELECT 1 FROM ib_wallet w WHERE w.ib1_commission_id = c.id)
        ")->cnt;

        // Also check: status=0 but HAS wallets (should be marked status=1)
        $unmarked = DB::selectOne("
            SELECT COUNT(DISTINCT c.id) as cnt
            FROM ib1_commission c
            INNER JOIN ib_wallet w ON w.ib1_commission_id = c.id
            WHERE c.status = 0
              AND c.deleted_at IS NULL
        ")->cnt;

        $this->table(['Issue', 'Count'], [
            ['Commissions status=0 with NO wallets', number_format($stuck)],
            ['Commissions status=0 but HAS wallets (should be status=1)', number_format($unmarked)],
        ]);

        $this->newLine();
    }

    protected function analyzeOverpaidIBs(?string $referral): void
    {
        $this->info('── 7. IB Overpayment Analysis ──');
        $this->comment('   IBs who received more wallet entries than expected per order');

        // Each order should produce at most 1 wallet per IB user
        // If an IB has multiple wallets for the same order, they were overpaid
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

        if ($overpaid->isEmpty()) {
            $this->info('   No overpaid IBs found (no duplicate wallet entries per order per user).');
        } else {
            $this->warn("   Found {$overpaid->count()} IBs with potential duplicate payments:");
            $this->table(
                ['Referral Code', 'Total Wallets', 'Unique Orders', 'Duplicate Entries', 'Total Paid'],
                $overpaid->map(fn($r) => [
                    $r->referral_code,
                    number_format($r->total_wallets),
                    number_format($r->unique_orders),
                    number_format($r->duplicate_entries),
                    number_format($r->total_paid, 4),
                ])
            );
        }

        $this->newLine();

        // Cross-check: wallets with amount > plan rate * volume
        $this->comment('   Top 20 IBs by total commission received:');
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

        $this->table(
            ['Referral Code', 'Wallet Entries', 'Unique Orders', 'Total Amount', 'Avg Per Entry'],
            $topIBs->get()->map(fn($r) => [
                $r->referral_code,
                number_format($r->wallet_count),
                number_format($r->unique_orders),
                number_format($r->total_amount, 2),
                number_format($r->avg_per_entry, 6),
            ])
        );

        $this->newLine();
    }

    protected function analyzeCommissionsByStatus(): void
    {
        $this->info('── 8. Commission Distribution Pipeline Health ──');

        // How many commissions per status
        $pipeline = DB::table('ib1_commission')
            ->whereNull('deleted_at')
            ->select(
                'status',
                DB::raw('COUNT(*) as cnt'),
                DB::raw('MIN(created_at) as oldest'),
                DB::raw('MAX(created_at) as newest')
            )
            ->groupBy('status')
            ->get();

        $statusLabels = [0 => 'Unprocessed', 1 => 'Processed', 10 => 'Discarded (<10s)'];
        $this->table(
            ['Status', 'Label', 'Count', 'Oldest', 'Newest'],
            $pipeline->map(fn($r) => [
                $r->status,
                $statusLabels[$r->status] ?? 'Unknown',
                number_format($r->cnt),
                $r->oldest,
                $r->newest,
            ])
        );

        // Wallet entries per day (last 7 days)
        $this->newLine();
        $this->comment('   Wallet entries created per day (last 7 days):');
        $daily = DB::table('ib_wallet')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(*) as entries'),
                DB::raw('SUM(CAST(ib_wallet AS DECIMAL(20,10))) as total_amount')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->get();

        if ($daily->isEmpty()) {
            $this->info('   No wallet entries in the last 7 days.');
        } else {
            $this->table(
                ['Date', 'Entries', 'Total Amount'],
                $daily->map(fn($r) => [
                    $r->day,
                    number_format($r->entries),
                    number_format($r->total_amount, 2),
                ])
            );
        }

        $this->newLine();
    }
}
