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
        Schema::create('bankdetails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('bankName', 150)->nullable();
            $table->string('location', 100)->nullable();
            $table->mediumText('bankDetails')->nullable();
            $table->string('accountNumber')->nullable();
            $table->boolean('status')->default(true);
            $table->string('swiftCode')->nullable();
            $table->string('ifscCode')->nullable();
            $table->string('accountName', 200)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bankdetails');
    }
};
