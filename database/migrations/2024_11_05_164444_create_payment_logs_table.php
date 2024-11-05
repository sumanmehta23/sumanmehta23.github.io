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
            $table->bigInteger('payment_id', true);
            $table->decimal('payment_amount', 10);
            $table->string('payment_type');
            $table->text('payment_req')->nullable();
            $table->text('payment_reference_id')->nullable();
            $table->text('payment_url')->nullable();
            $table->string('payment_status')->nullable();
            $table->text('payment_res')->nullable();
            $table->string('initiated_by', 100)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->text('remarks')->nullable();
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
