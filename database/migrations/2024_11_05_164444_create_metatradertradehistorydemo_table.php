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
        Schema::create('metatradertradehistorydemo', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('tradeIndex');
            $table->string('closePrice')->nullable();
            $table->bigInteger('closeTime')->nullable();
            $table->bigInteger('openTime')->nullable();
            $table->string('cmd')->nullable();
            $table->string('symbol')->nullable();
            $table->string('comment')->nullable();
            $table->string('commission')->nullable();
            $table->string('commissionAgent')->nullable();
            $table->string('digits')->nullable();
            $table->string('expiration')->nullable();
            $table->string('gwClosePrice')->nullable();
            $table->string('gwOpenPrice')->nullable();
            $table->string('gwOrder')->nullable();
            $table->string('gwVolume')->nullable();
            $table->string('login')->nullable();
            $table->string('magic')->nullable();
            $table->string('marginRate')->nullable();
            $table->string('openPrice')->nullable();
            $table->integer('orderId')->nullable()->unique('order');
            $table->string('reason')->nullable();
            $table->string('sl')->nullable();
            $table->string('state')->nullable();
            $table->string('storage')->nullable();
            $table->string('taxes')->nullable();
            $table->string('timestamp')->nullable();
            $table->string('tp')->nullable();
            $table->string('volume')->nullable();
            $table->string('volumeReal')->nullable();
            $table->dateTime('lastUpdated')->nullable();
            $table->string('pnl')->default('0');
            $table->boolean('hedgeOrder')->default(false);
            $table->double('hedgedifference', 10, 4)->default(0);
            $table->double('hedgeFL', 10, 4)->default(0);
            $table->double('hedgeHL', 10, 4)->default(0);
            $table->double('oMarginRate', 10, 4)->default(0);
            $table->string('oTimestamp', 150)->nullable();
            $table->string('entry', 150)->nullable();
            $table->string('positionID', 150)->nullable();
            $table->string('contractSize', 100)->nullable();
            $table->string('deal', 100)->nullable();
            $table->string('dealer', 100)->nullable();
            $table->string('digitsCurrency', 20)->nullable();
            $table->string('expertID', 150)->nullable();
            $table->string('externalID', 200)->nullable();
            $table->string('gateway', 150)->nullable();
            $table->string('priceGateway', 150)->nullable();
            $table->string('pricePosition', 150)->nullable();
            $table->string('profitRaw', 100)->nullable();
            $table->string('tickSize', 150)->nullable();
            $table->string('tickValue', 150)->nullable();
            $table->string('oTimeMsc', 200)->nullable();
            $table->string('volumeClosed', 150)->nullable();
            $table->boolean('cSts')->default(false);
            $table->integer('version')->default(4);
            $table->string('cCmd', 20)->nullable();
            $table->string('cOrder', 200)->nullable();
            $table->string('cEntry', 200)->nullable();
            $table->string('cDeal', 200)->nullable();
            $table->string('cDealer', 200)->nullable();
            $table->string('cExtrnalID', 200)->nullable();
            $table->string('cGateway', 200)->nullable();
            $table->string('cPriceGateway', 200)->nullable();
            $table->string('cTickSize', 200)->nullable();
            $table->string('cTickValue', 200)->nullable();
            $table->string('timeMsc', 150)->nullable();
            $table->string('orderGroupOpen', 150)->default('noGroup');
            $table->string('orderGroupClose', 150)->default('noGroup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metatradertradehistorydemo');
    }
};
