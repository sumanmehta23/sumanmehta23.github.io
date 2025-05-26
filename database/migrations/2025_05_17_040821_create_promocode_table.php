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
        Schema::create('promocode', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Auto-increment primary key
            $table->string('code',255); // Promocode itself
            $table->decimal('promo_percentage', 5, 2); // Bonus percentage with precision
            $table->boolean('status'); // Status (active/inactive)
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocode');
    }
};
