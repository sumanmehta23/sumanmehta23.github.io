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
        // Check if the trades table exists and commission column doesn't exist
        if (Schema::hasTable('trades') && !Schema::hasColumn('trades', 'commission')) {
            Schema::table('trades', function (Blueprint $table) {
                $table->decimal('commission', 15, 2)->nullable()->after('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('trades') && Schema::hasColumn('trades', 'commission')) {
            Schema::table('trades', function (Blueprint $table) {
                $table->dropColumn('commission');
            });
        }
    }
};
