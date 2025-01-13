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
        // Schema::create('permissions', function (Blueprint $table) {
        //     $table->uuid('id')->primary();
        //     $table->uuid('role_id');
        //     $table->uuid('page_id');
        //     $table->uuid('created_by');
        //     $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        //     $table->foreign('page_id')->references('id')->on('pages')->cascadeOnDelete();
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('permissions');
    }
};
