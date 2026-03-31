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
        Schema::table('kyc_logs', function (Blueprint $table) {
            // Add applicant_id after client_id
            if (!Schema::hasColumn('kyc_logs', 'applicant_id')) {
                $table->string('applicant_id', 255)->nullable()->after('client_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kyc_logs', function (Blueprint $table) {
            if (Schema::hasColumn('kyc_logs', 'applicant_id')) {
                $table->dropColumn('applicant_id');
            }
        });
    }
};
