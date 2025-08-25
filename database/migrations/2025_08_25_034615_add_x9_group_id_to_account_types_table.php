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
        Schema::table('account_types', function (Blueprint $table) {
            $table->integer('x9_group_id')->nullable()->after('ac_group')->comment('X9 client group ID for this account type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_types', function (Blueprint $table) {
            $table->dropColumn('x9_group_id');
        });
    }
};
