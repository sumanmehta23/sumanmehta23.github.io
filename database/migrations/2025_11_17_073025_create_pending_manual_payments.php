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
        Schema::create('pending_manual_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('payment_log_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('account_id')->nullable();

            $table->string('email', 100)->nullable();
            $table->string('code', 100)->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('coin', 50)->nullable();
            $table->decimal('coin_amount', 20, 8)->nullable();
            $table->decimal('usd_value', 20, 2)->nullable();
            $table->decimal('initial_requested_amount', 20, 2)->nullable();

            $table->timestamp('deposit_date')->nullable();

            // Removed (based on your second migration):
            // $table->text('transaction_details');
            // $table->text('payment_response');
            // $table->timestamp('transaction_timestamp');

            $table->text('polygon_response')->nullable();
            $table->string('promocode', 50)->nullable();

            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])
                ->default('pending');

            $table->text('admin_notes')->nullable();
            $table->string('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index('transaction_id');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_manual_payments');
    }
};
