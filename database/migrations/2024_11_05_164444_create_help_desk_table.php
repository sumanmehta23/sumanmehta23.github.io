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
        Schema::create('help_desk', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email', 50)->nullable();
            $table->string('subject', 100)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('priority', 100)->nullable();
            $table->string('message', 300)->nullable();
            $table->timestamp('created_date_js')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->integer('status')->default(0);
            $table->string('admin_remark', 100)->nullable();
            $table->timestamp('Js_Admin_Remark_Date')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_desk');
    }
};
