<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            // CRITICAL: Index for the main sync query - Trade::where('account_id', $account->id)
            // This is the most performance-critical index for BatchSyncTradesJob
            $table->index('account_id', 'idx_trades_account_id');

            // CRITICAL: Unique index for position-based lookups and upserts
            // Used in: $existingTrades->get($positionId) and Trade::upsert(..., ['position_id'], ...)
            $table->unique('position_id', 'idx_trades_position_id_unique');

            // PERFORMANCE: Composite index for account + position queries
            // Optimizes queries that filter by both account and position
            $table->index(['account_id', 'position_id'], 'idx_trades_account_position');

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
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropIndex('idx_trades_account_id');
            $table->dropIndex('idx_trades_position_id_unique');
            $table->dropIndex('idx_trades_account_position');
            $table->dropIndex('idx_trades_status');
            $table->dropIndex('idx_trades_open_time');
            $table->dropIndex('idx_trades_close_time');
            $table->dropIndex('idx_trades_account_status');
            $table->dropIndex('idx_trades_account_open_time');
        });
    }
};
