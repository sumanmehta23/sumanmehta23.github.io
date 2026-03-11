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
        Schema::create('learn_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learn_section_id')->constrained('learn_sections')->cascadeOnDelete();
            $table->string('title');
            $table->string('wistia_id');
            $table->json('tags')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learn_videos');
    }
};

