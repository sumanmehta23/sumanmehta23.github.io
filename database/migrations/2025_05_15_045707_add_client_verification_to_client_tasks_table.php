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
        Schema::table('client_tasks', function (Blueprint $table) {
            $table->boolean('client_verification')->after('task_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_tasks', function (Blueprint $table) {
            $table->dropColumn('client_verification');
        });
    }
};
