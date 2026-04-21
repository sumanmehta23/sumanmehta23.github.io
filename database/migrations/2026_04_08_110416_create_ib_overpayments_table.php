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
        Schema::create('ib_overpayments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ib_user_id')->comment('IB user who was overpaid (from ib1.user_id)');
            $table->string('referral_code', 100)->index()->comment('IB referral code');
            $table->string('order_id', 100)->comment('Deal/order that had duplicate commission');
            $table->string('account_code', 100)->comment('Trader account code');
            $table->foreignUuid('duplicate_commission_id')->nullable()->comment('The duplicate ib1_commission record');
            $table->foreignUuid('duplicate_wallet_id')->nullable()->comment('The duplicate ib_wallet record');
            $table->foreignUuid('original_commission_id')->nullable()->comment('The original/correct ib1_commission record');
            $table->foreignUuid('original_wallet_id')->nullable()->comment('The original/correct ib_wallet record');
            $table->decimal('overpaid_amount', 20, 10)->default(0)->comment('Amount overpaid');
            $table->decimal('recovered_amount', 20, 10)->default(0)->comment('Amount already recovered/deducted');
            $table->decimal('balance_at_detection', 20, 10)->default(0)->comment('IB wallet balance when detected');
            $table->string('status', 20)->default('detected')->comment('detected, acknowledged, recovering, recovered, written_off');
            $table->text('notes')->nullable();
            $table->timestamp('detected_at')->useCurrent();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('recovered_at')->nullable();
            $table->timestamps();

            $table->index(['ib_user_id', 'status']);
            $table->index(['referral_code', 'order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ib_overpayments');
    }
};
