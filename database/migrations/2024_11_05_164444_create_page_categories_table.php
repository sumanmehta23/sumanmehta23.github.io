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
        Schema::create('page_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('page_category_id');
            $table->string('category_name');
            $table->text('category_desc');
            $table->tinyInteger('is_active');
            $table->integer('order_by');
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_categories');
    }
};
