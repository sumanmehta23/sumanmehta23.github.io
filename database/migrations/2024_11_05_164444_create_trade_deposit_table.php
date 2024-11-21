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
        Schema::create('trade_deposit', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email', 50)->nullable();
            $table->string('trade_id', 100)->nullable();
            $table->string('deposit_amount', 100)->nullable();
            $table->string('deposit_currency', 50)->nullable()->default('USD');
            $table->string('deposit_type', 100)->nullable();
            $table->string('deposit_from', 100)->nullable();
            $table->timestamp('deposted_date')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->integer('Status')->default(0);
            $table->string('AdminRemark', 100)->nullable();
            $table->timestamp('Js_Admin_Remark_Date')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->text('deposit_proof')->nullable();
            $table->string('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_deposit');
    }
};
