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
        Schema::create('wallet_deposit', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email', 50)->nullable();
            $table->string('deposit_amount', 100)->nullable();
            $table->string('deposit_type', 100)->nullable();
            $table->string('company_bank', 100)->nullable();
            $table->string('client_bank', 100)->nullable();
            $table->string('transaction_id', 100)->nullable()->unique('transaction_id');
            $table->timestamp('deposted_date')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->integer('Status')->default(0);
            $table->string('AdminRemark', 100)->nullable();
            $table->timestamp('Js_Admin_Remark_Date')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->string('btc_amount', 100)->nullable();
            $table->string('currency_type', 50)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
            $table->longText('callback_data')->nullable();
            $table->longText('callback_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_deposit');
    }
};
