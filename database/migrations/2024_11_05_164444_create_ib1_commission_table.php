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
        Schema::create('ib1_commission', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_id', 100)->nullable();
            $table->string('code', 100)->nullable();
            $table->string('symbol', 100)->nullable();
            $table->float('volume')->nullable();
            $table->bigInteger('init_volume')->nullable();
            $table->string('time_closed', 100)->nullable();
            $table->integer('status')->default(0);

            $table->unique(['order_id', 'code'], 'closed_order');
            $table->foreignIdFor(User::class)->constrained((new User())->getTable())->onUpdate('cascade')->onDelete('cascade');
            $table->foreignIdFor(\App\Models\Account::class)->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
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
