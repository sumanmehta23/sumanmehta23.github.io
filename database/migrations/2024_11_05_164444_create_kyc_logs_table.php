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
        Schema::create('kyc_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('kyc_log_id');
            $table->string('client_id');
            $table->text('callback_code');
            $table->longText('callback_payload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_logs');
    }
};
