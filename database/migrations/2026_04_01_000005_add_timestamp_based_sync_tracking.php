<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts', 'last_trade_sync_timestamp')) {
                $table->integer('last_trade_sync_timestamp')
                    ->nullable()
                    ->after('last_trade_sync_to')
                    ->comment('Unix timestamp of the last synced trade (for incremental sync)');
            }
            // Keep position column for backward compatibility but will deprecate it
            if (Schema::hasColumn('accounts', 'last_trade_sync_position')) {
                $table->comment('Position column deprecated - use last_trade_sync_timestamp instead');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'last_trade_sync_timestamp')) {
                $table->dropColumn('last_trade_sync_timestamp');
            }
        });
    }
};
