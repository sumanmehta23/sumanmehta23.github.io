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
        Schema::create('ib_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('ib_cat_id');
            $table->string('ib_cat_name');
            $table->string('ib_cat_type', 100)->default('ib');
            $table->text('ib_cat_desc')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ib_categories');
    }
};
