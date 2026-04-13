<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedInteger('pending_deal_count')->nullable()->after('deals_sync_complete')
                ->comment('Estimated deals since last sync (from getDealTotals API)');
            $table->timestamp('pending_deal_count_at')->nullable()->after('pending_deal_count')
                ->comment('When pending_deal_count was last updated');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['pending_deal_count', 'pending_deal_count_at']);
        });
    }
};
