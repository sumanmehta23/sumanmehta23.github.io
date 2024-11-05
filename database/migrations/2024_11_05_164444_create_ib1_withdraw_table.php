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
        Schema::create('ib1_withdraw', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('email', 50)->nullable();
            $table->string('withdraw_amount', 100)->nullable();
            $table->string('withdraw_type', 100)->nullable();
            $table->string('client_bank', 100)->nullable();
            $table->timestamp('withdraw_date')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->integer('Status')->default(0);
            $table->string('AdminRemark', 100)->nullable();
            $table->string('Js_Admin_Remark_Date', 100)->nullable();
            $table->string('transaction_id', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ib1_withdraw');
    }
};
