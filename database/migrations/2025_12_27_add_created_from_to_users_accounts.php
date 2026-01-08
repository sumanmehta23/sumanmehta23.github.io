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
        // Add created_from column to aspnetusers table
        if (Schema::hasTable('aspnetusers')) {
            Schema::table('aspnetusers', function (Blueprint $table) {
                if (!Schema::hasColumn('aspnetusers', 'created_from')) {
                    $table->string('created_from')->nullable()->default(null)->after('id')
                        ->comment('Source of user creation: zapier, manual, web, etc.');
                    $table->index('created_from');
                }
            });
        }

        // Add created_from column to accounts table
        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('accounts', 'created_from')) {
                    $table->string('created_from')->nullable()->default(null)->after('id')
                        ->comment('Source of account creation: zapier, manual, web, etc.');
                    $table->index('created_from');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('aspnetusers')) {
            Schema::table('aspnetusers', function (Blueprint $table) {
                if (Schema::hasColumn('aspnetusers', 'created_from')) {
                    $table->dropIndex(['created_from']);
                    $table->dropColumn('created_from');
                }
            });
        }

        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                if (Schema::hasColumn('accounts', 'created_from')) {
                    $table->dropIndex(['created_from']);
                    $table->dropColumn('created_from');
                }
            });
        }
    }
};
