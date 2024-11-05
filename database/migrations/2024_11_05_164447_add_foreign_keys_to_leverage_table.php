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
        Schema::table('leverage', function (Blueprint $table) {
            $table->foreign(['account_type_id'], 'leverage_ibfk_1')->references(['ac_index'])->on('account_types')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leverage', function (Blueprint $table) {
            $table->dropForeign('leverage_ibfk_1');
        });
    }
};
