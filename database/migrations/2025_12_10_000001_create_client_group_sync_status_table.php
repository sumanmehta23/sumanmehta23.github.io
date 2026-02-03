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
        Schema::create('client_group_sync_status', function (Blueprint $table) {
            $table->id();
            $table->integer('client_group_id')->unique();
            $table->date('last_sync_from')->nullable();
            $table->date('last_sync_to')->nullable();
            $table->enum('sync_status', ['pending', 'syncing', 'completed', 'failed'])->default('pending');
            $table->integer('total_trades_synced')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index('client_group_id');
            $table->index('sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_group_sync_status');
    }
};
