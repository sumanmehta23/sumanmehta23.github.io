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
            // Track if this wallet entry is part of an overpayment: null, 'flagged', 'recovered'
            $table->enum('overpayment_flag', ['flagged', 'recovered'])->nullable()->after('ib_withdraw');

            // Track the primary wallet entry ID that this is overpayment for (for audit trail)
            $table->char('primary_wallet_id', 36)->nullable()->after('overpayment_flag');

            // Soft delete for overpaid entries
            $table->softDeletes()->after('updated_at');

            // Add indexes for faster lookups
            $table->index('overpayment_flag');
            $table->index(['ib1_commission_id', 'ib_withdraw', 'overpayment_flag']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ib_wallet', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['ib1_commission_id', 'ib_withdraw', 'overpayment_flag']);
            $table->dropIndex('overpayment_flag');
            $table->dropColumn('primary_wallet_id');
            $table->dropColumn('overpayment_flag');
        });
    }
};
