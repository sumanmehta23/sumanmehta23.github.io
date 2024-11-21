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
        Schema::create('kyc_update', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email', 100)->nullable();
            $table->string('kyc_type', 100)->nullable();
            $table->string('kyc_frontside', 100)->nullable();
            $table->string('front_image', 100)->nullable();
            $table->string('kyc_backside', 100)->nullable();
            $table->string('back_image', 100)->nullable();
            $table->timestamp('registered_date_js')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->string('Admin_Remark', 100)->nullable();
            $table->timestamp('Admin_Remark_Date')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->integer('Status')->nullable()->default(0);
            $table->integer('added_by')->nullable()->default(0);
            $table->string('approved_by')->nullable()->default('0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_update');
    }
};
