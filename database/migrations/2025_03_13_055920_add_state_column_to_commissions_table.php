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
            $table->tinyInteger('orderstate')->default(4)->after('status')->comment('2: Cancelled, 4: Filled 5: Rejected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ib1_commission', function (Blueprint $table) {
            $table->dropColumn('orderstate');
        });
    }
};
