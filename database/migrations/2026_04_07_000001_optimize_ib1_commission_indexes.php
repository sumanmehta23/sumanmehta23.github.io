<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * OPTIMIZATION (April 7, 2026): Add optimal indexes for slow commission lookup queries
     * Problem: SyncAccountTradesJob queries WHERE code = ? AND order_id IN (?) 1300+ms per page
     * Solution: Add (code, order_id) index - matches query pattern for efficient lookup
     */
    public function up(): void
    {
        Schema::table('ib1_commission', function (Blueprint $table) {
            // Add optimized index for the slow query pattern in SyncAccountTradesJob
            // WHERE code = ? AND order_id IN (?) should use (code, order_id) index
            if (!$this->indexExists('ib1_commission', 'idx_code_order_id')) {
                $table->index(['code', 'order_id'], 'idx_code_order_id');
            }

            // Also add index for DistributeIbCommissionJob queries
            // WHERE code = ? AND status != ? runs frequently
            if (!$this->indexExists('ib1_commission', 'idx_code_status')) {
                $table->index(['code', 'status'], 'idx_code_status');
            }

            // CRITICAL (April 7, 2026): Add composite index for 2298ms LEFT JOIN query
            // Query: WHERE user_id IN (...) AND orderstate = 4 AND status NOT IN (1,10)
            // Composite index allows single-pass lookup instead of table scan
            if (!$this->indexExists('ib1_commission', 'idx_user_orderstate_status')) {
                $table->index(['user_id', 'orderstate', 'status'], 'idx_user_orderstate_status');
            }
        });

        Schema::table('ib_wallet', function (Blueprint $table) {
            // CRITICAL: Add index for LEFT JOIN condition
            // Join condition: ON ib1_commission.id = ib_wallet.ib1_commission_id AND user_id = ?
            // This index optimizes the JOIN lookup for non-matching rows (whereNull check)
            if (!$this->indexExists('ib_wallet', 'idx_commission_user')) {
                $table->index(['ib1_commission_id', 'user_id'], 'idx_commission_user');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ib1_commission', function (Blueprint $table) {
            if ($this->indexExists('ib1_commission', 'idx_code_order_id')) {
                $table->dropIndex('idx_code_order_id');
            }
            if ($this->indexExists('ib1_commission', 'idx_code_status')) {
                $table->dropIndex('idx_code_status');
            }
            if ($this->indexExists('ib1_commission', 'idx_user_orderstate_status')) {
                $table->dropIndex('idx_user_orderstate_status');
            }
        });

        Schema::table('ib_wallet', function (Blueprint $table) {
            if ($this->indexExists('ib_wallet', 'idx_commission_user')) {
                $table->dropIndex('idx_commission_user');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select(
            "SELECT DISTINCT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?",
            [env('DB_DATABASE'), $table, $indexName]
        );
        return count($indexes) > 0;
    }
};
