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
        Schema::table('bonus_transactions', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('bonus_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bonus_transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });
    }
};
