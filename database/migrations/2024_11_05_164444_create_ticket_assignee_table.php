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
        Schema::create('ticket_assignee', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('ticket_id')->unique('ticket_id');
            $table->integer('assignee')->index('assignee');
            $table->dateTime('assigned_at')->useCurrentOnUpdate()->useCurrent();
            $table->integer('assigned_by')->nullable();
            $table->integer('assigned_user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_assignee');
    }
};
