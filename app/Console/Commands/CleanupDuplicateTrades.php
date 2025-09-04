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
                            {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate trades before creating unique constraints';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔍 Analyzing duplicate trades...');

        // Check for duplicates
        $stats = $this->analyzeDuplicates();

        if ($stats['total_duplicates'] == 0) {
            $this->info('✅ No duplicate trades found! Database is clean.');
            return 0;
        }

        $this->warn("⚠️  Found {$stats['total_duplicates']} duplicate position_id entries:");
        $this->table(['Type', 'Count'], [
            ['NULL/Empty position_id', $stats['null_position_ids']],
            ['Zero position_id', $stats['zero_position_ids']],
            ['Duplicate within account', $stats['account_duplicates']],
            ['Duplicate across accounts', $stats['global_duplicates']]
        ]);

        if ($isDryRun) {
            $this->info('🔍 DRY RUN - No changes made. Run without --dry-run to execute cleanup.');
            $this->showSampleDuplicates();
            return 0;
        }

        if (!$force && !$this->confirm('Do you want to proceed with cleaning up these duplicates?')) {
            $this->info('Cleanup cancelled.');
            return 0;
        }

        $this->info('🧹 Starting cleanup process...');

        $this->cleanupDuplicates();

        $this->info('✅ Cleanup completed! Verifying results...');

        $newStats = $this->analyzeDuplicates();
        if ($newStats['total_duplicates'] == 0) {
            $this->info('✅ All duplicates cleaned up successfully!');
            $this->info('🚀 You can now run the database migration safely.');
        } else {
            $this->error("❌ Still found {$newStats['total_duplicates']} duplicates. Manual intervention may be required.");
        }

        return 0;
    }

    private function analyzeDuplicates(): array
    {
        // Count NULL/empty position_ids
        $nullPositionIds = DB::selectOne("
            SELECT COUNT(*) as count 
            FROM trades 
            WHERE position_id IS NULL OR position_id = '' OR TRIM(position_id) = ''
        ")->count;

        // Count zero position_ids
        $zeroPositionIds = DB::selectOne("
            SELECT COUNT(*) as count 
            FROM trades 
            WHERE position_id = '0' OR position_id = 0
        ")->count;

        // Count duplicates within the same account
        $accountDuplicates = DB::selectOne("
            SELECT COUNT(*) - COUNT(DISTINCT CONCAT(account_id, '-', position_id)) as count
            FROM trades 
            WHERE position_id IS NOT NULL AND position_id != '' AND position_id != '0' AND position_id != 0
        ")->count;

        // Count global duplicates (same position_id across different accounts)
        $globalDuplicates = DB::selectOne("
            SELECT COUNT(*) - COUNT(DISTINCT position_id) as count
            FROM trades 
            WHERE position_id IS NOT NULL AND position_id != '' AND position_id != '0' AND position_id != 0
        ")->count;

        return [
            'null_position_ids' => $nullPositionIds,
            'zero_position_ids' => $zeroPositionIds,
            'account_duplicates' => $accountDuplicates,
            'global_duplicates' => $globalDuplicates,
            'total_duplicates' => $nullPositionIds + $zeroPositionIds + $accountDuplicates + $globalDuplicates
        ];
    }

    private function showSampleDuplicates(): void
    {
        $this->info('📋 Sample duplicate entries:');

        // Show sample NULL/empty
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

        // Show sample duplicates within accounts
        $duplicateSamples = DB::select("
            SELECT t1.id, t1.account_id, t1.position_id, t1.created_at, COUNT(*) as duplicate_count
            FROM trades t1
            INNER JOIN trades t2 ON t1.account_id = t2.account_id AND t1.position_id = t2.position_id AND t1.id != t2.id
            WHERE t1.position_id IS NOT NULL AND t1.position_id != '' AND t1.position_id != '0' AND t1.position_id != 0
            GROUP BY t1.id, t1.account_id, t1.position_id, t1.created_at
            LIMIT 5
        ");

        if (!empty($duplicateSamples)) {
            $this->info('Account duplicate samples:');
            $headers = ['ID', 'Account ID', 'Position ID', 'Created At', 'Duplicate Count'];
            $rows = array_map(fn($row) => [$row->id, $row->account_id, $row->position_id, $row->created_at, $row->duplicate_count], $duplicateSamples);
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
}
