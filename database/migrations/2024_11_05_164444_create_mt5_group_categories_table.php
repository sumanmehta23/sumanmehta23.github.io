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
        Schema::create('mt5_group_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('mt5_grp_cat_id');
            $table->string('mt5_grp_cat_name');
            $table->string('mt5_grp_cat_type', 100);
            $table->text('mt5_grp_cat_desc')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->string('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mt5_group_categories');
    }
};
