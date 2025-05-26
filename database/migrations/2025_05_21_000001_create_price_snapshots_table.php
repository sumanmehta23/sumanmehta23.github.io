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
        Schema::create('price_snapshots', function (Blueprint $table) {
            $table->string('Symbol', 20);
            $table->string('component1', 3)->nullable()->comment('First component / currency');
            $table->string('component2', 3)->nullable()->comment('Second component of the instrument (like base currency)');
            $table->bigInteger('Timestamp')->default(0);
            $table->decimal('Price', 20, 5)->default(0.00000);
            $table->decimal('Ask', 20, 5)->default(0.00000);
            $table->decimal('Bid', 20, 5)->default(0.00000);
            $table->double('RateToUSD')->default(1);
            $table->integer('digits')->default(5)->comment('Number of digits after the decimal place');
            $table->bigInteger('mul_factor')->default(1);
            $table->integer('contractsize')->default(100000)->comment('Typical contract size');
            $table->double('minlots')->default(0.01)->comment('Minimum lots allowed for the symbol');
            $table->double('maxlots')->default(50)->comment('Maximum lots allowed for this symbol');
            $table->double('mmr')->default(2)->comment('Minimum margin percentage required for this Symbol');
            $table->integer('leverage')->default(100)->comment('Leverage allowed for this Symbol');

            // Setting Symbol as primary key
            $table->primary('Symbol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_snapshots');
    }
};
