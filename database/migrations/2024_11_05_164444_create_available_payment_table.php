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
        Schema::create('available_payment', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('payment_mode', 100)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('account_holdername', 100)->nullable();
            $table->string('account_detail', 100)->nullable();
            $table->string('account_type', 100)->nullable();
            $table->string('bank_codename1', 100)->nullable();
            $table->string('bank_codename2', 100)->nullable();
            $table->string('bank_ifsc_code', 100)->nullable();
            $table->string('bank_iban_code', 100)->nullable();
            $table->string('image', 100)->nullable();
            $table->string('agent_location', 100)->nullable();
            $table->string('agent_address', 100)->nullable();
            $table->timestamp('register_date')->useCurrent();
            $table->integer('updated_by');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
            $table->text('api_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->text('additional_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('available_payment');
    }
};
