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
        Schema::create('bonusdeposit', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 150)->nullable();
            $table->string('code', 150)->default('noCode');
            $table->string('comment')->nullable();
            $table->decimal('amount')->nullable();
            $table->dateTime('date')->nullable();
            $table->string('tradeAccId')->nullable();
            $table->string('uid', 200)->nullable();
            $table->string('email', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonusdeposit');
    }
};
