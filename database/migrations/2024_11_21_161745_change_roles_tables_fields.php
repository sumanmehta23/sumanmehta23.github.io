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
        if (Schema::hasColumn('roles', 'role_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->renameColumn('role_name','name');
            });
        }
        if (Schema::hasColumn('roles', 'role_desc')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->renameColumn('role_desc','description');
            });
        }
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        if (Schema::hasColumn('roles', 'role_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->renameColumn('name','role_name');
            });
        }
        if (Schema::hasColumn('roles', 'description')) {
            Schema::table('roles', function (Blueprint $table) {
            $table->renameColumn('description','role_desc');
            });
        }
    }
};
