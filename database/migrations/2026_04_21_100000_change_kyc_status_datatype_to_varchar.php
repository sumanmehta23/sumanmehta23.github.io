<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('aspnetusers', function (Blueprint $table) {
            // Change kyc_status column datatype to varchar(100)
            DB::statement('ALTER TABLE aspnetusers MODIFY COLUMN kyc_status VARCHAR(100) NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aspnetusers', function (Blueprint $table) {
            // Restore kyc_status to BIGINT
            DB::statement('ALTER TABLE aspnetusers MODIFY COLUMN kyc_status BIGINT NULL');
        });
    }
};
