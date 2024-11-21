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
        Schema::create('promotions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('index');
            $table->string('title', 200)->nullable();
            $table->dateTime('date')->nullable();
            $table->string('subtitle', 200)->nullable();
            $table->string('slag', 200)->nullable();
            $table->string('url')->nullable();
            $table->mediumText('description')->nullable();
            $table->string('img')->nullable();
            $table->boolean('status')->default(true);
            $table->string('catrgory')->nullable();
            $table->string('promotionId', 150)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
