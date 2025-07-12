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
        Schema::table('trade_withdrawal', function (Blueprint $table) {
            $table->float('promo_deduction')->nullable()->after('withdrawal_amount'); // replace 'some_existing_column' as needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_withdrawal', function (Blueprint $table) {
            $table->dropColumn('promo_deduction');
        });
    }
};
