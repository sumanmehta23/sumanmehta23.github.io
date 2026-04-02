<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts', 'last_trade_sync_from')) {
                $table->date('last_trade_sync_from')
                    ->nullable()
                    ->after('last_trade_sync_at')
                    ->comment('Start date of last trade sync range');
            }
            if (!Schema::hasColumn('accounts', 'last_trade_sync_to')) {
                $table->date('last_trade_sync_to')
                    ->nullable()
                    ->after('last_trade_sync_from')
                    ->comment('End date of last trade sync range');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'last_trade_sync_from')) {
                $table->dropColumn('last_trade_sync_from');
            }
            if (Schema::hasColumn('accounts', 'last_trade_sync_to')) {
                $table->dropColumn('last_trade_sync_to');
            }
        });
    }
};
