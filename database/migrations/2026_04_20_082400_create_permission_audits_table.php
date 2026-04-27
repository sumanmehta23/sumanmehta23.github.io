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
        Schema::create('permission_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Who made the change (foreign key to EmployeeList)
            $table->string('employee_id')->nullable()->index();

            // What happened
            $table->string('action')->index(); // 'create', 'update', 'delete', 'authorization_failed'
            $table->string('resource')->index(); // 'permission', 'role', 'permission_role', 'route'
            $table->string('resource_id')->nullable()->index(); // Which resource changed

            // Change data
            $table->json('old_values')->nullable(); // Previous values
            $table->json('new_values')->nullable(); // New values

            // Context
            $table->string('path')->nullable(); // Request path for authorization failures
            $table->string('method')->nullable()->default('N/A'); // HTTP method
            $table->string('ip_address')->nullable(); // Admin's IP
            $table->text('description'); // Human-readable summary

            $table->timestamps();

            // Indexes for common queries
            $table->index(['action', 'resource', 'created_at']);
            $table->index(['employee_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_audits');
    }
};
