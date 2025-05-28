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
        Schema::create('trades', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID as primary key

            $table->uuid('account_id'); // Use UUID if it's a relation to accounts
            $table->string('code', 50); // Trade or internal reference code (adjust length as needed)
            $table->string('order_id', 100); // Order identifier from broker/platform

            $table->string('symbol', 20); // e.g., "EURUSD"
            $table->unsignedBigInteger('position_id'); // Foreign key or internal trade reference

            $table->enum('type', ['buy', 'sell']); // Trade direction
            $table->decimal('volume', 10, 2); // Standard volume (lot size)
            $table->decimal('volume_ext', 20, 4)->default(0); // Extended/precise volume if needed

            $table->decimal('open_price', 15, 6); // Entry price
            $table->decimal('close_price', 15, 6)->nullable(); // Entry price
            $table->decimal('profit', 15, 2)->default(0);
            $table->decimal('sl', 15, 6)->nullable(); // Stop loss
            $table->decimal('tp', 15, 6)->nullable(); // Take profit

            $table->text('comment')->nullable(); // Optional comments

            $table->string('status', 50); // E.g., "open", "closed"
            $table->string('state', 50)->nullable(); // Optional field like "pending", "executed"

            $table->timestamp('open_time'); // Trade open time
            $table->timestamp('close_time')->nullable(); // Trade close time

            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
