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
        Schema::table('wallet_withdraw', function (Blueprint $table) {
            $table->string('approved_by')->nullable()->after('withdraw_date'); // Replace 'existing_column_name' with the column after which you want to add this
            $table->dateTime('approved_date')->nullable()->after('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_withdraw', function (Blueprint $table) {
            $table->dropColumn(['approved_by', 'approved_date']);
        });
    }
};
