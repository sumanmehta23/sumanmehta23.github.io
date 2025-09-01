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
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('accounts', 'last_trade_at')) {
                $table->timestamp('last_trade_at')->nullable()->index();
            }

            if (!Schema::hasColumn('accounts', 'sync_tier')) {
                $table->enum('sync_tier', ['very_active', 'active', 'inactive', 'dormant'])
                    ->default('inactive')
                    ->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['last_trade_at', 'sync_tier']);
        });
    }
};
