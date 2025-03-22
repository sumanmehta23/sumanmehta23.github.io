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
        Schema::table('ib1_commission', function (Blueprint $table) {
            $table->unsignedBigInteger('expert_position_id')->nullable()->after('order_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ib1_commission', function (Blueprint $table) {
            $table->dropColumn('expert_position_id');
        });
    }
};
