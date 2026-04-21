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
            if (!Schema::hasColumn('accounts', 'last_trade_sync_position')) {
                $table->integer('last_trade_sync_position')->nullable()->after('trade_sync_status')
                    ->comment('Last position synced for trades (for pagination tracking)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'last_trade_sync_position')) {
                $table->dropColumn('last_trade_sync_position');
            }
        });
    }
};
