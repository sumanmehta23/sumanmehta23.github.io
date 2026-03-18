<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forex_news_items', function (Blueprint $table) {
            $table->id();
            $table->string('guid_hash', 64)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('link');
            $table->timestamp('published_at')->nullable()->index();
            $table->string('date_label')->nullable()->index();
            $table->string('time_label')->nullable();
            $table->string('currency', 8)->nullable()->index();
            $table->string('impact', 16)->default('low')->index();
            $table->string('forecast')->nullable();
            $table->string('previous')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forex_news_items');
    }
};

