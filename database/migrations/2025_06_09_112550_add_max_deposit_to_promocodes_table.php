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
        Schema::table('promocode', function (Blueprint $table) {
            $table->decimal('max_deposit', 15, 2)->nullable()->after('code'); // replace 'existing_column_name' as needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promocodes', function (Blueprint $table) {
            $table->dropColumn('max_deposit');
        });
    }
};
