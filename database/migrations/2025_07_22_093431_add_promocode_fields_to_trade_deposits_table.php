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
        Schema::table('trade_deposits', function (Blueprint $table) {
            $table->decimal('promocode_percentage', 5, 2)->nullable()->after('code');
            $table->string('promocode_code')->nullable()->after('promocode_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_deposits', function (Blueprint $table) {
            $table->dropColumn(['promocode_percentage', 'promocode_code']);
        });
    }
};
