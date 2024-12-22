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
        Schema::create('ticket_followup', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('ticket_id')->index('ticket_id');
            $table->text('remarks')->nullable();
            $table->text('attachment')->nullable();
            $table->integer('status')->nullable()->index('status');
            $table->integer('assignee')->nullable()->index('assignee');
            $table->enum('user_type', ['user', 'admin'])->index('added_by');
            $table->integer('user_id')->nullable();
            $table->integer('admin_id')->nullable();
            $table->dateTime('added_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_followup');
    }
};
