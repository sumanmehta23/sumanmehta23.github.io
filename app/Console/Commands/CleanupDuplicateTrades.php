<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateTrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-duplicate-trades 
                            {--dry-run : Show what would be cleaned up without making changes}
                            {--force : Force cleanup without confirmation}
                            {--delete : Delete duplicate trades instead of making them unique}
                            {--keep=newest : Which trade to keep (newest|oldest) when deleting duplicates}
                            {--delete-invalid : Delete ALL trades with NULL/empty/zero position_id (not just duplicates)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate trades - either delete duplicates or make them unique before creating constraints';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');
        $delete = $this->option('delete');
        $deleteInvalid = $this->option('delete-invalid');
        $keep = $this->option('keep');

        $this->info('🔍 Analyzing duplicate trades...');

        // Check for duplicates
        $stats = $this->analyzeDuplicates();

        if ($deleteInvalid) {
            $this->warn("🗑️  DELETE INVALID MODE: Will delete ALL trades with NULL/empty/zero position_id!");
            $this->warn("⚠️  This will delete {$stats['null_position_ids']} NULL/empty + {$stats['zero_position_ids']} zero position_id trades!");
            $this->warn("⚠️  This action cannot be undone!");
        } elseif ($stats['total_duplicates'] == 0) {
            $this->info('✅ No duplicate trades found! Database is clean.');
            return 0;
        }

        if (!$deleteInvalid) {
            $this->warn("⚠️  Found {$stats['total_duplicates']} duplicate position_id entries:");
            $this->table(['Type', 'Count'], [
                ['NULL/Empty position_id', $stats['null_position_ids']],
                ['Zero position_id', $stats['zero_position_ids']],
                ['Duplicate within account', $stats['account_duplicates']],
                ['Duplicate across accounts', $stats['global_duplicates']]
            ]);
        }

        if ($delete && !$deleteInvalid) {
            $this->warn("🗑️  DELETE MODE: Will delete duplicate trades, keeping only the {$keep} one from each group!");
            $this->warn("⚠️  This action cannot be undone!");
        } elseif (!$deleteInvalid) {
            $this->info("🔧 PRESERVE MODE: Will make duplicates unique by modifying position_id (no data loss)");
        }

        if ($isDryRun) {
            $this->info('🔍 DRY RUN - No changes made. Run without --dry-run to execute cleanup.');
            if ($deleteInvalid) {
                $this->showInvalidTradesPreview();
            } else {
                $this->showSampleDuplicates();
                if ($delete && $stats['total_duplicates'] < 100000) {
                    // Only show detailed preview for smaller datasets
                    $this->showDeletionPreview();
                } elseif ($delete) {
                    $this->info("📋 Large dataset detected ({$stats['total_duplicates']} duplicates)");
                    $this->info("💡 Will delete duplicates keeping the {$keep} trade from each (account_id, position_id) group");
                    $this->warn("⚠️  This will permanently delete approximately " . number_format($stats['total_duplicates']) . " trades!");
                }
            }
            return 0;
        }

        if (!$force && !$this->confirm('Do you want to proceed with cleaning up these duplicates?')) {
            $this->info('Cleanup cancelled.');
            return 0;
        }

        $this->info('🧹 Starting cleanup process...');

        if ($deleteInvalid) {
            $this->deleteInvalidTrades();
        } elseif ($delete) {
            $this->deleteDuplicates($keep);
        } else {
            $this->cleanupDuplicates();
        }

        $this->info('✅ Cleanup completed! Verifying results...');

        $newStats = $this->analyzeDuplicates();
        if ($deleteInvalid) {
            if ($newStats['null_position_ids'] == 0 && $newStats['zero_position_ids'] == 0) {
                $this->info('✅ All invalid trades deleted successfully!');
            } else {
                $this->error("❌ Still found invalid trades. Manual intervention may be required.");
            }
        } elseif ($newStats['total_duplicates'] == 0) {
            $this->info('✅ All duplicates cleaned up successfully!');
            $this->info('🚀 You can now run the database migration safely.');
        } else {
            $this->error("❌ Still found {$newStats['total_duplicates']} duplicates. Manual intervention may be required.");
        }

        return 0;
    }

    private function analyzeDuplicates(): array
    {
        $this->info('📊 Analyzing 495k+ trades efficiently...');

        // Get accurate duplicate counts and total counts
        $stats = DB::selectOne("
            SELECT 
                -- Total counts for delete-invalid mode
                (SELECT COUNT(*) FROM trades WHERE position_id IS NULL OR position_id = '' OR TRIM(position_id) = '') as total_null_count,
                (SELECT COUNT(*) FROM trades WHERE position_id = '0' OR position_id = 0) as total_zero_count,
                
                -- Count NULL/empty that have duplicates within same account
                (SELECT COUNT(*) - COUNT(DISTINCT account_id) 
                 FROM trades 
                 WHERE position_id IS NULL OR position_id = '' OR TRIM(position_id) = '') as null_duplicates,
                
                -- Count zero position_id that have duplicates within same account  
                (SELECT COUNT(*) - COUNT(DISTINCT account_id) 
                 FROM trades 
                 WHERE position_id = '0' OR position_id = 0) as zero_duplicates,
                
                -- Count normal position_id duplicates within accounts
                (SELECT COUNT(*) - COUNT(DISTINCT CONCAT(account_id, '-', position_id)) 
                 FROM trades 
                 WHERE position_id IS NOT NULL 
                 AND position_id != '' 
                 AND position_id != '0' 
                 AND position_id != 0) as account_duplicates,
                
                -- Count global duplicates (same position_id across different accounts) 
                (SELECT COUNT(*) - COUNT(DISTINCT position_id) 
                 FROM trades 
                 WHERE position_id IS NOT NULL 
                 AND position_id != '' 
                 AND position_id != '0' 
                 AND position_id != 0) as global_duplicates
        ");

        return [
            'null_position_ids' => max(0, (int)$stats->total_null_count), // Total count for delete-invalid mode
            'zero_position_ids' => max(0, (int)$stats->total_zero_count), // Total count for delete-invalid mode
            'null_duplicates' => max(0, (int)$stats->null_duplicates),    // Actual duplicates
            'zero_duplicates' => max(0, (int)$stats->zero_duplicates),    // Actual duplicates
            'account_duplicates' => max(0, (int)$stats->account_duplicates),
            'global_duplicates' => max(0, (int)$stats->global_duplicates),
            'total_duplicates' => max(0, (int)($stats->null_duplicates + $stats->zero_duplicates + $stats->account_duplicates + $stats->global_duplicates))
        ];
    }

    private function showSampleDuplicates(): void
    {
        $this->info('📋 Sample duplicate entries (optimized for large dataset):');

        // Show sample NULL/empty/zero position_ids (limit 5)
        $nullSamples = DB::select("
            SELECT id, account_id, position_id, created_at 
            FROM trades 
            WHERE position_id IS NULL OR position_id = '' OR position_id = '0' OR position_id = 0
            LIMIT 5
        ");

        if (!empty($nullSamples)) {
            $this->info('NULL/Zero position_id samples:');
            $headers = ['ID', 'Account ID', 'Position ID', 'Created At'];
            $rows = array_map(fn($row) => [$row->id, $row->account_id, $row->position_id ?? 'NULL', $row->created_at], $nullSamples);
            $this->table($headers, $rows);
        }

        // Show sample account duplicates (limit 3 groups)
        $duplicateSamples = DB::select("
            SELECT account_id, position_id, COUNT(*) as duplicate_count,
                   MIN(created_at) as oldest, MAX(created_at) as newest
            FROM trades 
            WHERE position_id IS NOT NULL AND position_id != '' 
            AND position_id != '0' AND position_id != 0
            GROUP BY account_id, position_id
            HAVING COUNT(*) > 1
            ORDER BY duplicate_count DESC
            LIMIT 3
        ");

        if (!empty($duplicateSamples)) {
            $this->info('Account duplicate samples:');
            $headers = ['Account ID', 'Position ID', 'Count', 'Oldest', 'Newest'];
            $rows = array_map(fn($row) => [$row->account_id, $row->position_id, $row->duplicate_count, $row->oldest, $row->newest], $duplicateSamples);
            $this->table($headers, $rows);
        }
    }

    private function cleanupDuplicates(): void
    {
        DB::transaction(function () {
            // Disable strict mode temporarily for cleanup
            DB::statement("SET SESSION sql_mode = ''");

            $this->info('1/4 Fixing NULL/empty position_id values...');
            $affected = DB::update("
                UPDATE trades 
                SET position_id = CONCAT('-null-', id) 
                WHERE position_id IS NULL OR position_id = '' OR TRIM(position_id) = ''
            ");
            $this->info("   Fixed {$affected} NULL/empty position_id entries");

            $this->info('2/4 Fixing zero position_id values...');
            $affected = DB::update("
                UPDATE trades 
                SET position_id = CONCAT('-zero-', id) 
                WHERE position_id = '0' OR position_id = 0
            ");
            $this->info("   Fixed {$affected} zero position_id entries");

            $this->info('3/4 Fixing duplicate position_ids within same account...');
            $affected = DB::update("
                UPDATE trades t1
                INNER JOIN (
                    SELECT account_id, position_id, MAX(id) as max_id
                    FROM trades 
                    WHERE position_id IS NOT NULL AND position_id != '' AND position_id NOT LIKE '-%'
                    GROUP BY account_id, position_id
                    HAVING COUNT(*) > 1
                ) t2 ON t1.account_id = t2.account_id AND t1.position_id = t2.position_id
                SET t1.position_id = CONCAT(t1.position_id, '-dup-', t1.id)
                WHERE t1.id != t2.max_id
            ");
            $this->info("   Fixed {$affected} duplicate entries within accounts");

            $this->info('4/4 Fixing global duplicate position_ids across accounts...');
            $affected = DB::update("
                UPDATE trades t1
                INNER JOIN (
                    SELECT position_id, MIN(id) as min_id
                    FROM trades 
                    WHERE position_id IS NOT NULL AND position_id != '' AND position_id NOT LIKE '-%'
                    GROUP BY position_id
                    HAVING COUNT(*) > 1
                ) t2 ON t1.position_id = t2.position_id
                SET t1.position_id = CONCAT(t1.position_id, '-global-', t1.id)
                WHERE t1.id != t2.min_id
            ");
            $this->info("   Fixed {$affected} global duplicate entries");

            // Restore strict mode
            DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
        });
    }

    private function deleteDuplicates($keep = 'newest')
    {
        $this->info("🗑️  Deleting duplicates, keeping the {$keep} trade from each group...");
        $this->info("⚡ Using optimized bulk deletion for large dataset...");

        $totalDeleted = 0;

        // Start manual transaction control for better handling of DDL + DML
        DB::statement("SET autocommit = 0");
        DB::statement("START TRANSACTION");

        try {
            // 1. Handle NULL/empty position_id duplicates
            $this->info("1/3 Handling NULL/empty position_id duplicates...");
            $orderClause = ($keep === 'newest') ? 'created_at DESC, id DESC' : 'created_at ASC, id ASC';

            $nullDeleted = DB::delete("
                DELETE t1 FROM trades t1
                INNER JOIN trades t2 ON t1.account_id = t2.account_id
                WHERE (t1.position_id IS NULL OR t1.position_id = '' OR TRIM(t1.position_id) = '')
                AND (t2.position_id IS NULL OR t2.position_id = '' OR TRIM(t2.position_id) = '')
                AND (
                    t1.created_at < t2.created_at OR 
                    (t1.created_at = t2.created_at AND t1.id < t2.id)
                ) = " . (($keep === 'newest') ? '1' : '0') . "
            ");
            $totalDeleted += $nullDeleted;
            $this->info("   Deleted {$nullDeleted} NULL/empty position_id duplicates");

            // 2. Handle zero position_id duplicates
            $this->info("2/3 Handling zero position_id duplicates...");
            $zeroDeleted = DB::delete("
                DELETE t1 FROM trades t1
                INNER JOIN trades t2 ON t1.account_id = t2.account_id
                WHERE (t1.position_id = '0' OR t1.position_id = 0)
                AND (t2.position_id = '0' OR t2.position_id = 0)
                AND (
                    t1.created_at < t2.created_at OR 
                    (t1.created_at = t2.created_at AND t1.id < t2.id)
                ) = " . (($keep === 'newest') ? '1' : '0') . "
            ");
            $totalDeleted += $zeroDeleted;
            $this->info("   Deleted {$zeroDeleted} zero position_id duplicates");

            // 3. Handle normal duplicates (same account_id + position_id)
            $this->info("3/3 Handling normal duplicate position_ids...");

            // Clean up any existing temp table first
            DB::statement("DROP TEMPORARY TABLE IF EXISTS trades_to_keep");

            // Create temp table with the IDs we want to KEEP (one per account_id + position_id)
            DB::statement("
                CREATE TEMPORARY TABLE trades_to_keep AS
                SELECT 
                    t1.id,
                    ROW_NUMBER() OVER (
                        PARTITION BY t1.account_id, t1.position_id 
                        ORDER BY {$orderClause}
                    ) as row_num
                FROM trades t1
                WHERE t1.position_id IS NOT NULL 
                AND t1.position_id != ''
                AND t1.position_id != '0'
                AND t1.position_id != 0
            ");

            // Add index for performance
            DB::statement("ALTER TABLE trades_to_keep ADD INDEX idx_keep (id)");

            // Delete all trades that are NOT in our "keep" list
            $normalDeleted = DB::delete("
                DELETE t FROM trades t
                LEFT JOIN trades_to_keep tk ON t.id = tk.id AND tk.row_num = 1
                WHERE tk.id IS NULL
                AND t.position_id IS NOT NULL 
                AND t.position_id != ''
                AND t.position_id != '0'
                AND t.position_id != 0
            ");
            $totalDeleted += $normalDeleted;
            $this->info("   Deleted {$normalDeleted} normal position_id duplicates");

            // Commit the transaction
            DB::statement("COMMIT");
            $this->info("✅ Total deleted: {$totalDeleted} duplicate trades, keeping the {$keep} from each group.");
        } catch (\Exception $e) {
            // Rollback on error
            try {
                DB::statement("ROLLBACK");
            } catch (\Exception $rollbackError) {
                // Ignore rollback errors
            }
            $this->error("❌ Error during deletion: " . $e->getMessage());
            throw $e;
        } finally {
            // Clean up temp table and restore autocommit
            try {
                DB::statement("DROP TEMPORARY TABLE IF EXISTS trades_to_keep");
                DB::statement("SET autocommit = 1");
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
        }
    }

    private function showDeletionPreview()
    {
        $this->info("📋 Deletion Preview (optimized for large dataset):");

        $keep = $this->option('keep');

        // Get comprehensive statistics including all duplicate types
        $stats = DB::selectOne("
            SELECT 
                COUNT(*) as total_trades,
                -- NULL/empty duplicates within accounts
                SUM(CASE WHEN position_id IS NULL OR position_id = '' OR TRIM(position_id) = '' THEN 1 ELSE 0 END) as null_trades,
                -- Zero duplicates within accounts  
                SUM(CASE WHEN position_id = '0' OR position_id = 0 THEN 1 ELSE 0 END) as zero_trades,
                -- Normal trades with valid position_ids
                SUM(CASE WHEN position_id IS NOT NULL AND position_id != '' AND position_id != '0' AND position_id != 0 THEN 1 ELSE 0 END) as normal_trades,
                -- Unique combinations for normal trades only
                COUNT(DISTINCT CASE WHEN position_id IS NOT NULL AND position_id != '' AND position_id != '0' AND position_id != 0 THEN CONCAT(account_id, '-', position_id) END) as unique_normal_combinations,
                -- Unique accounts for null/zero trades
                COUNT(DISTINCT CASE WHEN position_id IS NULL OR position_id = '' OR TRIM(position_id) = '' THEN account_id END) as unique_null_accounts,
                COUNT(DISTINCT CASE WHEN position_id = '0' OR position_id = 0 THEN account_id END) as unique_zero_accounts
            FROM trades
        ");

        // Calculate what would be deleted
        $null_to_delete = $stats->null_trades - $stats->unique_null_accounts;
        $zero_to_delete = $stats->zero_trades - $stats->unique_zero_accounts;
        $normal_to_delete = $stats->normal_trades - $stats->unique_normal_combinations;
        $total_to_delete = $null_to_delete + $zero_to_delete + $normal_to_delete;

        $this->table(['Category', 'Total', 'Unique', 'To Delete', 'To Keep'], [
            ['NULL/Empty position_id', number_format($stats->null_trades), number_format($stats->unique_null_accounts), number_format($null_to_delete), number_format($stats->unique_null_accounts)],
            ['Zero position_id', number_format($stats->zero_trades), number_format($stats->unique_zero_accounts), number_format($zero_to_delete), number_format($stats->unique_zero_accounts)],
            ['Normal position_id', number_format($stats->normal_trades), number_format($stats->unique_normal_combinations), number_format($normal_to_delete), number_format($stats->unique_normal_combinations)],
            ['TOTAL', number_format($stats->total_trades), '-', number_format($total_to_delete), number_format($stats->total_trades - $total_to_delete)]
        ]);

        if ($total_to_delete == 0) {
            $this->info("✅ No duplicates to delete!");
            return;
        }

        $this->warn("💡 Strategy: Keep the {$keep} trade from each group (by account_id + position_id)");
        $this->warn("⚠️  This will permanently delete " . number_format($total_to_delete) . " trades!");
        $this->info("✅ Will keep " . number_format($stats->total_trades - $total_to_delete) . " trades");
    }

    private function deleteInvalidTrades()
    {
        $this->info("🗑️  Deleting ALL trades with NULL/empty/zero position_id...");
        $this->info("⚡ Using optimized bulk deletion for large dataset...");

        // Start manual transaction control
        DB::statement("SET autocommit = 0");
        DB::statement("START TRANSACTION");

        try {
            // Delete all NULL/empty position_id trades
            $this->info("1/2 Deleting NULL/empty position_id trades...");
            $nullDeleted = DB::delete("
                DELETE FROM trades 
                WHERE position_id IS NULL 
                OR position_id = '' 
                OR TRIM(position_id) = ''
            ");
            $this->info("   Deleted {$nullDeleted} NULL/empty position_id trades");

            // Delete all zero position_id trades
            $this->info("2/2 Deleting zero position_id trades...");
            $zeroDeleted = DB::delete("
                DELETE FROM trades 
                WHERE position_id = '0' 
                OR position_id = 0
            ");
            $this->info("   Deleted {$zeroDeleted} zero position_id trades");

            $totalDeleted = $nullDeleted + $zeroDeleted;

            // Commit the transaction
            DB::statement("COMMIT");
            $this->info("✅ Total deleted: {$totalDeleted} invalid trades.");
        } catch (\Exception $e) {
            // Rollback on error
            try {
                DB::statement("ROLLBACK");
            } catch (\Exception $rollbackError) {
                // Ignore rollback errors
            }
            $this->error("❌ Error during deletion: " . $e->getMessage());
            throw $e;
        } finally {
            // Restore autocommit
            try {
                DB::statement("SET autocommit = 1");
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
        }
    }

    private function showInvalidTradesPreview()
    {
        $this->info("📋 Invalid Trades Preview:");

        // Get counts
        $stats = DB::selectOne("
            SELECT 
                SUM(CASE WHEN position_id IS NULL OR position_id = '' OR TRIM(position_id) = '' THEN 1 ELSE 0 END) as null_count,
                SUM(CASE WHEN position_id = '0' OR position_id = 0 THEN 1 ELSE 0 END) as zero_count,
                COUNT(*) as total_trades
            FROM trades
        ");

        $totalInvalid = $stats->null_count + $stats->zero_count;
        $validTrades = $stats->total_trades - $totalInvalid;

        $this->table(['Category', 'Count'], [
            ['NULL/Empty position_id trades', number_format($stats->null_count)],
            ['Zero position_id trades', number_format($stats->zero_count)],
            ['Total INVALID to DELETE', number_format($totalInvalid)],
            ['Valid trades to KEEP', number_format($validTrades)],
            ['Total trades', number_format($stats->total_trades)]
        ]);

        if ($totalInvalid == 0) {
            $this->info("✅ No invalid trades to delete!");
            return;
        }

        // Show samples
        $this->info("📋 Sample invalid trades (first 5):");
        $samples = DB::select("
            SELECT id, account_id, position_id, created_at 
            FROM trades 
            WHERE position_id IS NULL OR position_id = '' OR position_id = '0' OR position_id = 0
            ORDER BY created_at DESC
            LIMIT 5
        ");

        if (!empty($samples)) {
            $headers = ['ID', 'Account ID', 'Position ID', 'Created At'];
            $rows = array_map(fn($row) => [
                substr($row->id, 0, 8) . '...',
                substr($row->account_id, 0, 8) . '...',
                $row->position_id ?? 'NULL',
                $row->created_at
            ], $samples);
            $this->table($headers, $rows);
        }

        $this->warn("⚠️  This will permanently delete " . number_format($totalInvalid) . " trades!");
        $this->info("✅ Will keep " . number_format($validTrades) . " valid trades");
    }
}
