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
            $table->string('transaction_id')->nullable()->after('id');
            $table->text('callback_data')->nullable()->after('transaction_id');
            $table->text('callback_code')->nullable()->after('callback_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_deposits', function (Blueprint $table) {
            $table->dropColumn(['transaction_id', 'callback_data', 'callback_code']);
        });
    }
};
