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
        Schema::create('trade_withdrawal', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('email', 50)->nullable();
            $table->string('trade_id', 100)->nullable();
            $table->string('withdrawal_amount', 100)->nullable();
            $table->string('withdraw_type', 100)->nullable();
            $table->string('withdraw_to', 100)->nullable();
            $table->string('wallet_qr', 250)->nullable();
            $table->timestamp('withdraw_date')->nullable()->useCurrent();
            $table->integer('Status')->default(0);
            $table->string('AdminRemark', 100)->nullable();
            $table->string('Js_Admin_Remark_Date', 100)->nullable();
            $table->string('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_withdrawal');
    }
};
