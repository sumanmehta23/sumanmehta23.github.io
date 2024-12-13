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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('client_id')->nullable();
            $table->string('ticket_no', 200)->nullable();
            $table->string('subject_name', 200)->nullable();
            $table->string('email_id', 200)->nullable();
            $table->string('ticket_services', 50)->nullable();
            $table->mediumText('discription')->nullable();
            $table->dateTime('ticket_open')->nullable();
            $table->dateTime('ticket_close')->nullable();
            $table->enum('status', ['Open', 'Closed']);
            $table->string('U_Name', 50)->nullable();
            $table->string('U_id', 150)->nullable();
            $table->integer('ticket_type_id')->index('ticket_type_id');
            $table->integer('ticket_status_id')->index('ticket_status_id');
            $table->dateTime('created_at')->useCurrent();
            $table->integer('created_by')->nullable();
            $table->integer('created_user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
