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
        Schema::create('client_wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('client_wallet_id');
            $table->string('wallet_name');
            $table->string('wallet_currency', 50);
            $table->string('wallet_network', 500);
            $table->text('wallet_address');
            $table->tinyInteger('status')->default(1);
            $table->foreignUuid('user_id')->references('id')->on('aspnetusers')->onUpdate('cascade')->onDelete('cascade');
            $table->string('admin_action_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_wallets');
    }
};
