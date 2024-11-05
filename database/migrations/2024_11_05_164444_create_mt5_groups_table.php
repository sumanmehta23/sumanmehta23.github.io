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
        Schema::create('mt5_groups', function (Blueprint $table) {
            $table->integer('mt5_group_id', true);
            $table->string('mt5_group_name');
            $table->enum('mt5_group_type', ['demo', 'live'])->default('demo');
            $table->text('mt5_group_desc');
            $table->boolean('is_active')->default(true);
            $table->string('updated_by');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mt5_groups');
    }
};
