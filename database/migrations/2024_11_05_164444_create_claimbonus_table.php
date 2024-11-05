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
        Schema::create('claimbonus', function (Blueprint $table) {
            $table->bigInteger('indexNo', true);
            $table->string('uniqueId', 200)->nullable();
            $table->integer('bonusRdmType')->default(0);
            $table->string('bonusType', 200)->nullable();
            $table->string('userId', 200)->nullable();
            $table->string('refUserId', 200)->nullable();
            $table->string('tradingAccId', 150)->nullable();
            $table->decimal('amount', 10, 4)->default(0);
            $table->dateTime('date')->nullable();
            $table->string('typeAlias')->nullable();
            $table->dateTime('claimedOn')->nullable();
            $table->string('status', 50)->default('pending');
            $table->boolean('statusCode')->default(false);
            $table->dateTime('expDate')->nullable();
            $table->integer('expState')->default(0);
            $table->integer('bonusState')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claimbonus');
    }
};
