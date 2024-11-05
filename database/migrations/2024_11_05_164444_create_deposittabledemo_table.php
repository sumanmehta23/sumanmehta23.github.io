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
        Schema::create('deposittabledemo', function (Blueprint $table) {
            $table->bigInteger('depositIndex', true);
            $table->integer('orderId')->nullable();
            $table->dateTime('date')->nullable();
            $table->string('gateway', 150)->nullable();
            $table->string('batchId')->nullable();
            $table->string('currency', 5)->default('USD');
            $table->double('amount', 10, 4)->default(0);
            $table->string('status', 25)->nullable();
            $table->string('clientAccountId');
            $table->string('clientTradeAccountId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposittabledemo');
    }
};
