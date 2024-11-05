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
        Schema::create('categorylist', function (Blueprint $table) {
            $table->integer('categoryIndex', true);
            $table->dateTime('date');
            $table->string('categoryFor', 100);
            $table->string('categoryName');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorylist');
    }
};
