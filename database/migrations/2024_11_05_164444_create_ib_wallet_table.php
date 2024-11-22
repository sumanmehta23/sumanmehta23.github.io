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
        Schema::create('ib_wallet', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ib_wallet', 50)->nullable();
            $table->string('ib_withdraw', 100)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('trade_id', 100)->nullable();
            $table->string('order_id', 100)->nullable();
            $table->text('remark')->nullable();
            $table->string('ib_level', 100)->nullable();
            $table->timestamp('reg_date')->useCurrentOnUpdate()->useCurrent();
            $table->foreignIdFor(\App\Models\User::class)->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignIdFor(\App\Models\Account::class)->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ib_wallet');
    }
};
