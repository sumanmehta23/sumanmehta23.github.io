<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Performance improvements for BatchSyncTradesJob
     */
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            // CRITICAL: Covering index for the main sync query
            // This covers Trade::where('account_id')->select(['id', 'position_id', 'status', 'close_time', 'updated_at'])
            // All required fields are in the index, eliminating table lookups
            $table->index(['account_id', 'position_id', 'id', 'status', 'close_time', 'updated_at'], 'idx_trades_sync_covering');

            // PERFORMANCE: Optimize upsert operations
            // Composite index for faster duplicate detection during bulk upserts
            $table->index(['account_id', 'order_id'], 'idx_trades_account_order');

            // ANALYTICAL: Symbol-based queries optimization
            $table->index(['account_id', 'symbol', 'status'], 'idx_trades_account_symbol_status');

            // TEMPORAL: Optimize time-range queries with profit analysis
            $table->index(['account_id', 'open_time', 'close_time', 'profit'], 'idx_trades_account_time_profit');
        });

        // Analyze table after index creation for optimal query planning
        DB::statement('ANALYZE TABLE trades');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropIndex('idx_trades_sync_covering');
            $table->dropIndex('idx_trades_account_order');
            $table->dropIndex('idx_trades_account_symbol_status');
            $table->dropIndex('idx_trades_account_time_profit');
        });
    }
};
