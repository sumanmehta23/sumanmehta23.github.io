<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts', 'last_trade_sync_position')) {
                $table->integer('last_trade_sync_position')
                    ->nullable()
                    ->default(0)
                    ->after('last_trade_sync_to')
                    ->comment('Last pagination position in trade history');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'last_trade_sync_position')) {
                $table->dropColumn('last_trade_sync_position');
            }
        });
    }
};
