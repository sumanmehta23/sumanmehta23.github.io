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
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('payment_id');
            $table->decimal('payment_amount', 10);
            $table->string('payment_type');
            $table->text('payment_req')->nullable();
            $table->text('payment_reference_id')->nullable();
            $table->text('payment_url')->nullable();
            $table->string('payment_status')->nullable();
            $table->text('payment_res')->nullable();
            $table->string('initiated_by', 100)->nullable();
            
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
