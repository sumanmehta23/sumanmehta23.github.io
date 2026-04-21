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
            if (!Schema::hasColumn('accounts', 'trade_sync_status')) {
                $table->enum('trade_sync_status', ['pending', 'success', 'not_found', 'error'])
                    ->nullable()
                    ->after('last_trade_sync_at')
                    ->comment('Status of last trade sync: pending, success, not_found, or error');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'trade_sync_status')) {
                $table->dropColumn('trade_sync_status');
            }
        });
    }
};
