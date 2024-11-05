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
            $table->integer('client_wallet_id', true);
            $table->string('wallet_name');
            $table->string('wallet_currency', 50);
            $table->string('wallet_network', 500);
            $table->text('wallet_address');
            $table->string('created_by')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('user_id')->nullable();
            $table->string('admin_action_by')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();
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
