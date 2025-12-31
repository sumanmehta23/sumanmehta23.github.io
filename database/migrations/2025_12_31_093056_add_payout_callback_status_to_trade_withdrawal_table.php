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
        Schema::table('trade_withdrawal', function (Blueprint $table) {
            $table->text('payout_callback_status')
                  ->nullable()
                  ->after('payout_res');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_withdrawal', function (Blueprint $table) {
            $table->dropColumn('payout_callback_status');
        });
    }
};
