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
        Schema::create('ib_commessions_report', function (Blueprint $table) {
            $table->bigInteger('indexId', true);
            $table->string('ibType', 50)->nullable();
            $table->string('ibId', 200)->nullable();
            $table->double('commession', 12, 8)->default(0);
            $table->dateTime('date')->nullable();
            $table->string('login', 100)->nullable()->default('noLogin');
            $table->bigInteger('orderID')->nullable()->default(-1);
            $table->double('lot', 10, 4)->default(0);
            $table->double('conversionRate', 10, 4)->default(1);
            $table->double('lotConversion', 10, 4)->default(0);
            $table->bigInteger('positionID')->default(-1);
            $table->string('symbol', 15)->default('noSymbol');
            $table->double('HedgeFull', 10, 4)->default(0);
            $table->double('HedgeHalf', 10, 4)->default(0);
            $table->string('openTime', 100)->default('noTime');
            $table->string('closeTime', 100)->default('noTime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ib_commessions_report');
    }
};
