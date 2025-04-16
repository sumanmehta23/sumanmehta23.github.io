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
            $table->text('payout_req')->nullable()->after('code');
            $table->text('payout_res')->nullable()->after('payout_req');
            $table->string('transaction_id')->nullable()->after('payout_res');
            $table->string('approved_by')->nullable()->after('transaction_id');
            $table->timestamp('approved_date')->nullable()->after('approved_by'); // Fixed here
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_withdrawal', function (Blueprint $table) {
            $table->dropColumn(['payout_req', 'payout_res', 'transaction_id','approved_by','approved_date']);
        });
    }
};
