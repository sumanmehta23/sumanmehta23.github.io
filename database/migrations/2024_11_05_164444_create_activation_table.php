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
        Schema::create('activation', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->dateTime('create_time')->useCurrent();
            $table->mediumText('activation_key')->nullable();
            $table->boolean('activated')->default(false);
            $table->string('name', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('mobile', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activation');
    }
};
