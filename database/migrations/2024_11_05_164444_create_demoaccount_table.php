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
        Schema::create('demoaccount', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->nullable();
            $table->string('trade_id', 50)->nullable();
            $table->string('account_type', 55)->nullable();
            $table->string('leverage');
            $table->string('currency', 20)->default('USD');
            $table->decimal('Balance', 15)->default(0);
            $table->decimal('credit', 15)->nullable()->default(0);
            $table->double('equity')->nullable()->default(0);
            $table->string('tradePlatform', 100)->default('MetaTrader5');
            $table->integer('lotsCompleted')->default(0);
            $table->double('MarginFree')->default(0);
            $table->double('MarginLevel')->default(0);
            $table->string('MarginLevelType', 60)->default('ok');
            $table->double('adj')->default(0);
            $table->double('deposit')->default(0);
            $table->double('withdraw')->default(0);
            $table->double('internal_transfer')->default(0);
            $table->double('internalDeposit')->default(0);
            $table->string('trader_pwd', 200)->nullable();
            $table->string('invester_pwd', 200)->nullable();
            $table->string('phone_pwd', 200)->nullable();
            $table->dateTime('Registered_Date')->useCurrent();
            $table->string('status', 50)->default('active');
            $table->double('bonusDeposit')->default(0);
            $table->double('wBonusDeposit')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demoaccount');
    }
};
