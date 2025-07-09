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
        Schema::table('aspnetusers', function (Blueprint $table) {
            $table->renameColumn('affiliate_reference_code', 'cxd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aspnetusers', function (Blueprint $table) {
            $table->renameColumn('cxd', 'affiliate_reference_code');
        });
    }
};
