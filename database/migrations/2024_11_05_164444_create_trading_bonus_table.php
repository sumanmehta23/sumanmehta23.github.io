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
        Schema::create('trading_bonus', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('trade_id')->nullable();
            $table->string('bonus_amount', 100)->nullable();
            $table->string('comments', 100)->nullable();
            $table->timestamp('Registered_Date')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trading_bonus');
    }
};
