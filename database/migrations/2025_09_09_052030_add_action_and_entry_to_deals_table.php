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
        Schema::table('deals', function (Blueprint $table) {
            $table->unsignedTinyInteger('action')->nullable()->after('type')->comment('Deal operation type (DEAL_TYPE_*)');
            $table->unsignedTinyInteger('entry')->nullable()->after('action')->comment('Deal direction: 0=in, 1=out, 2=inout');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn(['action', 'entry']);
        });
    }
};
