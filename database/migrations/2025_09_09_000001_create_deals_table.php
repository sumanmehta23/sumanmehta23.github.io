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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->uuid('account_id'); // UUID to match accounts table
            $table->unsignedBigInteger('deal_id')->unique(); // MT5 Deal ID
            $table->unsignedBigInteger('order_id')->nullable(); // Related Order ID
            $table->string('position_id', 100)->nullable(); // Position identifier
            $table->string('symbol', 20);
            $table->tinyInteger('type'); // 0=buy, 1=sell
            $table->decimal('volume', 15, 8); // Deal volume
            $table->decimal('price', 15, 5); // Deal price
            $table->decimal('profit', 15, 2)->default(0); // Deal profit
            $table->decimal('swap', 15, 2)->default(0); // Swap
            $table->decimal('commission', 15, 2)->default(0); // Commission
            $table->string('comment')->nullable(); // Deal comment
            $table->integer('reason')->nullable(); // Deal reason
            $table->timestamp('time_done'); // Deal execution time
            $table->bigInteger('time_msc')->nullable(); // Milliseconds
            $table->timestamp('time_setup')->nullable(); // Deal setup time
            $table->unsignedBigInteger('magic')->nullable(); // EA magic number
            $table->string('external_id')->nullable(); // External identifier
            $table->decimal('rate_profit', 15, 8)->default(1); // Profit conversion rate
            $table->decimal('rate_margin', 15, 8)->default(1); // Margin conversion rate
            $table->json('raw_data')->nullable(); // Store complete MT5 deal data
            $table->timestamps();

            // Indexes for performance
            $table->index('account_id');
            $table->index('deal_id');
            $table->index('order_id');
            $table->index(['account_id', 'position_id']);
            $table->index(['account_id', 'time_done']);
            $table->index(['account_id', 'symbol']);
            $table->index('time_done');

            // Composite index for efficient lookups
            $table->index(['account_id', 'deal_id']);
            $table->index(['account_id', 'position_id', 'time_done']);

            // Foreign key constraint
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
