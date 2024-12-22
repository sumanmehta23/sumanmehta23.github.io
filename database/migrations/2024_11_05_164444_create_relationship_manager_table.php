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
        Schema::create('relationship_manager', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id', 50)->index('user_id');
            $table->string('rm_id', 50)->index('rm_id');
            $table->string('added_by',50)->nullable();
            $table->dateTime('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relationship_manager');
    }
};
