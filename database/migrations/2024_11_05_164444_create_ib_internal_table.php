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
        Schema::create('ib_internal', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('email', 100)->nullable();
            $table->string('trade_id', 100)->nullable();
            $table->string('ib_amount', 100)->nullable();
            $table->string('transfer_to', 100)->nullable()->default('IB Wallet');
            $table->timestamp('transfer_date')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ib_internal');
    }
};
