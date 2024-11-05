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
        Schema::create('bonuses', function (Blueprint $table) {
            $table->integer('bonus_id', true);
            $table->string('bonus_name');
            $table->string('bonus_code');
            $table->text('bonus_desc');
            $table->dateTime('bonus_starts_at');
            $table->dateTime('bonus_ends_at');
            $table->enum('bonus_accessable', ['First Deposit', 'Welcome Bonus'])->default('First Deposit');
            $table->enum('bonus_shows_on', ['all', 'groups', 'users'])->default('all');
            $table->longText('bonus_show_list')->nullable();
            $table->enum('bonus_type', ['percentage', 'flat'])->default('percentage');
            $table->decimal('bonus_value', 10);
            $table->integer('bonus_limit')->default(1);
            $table->boolean('status')->default(true);
            $table->string('bonus_updated_by', 300);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonuses');
    }
};
