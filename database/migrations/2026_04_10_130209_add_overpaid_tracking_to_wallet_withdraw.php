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
        Schema::table('wallet_withdraw', function (Blueprint $table) {
            // Track overpaid amount withdrawn on this withdrawal
            $table->decimal('overpaid_amount', 20, 10)->nullable()->after('withdraw_amount')->comment('Amount that was overpaid (flagged entries) included in this withdrawal');

            // Track if this withdrawal included any overpaid commissions
            $table->tinyInteger('has_overpaid', false, true)->default(0)->after('overpaid_amount')->comment('1 if overpaid amount was included');

            // Add index for faster lookups
            $table->index(['user_id', 'has_overpaid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_withdraw', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'has_overpaid']);
            $table->dropColumn('has_overpaid');
            $table->dropColumn('overpaid_amount');
        });
    }
};
