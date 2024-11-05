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
        Schema::create('login_history', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('email', 100)->nullable();
            $table->string('ip', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('action', 100)->nullable();
            $table->timestamp('created_date_js')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->integer('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_history');
    }
};
