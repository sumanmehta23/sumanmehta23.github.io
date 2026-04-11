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
        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('accounts', 'not_found_in_mt5')) {
                    $table->boolean('not_found_in_mt5')->default(false)->after('deletion_type')
                        ->comment('Mark accounts not found in MT5 (deleted or invalid)');
                    $table->index('not_found_in_mt5');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                if (Schema::hasColumn('accounts', 'not_found_in_mt5')) {
                    $table->dropIndex(['not_found_in_mt5']);
                    $table->dropColumn('not_found_in_mt5');
                }
            });
        }
    }
};
