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
        Schema::create('total_balance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email', 50)->nullable();
            $table->string('trade_id', 50)->nullable();
            $table->string('deposit_amount', 50)->nullable()->default('0');
            $table->string('withdraw_amount', 50)->nullable()->default('0');
            $table->string('trading_deposited', 50)->nullable()->default('0');
            $table->string('trading_withdrawal', 100)->nullable()->default('0');
            $table->string('refer_commission_amount', 50)->nullable()->default('0');
            $table->timestamp('reg_date')->useCurrentOnUpdate()->useCurrent();
            $table->string('deposit_type')->nullable();
            $table->string('status', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('total_balance');
    }
};
