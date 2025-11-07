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
        Schema::create('client_notes', function (Blueprint $table) {
            $table->id();
            $table->string('client_id');
            $table->uuid('admin_id');
            $table->text('note');
            $table->timestamps();

            $table->foreign('client_id')->references('Id')->on('aspnetusers')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('emplist')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_notes');
    }
};
