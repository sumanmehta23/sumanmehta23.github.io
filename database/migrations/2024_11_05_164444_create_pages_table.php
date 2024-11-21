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
        Schema::create('pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('page_id');
            $table->integer('page_category_id')->nullable()->index('page_category_id');
            $table->string('pagename');
            $table->string('filename');
            $table->boolean('is_submenu')->default(false);
            $table->boolean('active')->default(true);
            $table->integer('page_order');
            $table->string('icon', 50);
            $table->integer('show_in_menu')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
