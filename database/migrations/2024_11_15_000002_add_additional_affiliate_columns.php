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
        Schema::table('affiliates', function (Blueprint $table) {
            // Add all columns from the Excel screenshot
            $table->string('custom_id')->nullable()->after('affiliate_code');
            $table->string('single_campaign_mode')->nullable()->after('email');
            $table->boolean('email_verified')->default(false)->after('single_campaign_mode');
            $table->decimal('available_balance', 10, 2)->default(0.00)->after('email_verified');
            $table->text('promotional_materials')->nullable()->after('available_balance');
            $table->text('terms_and_conditions')->nullable()->after('promotional_materials');
            $table->text('privacy_policy')->nullable()->after('terms_and_conditions');
            $table->boolean('blocked')->default(false)->after('privacy_policy');
            $table->boolean('2fa_active')->default(false)->after('blocked');
            $table->boolean('deleted')->default(false)->after('2fa_active');
            $table->string('manager')->nullable()->after('deleted');
            $table->string('referrer')->nullable()->after('manager');
            $table->string('payout_groups')->nullable()->after('referrer');
            $table->string('payouts')->nullable()->after('payout_groups');
            $table->string('affiliate_group')->nullable()->after('payouts');
            $table->timestamp('creation_date')->nullable()->after('affiliate_group');
            $table->timestamp('last_login')->nullable()->after('creation_date');
            $table->text('additional_info')->nullable()->after('website');
            
            // Remove the unique constraint from email
            $table->dropUnique(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropColumn([
                'custom_id',
                'single_campaign_mode',
                'email_verified',
                'available_balance',
                'promotional_materials',
                'terms_and_conditions',
                'privacy_policy',
                'blocked',
                '2fa_active',
                'deleted',
                'manager',
                'referrer',
                'payout_groups',
                'payouts',
                'affiliate_group',
                'creation_date',
                'last_login',
                'additional_info',
            ]);
            
            $table->unique('email');
        });
    }
};
