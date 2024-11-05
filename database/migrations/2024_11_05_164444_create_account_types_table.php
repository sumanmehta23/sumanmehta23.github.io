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
        Schema::create('account_types', function (Blueprint $table) {
            $table->integer('ac_index', true);
            $table->integer('ac_category')->nullable();
            $table->integer('ac_book_type')->nullable();
            $table->string('ac_name', 50)->nullable();
            $table->integer('ac_min_deposit')->nullable();
            $table->integer('ac_max_deposit')->nullable();
            $table->string('ac_max_leverage')->nullable();
            $table->double('ac_lot_size', 4, 2)->nullable();
            $table->string('ac_group', 150)->nullable();
            $table->double('ac_spread', 10, 1)->nullable();
            $table->integer('ac_type')->nullable()->index('ac_type');
            $table->integer('acc_ib_cat')->nullable();
            $table->boolean('ib_enabled')->default(true);
            $table->enum('ac_swap', ['yes', 'no'])->default('yes');
            $table->boolean('is_client_group')->default(true);
            $table->boolean('inquiry_status')->default(false);
            $table->boolean('status')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->integer('display_priority')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};
