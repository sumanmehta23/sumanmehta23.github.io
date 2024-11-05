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
        Schema::create('aspnetusers_log', function (Blueprint $table) {
            $table->integer('id', true)->unique('id');
            $table->string('email', 100);
            $table->string('admin_email', 100);
            $table->string('type', 50);
            $table->text('value');
            $table->dateTime('added_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aspnetusers_log');
    }
};
