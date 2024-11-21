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
        Schema::create('userdocuments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('uid', 150)->nullable();
            $table->string('doc_name', 150)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('doc_type', 100)->nullable();
            $table->string('status', 50)->default('pending');
            $table->dateTime('date')->nullable();
            $table->string('verified_by', 150)->nullable();
            $table->dateTime('verified_on')->nullable();
            $table->string('uploaded_by', 50)->default('client');
            $table->mediumText('note')->nullable();
            $table->string('uploader_id', 150)->nullable();
            $table->string('email', 50)->default('noEmail');
            $table->string('client_type', 50)->default('client');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('userdocuments');
    }
};
