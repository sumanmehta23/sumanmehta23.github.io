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
        Schema::table('accounts', function (Blueprint $table) {
            // CRITICAL: Index for account code filtering - all sync commands filter by whereNotNull('code')
            // Used in: Account::whereNotNull('code') (appears 8+ times in PrioritySyncAccountsCommand)
            $table->index('code', 'idx_accounts_code');

            // PERFORMANCE: Composite index for balance activity + code
            // Used in: balance optimization queries that filter by both has_balance_activity and code
            $table->index(['has_balance_activity', 'code'], 'idx_accounts_balance_activity_code');

            // PERFORMANCE: Composite index for sync status + code
            // Used in: queries filtering by sync_status = 'needs_retry' with whereNotNull('code')
            $table->index(['sync_status', 'code'], 'idx_accounts_sync_status_code');

            // TEMPORAL: Index for last_sync_attempt_at for never-synced queries
            // Used in: ->whereNull('last_sync_attempt_at') queries
            $table->index('last_sync_attempt_at', 'idx_accounts_last_sync_attempt');

            // COMPOSITE: Complex filtering index for stale account detection
            // Optimizes queries filtering by code + sync_status + last_balance_sync_at
            $table->index(['code', 'sync_status', 'last_balance_sync_at'], 'idx_accounts_stale_detection');

            // COMPOSITE: Balance tracking composite index
            // Optimizes balance change detection queries
            $table->index(['code', 'has_balance_activity', 'last_balance_changed_at'], 'idx_accounts_balance_tracking');

            // PERFORMANCE: Platform filtering (if platform column exists)
            // Many queries might filter by platform type
            // Note: Only add if 'platform' column exists
            // $table->index('platform', 'idx_accounts_platform');

            // COMPOSITE: Complete sync priority index
            // Covers the most common filtering pattern: code + sync_status + balance_activity + sync_times
            $table->index(['code', 'sync_status', 'has_balance_activity', 'last_balance_sync_at'], 'idx_accounts_sync_priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex('idx_accounts_code');
            $table->dropIndex('idx_accounts_balance_activity_code');
            $table->dropIndex('idx_accounts_sync_status_code');
            $table->dropIndex('idx_accounts_last_sync_attempt');
            $table->dropIndex('idx_accounts_stale_detection');
            $table->dropIndex('idx_accounts_balance_tracking');
            // $table->dropIndex('idx_accounts_platform');
            $table->dropIndex('idx_accounts_sync_priority');
        });
    }
};
