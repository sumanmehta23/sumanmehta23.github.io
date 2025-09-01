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
        Schema::table('accounts', function (Blueprint $table) {
            // Add sync status tracking columns if they don't exist
            if (!Schema::hasColumn('accounts', 'last_sync_attempt_at')) {
                $table->timestamp('last_sync_attempt_at')->nullable();
            }

            if (!Schema::hasColumn('accounts', 'sync_status')) {
                $table->string('sync_status')->default('pending')->index();
            }

            if (!Schema::hasColumn('accounts', 'sync_error')) {
                $table->text('sync_error')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['last_sync_attempt_at', 'sync_status', 'sync_error']);
        });
    }
};
