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
            // Rename kyc_front to kyc_status and change type to bigint
            if (Schema::hasColumn('aspnetusers', 'kyc_front')) {
                $table->renameColumn('kyc_front', 'kyc_status');
            }
            
            // Rename kyc_back to kyc_reason and change type to longtext
            if (Schema::hasColumn('aspnetusers', 'kyc_back')) {
                $table->renameColumn('kyc_back', 'kyc_reason');
            }
            
            // Add kyc_synced_at column
            if (!Schema::hasColumn('aspnetusers', 'kyc_synced_at')) {
                $table->dateTime('kyc_synced_at')->nullable();
            }
        });
        
        // Now change the column types using raw SQL (since renameColumn doesn't support type changes)
        DB::statement('ALTER TABLE aspnetusers MODIFY COLUMN kyc_status BIGINT NULL');
        DB::statement('ALTER TABLE aspnetusers MODIFY COLUMN kyc_reason LONGTEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aspnetusers', function (Blueprint $table) {
            // Restore kyc_status to kyc_front as varchar
            if (Schema::hasColumn('aspnetusers', 'kyc_status')) {
                $table->renameColumn('kyc_status', 'kyc_front');
            }
            
            // Restore kyc_reason to kyc_back as varchar
            if (Schema::hasColumn('aspnetusers', 'kyc_reason')) {
                $table->renameColumn('kyc_reason', 'kyc_back');
            }
            
            // Drop kyc_synced_at column
            if (Schema::hasColumn('aspnetusers', 'kyc_synced_at')) {
                $table->dropColumn('kyc_synced_at');
            }
        });
        
        // Restore original column types
        DB::statement('ALTER TABLE aspnetusers MODIFY COLUMN kyc_front VARCHAR(100) NULL');
        DB::statement('ALTER TABLE aspnetusers MODIFY COLUMN kyc_back VARCHAR(100) NULL');
    }
};
