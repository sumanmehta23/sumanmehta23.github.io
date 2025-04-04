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
        Schema::table('ib_wallet', function (Blueprint $table) {
            $table->integer('order_id')->change();
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ib_wallet', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->string('order_id')->change();
        });
    }
};
