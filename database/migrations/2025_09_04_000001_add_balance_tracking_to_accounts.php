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
            $table->timestamp('last_balance_changed_at')->nullable()->after('last_balance_sync_at');
            $table->decimal('last_known_balance', 15, 2)->nullable()->after('last_balance_changed_at');
            $table->decimal('last_known_equity', 15, 2)->nullable()->after('last_known_balance');
            $table->boolean('has_balance_activity')->default(false)->after('last_known_equity');

            // Add indexes for performance
            $table->index(['has_balance_activity', 'last_balance_changed_at'], 'idx_balance_activity');
            $table->index(['last_balance_changed_at', 'last_balance_sync_at'], 'idx_balance_sync_comparison');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex('idx_balance_activity');
            $table->dropIndex('idx_balance_sync_comparison');
            $table->dropColumn([
                'last_balance_changed_at',
                'last_known_balance',
                'last_known_equity',
                'has_balance_activity'
            ]);
        });
    }
};
