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
        Schema::create('internaltransfer', function (Blueprint $table) {
            $table->bigInteger('itIndex', true);
            $table->integer('orderId')->nullable();
            $table->dateTime('date')->nullable()->useCurrent();
            $table->string('fromCurrency', 5)->default('USD');
            $table->double('amount', 10, 4)->default(0);
            $table->string('status', 25)->nullable();
            $table->string('TransferFromAccountId');
            $table->string('TransferToAccountId');
            $table->string('clientEmail');
            $table->string('clientName');
            $table->string('clientId');
            $table->string('toCurrency', 5)->default('USD');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internaltransfer');
    }
};
