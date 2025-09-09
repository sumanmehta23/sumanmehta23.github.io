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
            // Remove the simple last_deal_sync_at if it exists
            if (Schema::hasColumn('accounts', 'last_deal_sync_at')) {
                $table->dropColumn('last_deal_sync_at');
            }

            // Add columns to track the actual time range of deals fetched
            $table->timestamp('deals_synced_from')->nullable()->after('last_balance_sync_at')
                ->comment('Earliest deal time in our database for this account');
            $table->timestamp('deals_synced_to')->nullable()->after('deals_synced_from')
                ->comment('Latest deal time in our database for this account');
            $table->timestamp('deals_last_fetch_at')->nullable()->after('deals_synced_to')
                ->comment('When we last attempted to fetch deals');
            $table->boolean('deals_sync_complete')->default(false)->after('deals_last_fetch_at')
                ->comment('Whether we have complete deal history up to deals_synced_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn([
                'deals_synced_from',
                'deals_synced_to',
                'deals_last_fetch_at',
                'deals_sync_complete'
            ]);

            // Restore the simple column if needed
            $table->timestamp('last_deal_sync_at')->nullable()->after('last_balance_sync_at');
        });
    }
};
