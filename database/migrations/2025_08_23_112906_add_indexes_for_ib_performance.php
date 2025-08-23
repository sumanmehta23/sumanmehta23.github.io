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
        Schema::table('ib_wallet', function (Blueprint $table) {
            // Add index on user_id for faster JOINs
            $table->index('user_id', 'idx_ib_wallet_user_id');

            // Add composite indexes for aggregation queries
            $table->index(['user_id', 'ib_wallet'], 'idx_ib_wallet_user_deposit');
            $table->index(['user_id', 'ib_withdraw'], 'idx_ib_wallet_user_withdraw');
        });

        Schema::table('ib1', function (Blueprint $table) {
            // Add composite index for common WHERE conditions
            $table->index(['status', 'deleted_at'], 'idx_ib1_status_deleted');

            // Add index on user_id for JOINs
            $table->index('user_id', 'idx_ib1_user_id');

            // Add index on commonly searched fields
            $table->index('indexId', 'idx_ib1_index_id');
            $table->index('email', 'idx_ib1_email');
            $table->index('created_at', 'idx_ib1_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ib_wallet', function (Blueprint $table) {
            $table->dropIndex('idx_ib_wallet_user_id');
            $table->dropIndex('idx_ib_wallet_user_deposit');
            $table->dropIndex('idx_ib_wallet_user_withdraw');
        });

        Schema::table('ib1', function (Blueprint $table) {
            $table->dropIndex('idx_ib1_status_deleted');
            $table->dropIndex('idx_ib1_user_id');
            $table->dropIndex('idx_ib1_index_id');
            $table->dropIndex('idx_ib1_email');
            $table->dropIndex('idx_ib1_created_at');
        });
    }
};
