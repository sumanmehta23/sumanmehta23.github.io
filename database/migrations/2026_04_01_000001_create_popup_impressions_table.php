<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('popup_impressions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('popup_key', 120);
            $table->timestamp('shown_at');
            $table->timestamps();

            $table->index('user_id');
            $table->index('popup_key');
            $table->unique(['user_id', 'popup_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_impressions');
    }
};
