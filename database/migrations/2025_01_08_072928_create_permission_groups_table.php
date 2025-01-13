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
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID for the group
            $table->string('name')->unique(); // Group name (e.g., "Permission")
            $table->timestamps();
        });
        
        Schema::table('permissions', function (Blueprint $table) {
            $table->uuid('permission_group_id')->nullable(); // Foreign key to permission_groups
            $table->foreign('permission_group_id')->after('description')->references('id')->on('permission_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_groups');
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['permission_group_id']);
            $table->dropColumn('permission_group_id');
        });
    }
};
