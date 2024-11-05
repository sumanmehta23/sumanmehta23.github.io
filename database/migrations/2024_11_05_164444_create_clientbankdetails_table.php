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
        Schema::create('clientbankdetails', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('bankName', 150)->nullable();
            $table->string('branch', 100)->nullable();
            $table->mediumText('bankDetails')->nullable();
            $table->string('accountNumber')->nullable();
            $table->string('status', 50)->default('pending');
            $table->string('code')->nullable();
            $table->string('swift_code', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->dateTime('date')->nullable();
            $table->string('email', 50)->nullable();
            $table->string('ClientName', 50)->nullable();
            $table->string('address')->nullable();
            $table->dateTime('processedOn')->nullable();
            $table->string('processedBy')->nullable();
            $table->string('document')->nullable();
            $table->string('userId')->nullable();
            $table->string('comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientbankdetails');
    }
};
