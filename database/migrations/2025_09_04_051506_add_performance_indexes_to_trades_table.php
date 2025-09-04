<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // STEP 1: Clean up duplicate position_id entries before creating unique constraint
        $this->cleanupDuplicatePositionIds();

        Schema::table('trades', function (Blueprint $table) {
            // CRITICAL: Index for the main sync query - Trade::where('account_id', $account->id)
            // This is the most performance-critical index for BatchSyncTradesJob
            $table->index('account_id', 'idx_trades_account_id');

            // CRITICAL: Composite unique index for account + position (better than position_id alone)
            // This prevents duplicates per account while allowing position_id reuse across accounts
            $table->unique(['account_id', 'position_id'], 'idx_trades_account_position_unique');

            // PERFORMANCE: Index for position-based lookups
            // Note: Not unique because position_id might be reused across different accounts
            $table->index('position_id', 'idx_trades_position_id');

            // ANALYTICAL: Index for status-based queries (open/closed trades)
            // Useful for reporting and analytics on trade statuses
            $table->index('status', 'idx_trades_status');

            // TEMPORAL: Index for time-based queries
            // Optimizes queries filtering by open_time and close_time
            $table->index('open_time', 'idx_trades_open_time');
            $table->index('close_time', 'idx_trades_close_time');

            // COMPOSITE: Account + Status for filtered account queries
            // Optimizes queries like "get all open trades for account"
            $table->index(['account_id', 'status'], 'idx_trades_account_status');

            // COMPOSITE: Account + Time for chronological account queries
            // Optimizes time-range queries per account
            $table->index(['account_id', 'open_time'], 'idx_trades_account_open_time');
        });
    }

    /**
     * Clean up duplicate position_id entries before creating unique constraints
     */
    private function cleanupDuplicatePositionIds(): void
    {
        DB::statement("SET SESSION sql_mode = ''");

        // Step 1: Handle NULL or 0 position_id values
        // Set them to negative values based on their ID to make them unique
        DB::statement("
            UPDATE trades 
            SET position_id = CONCAT('-', id) 
            WHERE position_id IS NULL OR position_id = 0 OR position_id = ''
        ");

        // Step 2: Handle duplicate position_ids within the same account
        // Keep the most recent one (highest ID) and modify others
        DB::statement("
            UPDATE trades t1
            INNER JOIN (
                SELECT account_id, position_id, MAX(id) as max_id
                FROM trades 
                WHERE position_id IS NOT NULL AND position_id != 0 AND position_id != ''
                GROUP BY account_id, position_id
                HAVING COUNT(*) > 1
            ) t2 ON t1.account_id = t2.account_id AND t1.position_id = t2.position_id
            SET t1.position_id = CONCAT(t1.position_id, '_dup_', t1.id)
            WHERE t1.id != t2.max_id
        ");

        // Step 3: Handle any remaining global duplicates across different accounts
        // This shouldn't happen if position_id is properly scoped to accounts, but safety measure
        DB::statement("
            UPDATE trades t1
            INNER JOIN (
                SELECT position_id, MIN(id) as min_id
                FROM trades 
                GROUP BY position_id
                HAVING COUNT(*) > 1
            ) t2 ON t1.position_id = t2.position_id
            SET t1.position_id = CONCAT(t1.position_id, '_global_', t1.id)
            WHERE t1.id != t2.min_id
        ");

        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropIndex('idx_trades_account_id');
            $table->dropIndex('idx_trades_account_position_unique');
            $table->dropIndex('idx_trades_position_id');
            $table->dropIndex('idx_trades_status');
            $table->dropIndex('idx_trades_open_time');
            $table->dropIndex('idx_trades_close_time');
            $table->dropIndex('idx_trades_account_status');
            $table->dropIndex('idx_trades_account_open_time');
        });
    }
};
