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
        Schema::table('activity_log', function (Blueprint $table) {
            // Drop both causer_id and causer_type before recreating
            $table->dropColumn(['causer_id', 'causer_type']);

            // Recreate them as UUID-based morphs after subject_id
            $table->uuidMorphs('causer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            // Drop UUID-based causer columns
            $table->dropColumn(['causer_id', 'causer_type']);

            // Recreate them as nullableMorphs() after subject_id
            $table->nullableMorphs('causer');
        });
    }
};
