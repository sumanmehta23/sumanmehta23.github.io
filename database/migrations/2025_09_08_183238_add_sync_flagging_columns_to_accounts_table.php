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
            // Flagging columns for problematic accounts
            $table->string('sync_flag_reason')->nullable()->after('sync_error'); // reason for flagging (e.g., 'repeated_stuck_jobs', 'too_many_trades')
            $table->timestamp('sync_flagged_at')->nullable()->after('sync_flag_reason'); // when the account was flagged
            $table->integer('sync_stuck_count')->default(0)->after('sync_flagged_at'); // count of times account got stuck

            // Index for querying flagged accounts
            $table->index(['sync_status', 'sync_flagged_at'], 'idx_accounts_flagged');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex('idx_accounts_flagged');
            $table->dropColumn(['sync_flag_reason', 'sync_flagged_at', 'sync_stuck_count']);
        });
    }
};
