<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->foreignIdFor(User::class)->constrained((new User())->getTable())->onUpdate('cascade')->onDelete('cascade');
            $table->string('admin_action_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
