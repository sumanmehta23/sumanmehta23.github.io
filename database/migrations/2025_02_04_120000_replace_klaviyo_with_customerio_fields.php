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
            // Remove Klaviyo fields
            $table->dropColumn(['klaviyo_id', 'klaviyo_last_error']);
            
            // Add Customer.io fields
            $table->string('customerio_id')->nullable()->after('wallet_approved_at');
            $table->json('customerio_last_error')->nullable()->after('customerio_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aspnetusers', function (Blueprint $table) {
            // Remove Customer.io fields
            $table->dropColumn(['customerio_id', 'customerio_last_error']);
            
            // Add back Klaviyo fields
            $table->string('klaviyo_id')->nullable()->after('wallet_approved_at');
            $table->json('klaviyo_last_error')->nullable()->after('klaviyo_id');
        });
    }
};