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
        Schema::create('login_details', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('UserId', 150)->nullable();
            $table->dateTime('log_in')->nullable();
            $table->string('IP_address', 100)->nullable();
            $table->string('System_name', 100)->nullable();
            $table->string('browser_name', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_details');
    }
};
