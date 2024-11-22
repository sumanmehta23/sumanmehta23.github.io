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
        Schema::create('ib1_commission', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_id', 100)->nullable();
            $table->string('code', 100)->nullable();
            $table->string('volume', 100)->nullable();
            $table->string('time_closed', 100)->nullable();
            $table->integer('status')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->unique(['order_id', 'code'], 'closed_order');
            $table->foreignIdFor(\App\Models\User::class)->constrained()->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ib1_commission');
    }
};
