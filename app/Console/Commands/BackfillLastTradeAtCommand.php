<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackfillLastTradeAtCommand extends Command
{
    protected $signature = 'accounts:backfill-last-trade-at
                            {--codes= : Comma-separated list of specific account codes to process}
                            {--dry-run : Preview which accounts would be updated without making changes}
                            {--limit=0 : Max number of accounts to update (0 = no limit)}';

    protected $description = 'Backfill last_trade_at for MetaTrader5 accounts where it is NULL but trades exist (zero N+1: single DB query)';

    public function handle(): int
    {
        $isDryRun   = $this->option('dry-run');
        $codesInput = $this->option('codes');
        $limit      = max(0, (int) $this->option('limit'));

        $specificCodes = $codesInput
            ? array_values(array_filter(array_map('trim', explode(',', $codesInput))))
            : [];

        $this->info('=== Backfill last_trade_at for MetaTrader5 Accounts ===');

        if ($isDryRun) {
            $this->warn('[DRY RUN] No changes will be written to the database.');
        }

        if ($limit > 0) {
            $this->warn("Limit set: only {$limit} account(s) will be processed.");
        }

        Log::info('BackfillLastTradeAt: Starting', [
            'dry_run'        => $isDryRun,
            'specific_codes' => $specificCodes ?: 'all',
            'limit'          => $limit ?: 'none',
        ]);

        // ------------------------------------------------------------------
        // Step 1 – Count eligible accounts (1 query, no PHP-side data loaded).
        //
        // Eligible = MetaTrader5, last_trade_at IS NULL, has at least one
        // trade row so MAX(open_time) will produce a non-null value.
        // ------------------------------------------------------------------
        $this->info('Counting eligible accounts...');

        $countQuery = DB::table('accounts as a')
            ->join(
                DB::raw('(SELECT account_id, MAX(open_time) AS latest_open_time FROM trades GROUP BY account_id) t'),
                't.account_id', '=', 'a.id'
            )
            ->where('a.trade_platform', 'MetaTrader5')
            ->whereNull('a.last_trade_at')
            ->whereNotNull('a.code')
            ->whereNotNull('t.latest_open_time');

        if ($specificCodes) {
            $countQuery->whereIn('a.code', $specificCodes);
        }

        $total = (clone $countQuery)->count();

        // Cap total to the limit so summary numbers are accurate
        if ($limit > 0) {
            $total = min($total, $limit);
        }

        if ($total === 0) {
            $this->info('No eligible accounts found. Nothing to do.');
            Log::info('BackfillLastTradeAt: No eligible accounts found.');
            return self::SUCCESS;
        }

        $this->info("Found {$total} eligible account(s).");
        Log::info("BackfillLastTradeAt: Found {$total} eligible accounts");

        // ------------------------------------------------------------------
        // Step 2 – Dry-run preview (1 SELECT query, zero PHP loops).
        // ------------------------------------------------------------------
        if ($isDryRun) {
            $this->newLine();
            $this->info('Accounts that would be updated:');

            $previewQuery = (clone $countQuery)
                ->select('a.id', 'a.code', 't.latest_open_time')
                ->orderBy('a.code');

            if ($limit > 0) {
                $previewQuery->limit($limit);
            }

            $preview = $previewQuery->get();

            $this->table(
                ['Account ID', 'Code', 'Would set last_trade_at to'],
                $preview->map(fn ($r) => [$r->id, $r->code, $r->latest_open_time])->all()
            );

            $this->newLine();
            $this->warn("[DRY RUN] {$preview->count()} account(s) would be updated.");
            Log::info('BackfillLastTradeAt: Dry run complete', ['would_update' => $preview->count()]);
            return self::SUCCESS;
        }

        // ------------------------------------------------------------------
        // Step 3 – Single bulk UPDATE … JOIN.
        //
        // Everything happens inside MySQL:
        //   • The subquery aggregates MAX(open_time) per account.
        //   • The JOIN links it to accounts.
        //   • The WHERE guards ensure only qualifying rows are touched.
        //   • All dynamic values (codes) are PDO-bound — no injection risk.
        //
        // For 6 000 accounts this is ONE round-trip to the DB. Zero N+1.
        // ------------------------------------------------------------------
        $this->info('Running bulk UPDATE...');

        try {
            if ($specificCodes) {
                // Build safe placeholders for the code list
                $codePlaceholders = implode(',', array_fill(0, count($specificCodes), '?'));
                $limitClause      = $limit > 0 ? "LIMIT {$limit}" : '';

                $rowsAffected = DB::affectingStatement("
                    UPDATE accounts a
                    JOIN (
                        SELECT t.account_id, MAX(t.open_time) AS latest_open_time
                        FROM trades t
                        INNER JOIN accounts a2
                            ON a2.id = t.account_id
                           AND a2.trade_platform = 'MetaTrader5'
                           AND a2.last_trade_at IS NULL
                           AND a2.code IS NOT NULL
                           AND a2.code IN ({$codePlaceholders})
                        GROUP BY t.account_id
                    ) agg ON agg.account_id = a.id
                    SET a.last_trade_at = agg.latest_open_time
                    WHERE a.trade_platform = 'MetaTrader5'
                      AND a.last_trade_at IS NULL
                      AND a.code IN ({$codePlaceholders})
                    {$limitClause}
                ", array_merge($specificCodes, $specificCodes));
            } else {
                $limitClause = $limit > 0 ? "LIMIT {$limit}" : '';

                $rowsAffected = DB::affectingStatement("
                    UPDATE accounts a
                    JOIN (
                        SELECT t.account_id, MAX(t.open_time) AS latest_open_time
                        FROM trades t
                        INNER JOIN accounts a2
                            ON a2.id = t.account_id
                           AND a2.trade_platform = 'MetaTrader5'
                           AND a2.last_trade_at IS NULL
                           AND a2.code IS NOT NULL
                        GROUP BY t.account_id
                    ) agg ON agg.account_id = a.id
                    SET a.last_trade_at = agg.latest_open_time
                    WHERE a.trade_platform = 'MetaTrader5'
                      AND a.last_trade_at IS NULL
                    {$limitClause}
                ");
            }

            Log::info('BackfillLastTradeAt: UPDATE complete', [
                'rows_affected' => $rowsAffected,
            ]);
        } catch (\Throwable $e) {
            Log::error('BackfillLastTradeAt: UPDATE failed', ['error' => $e->getMessage()]);
            $this->error('UPDATE failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        // ------------------------------------------------------------------
        // Step 4 – Summary
        // ------------------------------------------------------------------
        $skipped = $total - $rowsAffected;

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Eligible accounts found', $total],
                ['Accounts updated',        $rowsAffected],
                ['Skipped (no trades)',     max(0, $skipped)],
            ]
        );

        Log::info('BackfillLastTradeAt: Completed', [
            'total'   => $total,
            'updated' => $rowsAffected,
            'skipped' => max(0, $skipped),
        ]);

        $this->info('Done.');
        return self::SUCCESS;
    }
}
