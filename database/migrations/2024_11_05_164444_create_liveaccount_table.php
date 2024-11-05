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
        Schema::create('liveaccount', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('email', 50)->nullable();
            $table->string('trade_id', 50)->nullable();
            $table->integer('account_type')->nullable();
            $table->decimal('credit', 10)->nullable();
            $table->string('leverage');
            $table->string('currency', 20)->default('USD');
            $table->decimal('Balance', 15)->default(0);
            $table->double('equity', 15, 4)->nullable()->default(0);
            $table->string('tradePlatform', 100)->default('MetaTrader5');
            $table->integer('lotsCompleted')->default(0);
            $table->double('MarginFree', 15, 4)->default(0);
            $table->double('MarginLevel', 15, 4)->default(0);
            $table->string('MarginLevelType')->default('ok');
            $table->double('adj', 10, 4)->default(0);
            $table->double('deposit', 15, 4)->default(0);
            $table->double('withdraw', 15, 4)->default(0);
            $table->double('internal_transfer', 15, 4)->default(0);
            $table->double('internalDeposit', 15, 4)->default(0);
            $table->string('trader_pwd', 200)->nullable();
            $table->string('invester_pwd', 200)->nullable();
            $table->string('phone_pwd', 200)->nullable();
            $table->timestamp('Registered_Date')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->string('status', 50)->default('active');
            $table->double('bonusDeposit', 15, 4)->default(0);
            $table->double('wBonusDeposit', 15, 4)->default(0);
            $table->string('ib1', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liveaccount');
    }
};
