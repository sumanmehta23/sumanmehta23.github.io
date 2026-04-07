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
            // CRITICAL FIX (April 7, 2026): Column order matters for NOT EXISTS performance!
            // Query: WHERE NOT EXISTS (SELECT 1 FROM ib_wallet WHERE user_id = ? AND ib1_commission_id = ?)
            // Index (user_id, ib1_commission_id) allows MySQL to:
            // 1. Quickly filter to rows matching user_id (very selective)
            // 2. Then check if any have matching ib1_commission_id
            // Previous index (ib1_commission_id, user_id) forced full table scan!
            if (!$this->indexExists('ib_wallet', 'idx_user_commission')) {
                $table->index(['user_id', 'ib1_commission_id'], 'idx_user_commission');
            }
            // Drop old inefficient index if it exists
            if ($this->indexExists('ib_wallet', 'idx_commission_user')) {
                $table->dropIndex('idx_commission_user');
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
            if ($this->indexExists('ib_wallet', 'idx_user_commission')) {
                $table->dropIndex('idx_user_commission');
            }
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
