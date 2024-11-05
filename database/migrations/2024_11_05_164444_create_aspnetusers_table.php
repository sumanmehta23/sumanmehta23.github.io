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
        Schema::create('aspnetusers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('uid', 50)->nullable();
            $table->string('email', 50)->default('noEmail');
            $table->boolean('email_confirmed')->default(false);
            $table->string('password', 100)->nullable();
            $table->string('country_code', 20)->nullable();
            $table->string('number', 100)->nullable();
            $table->boolean('number_confirmed')->default(false);
            $table->boolean('two_factor_enabled')->default(false);
            $table->dateTime('lockout_end_date')->nullable();
            $table->boolean('lockout_enabled')->default(false);
            $table->integer('access_count_failed')->default(0);
            $table->string('username', 150)->nullable();
            $table->string('fullname', 150)->nullable();
            $table->boolean('byPartner')->default(false);
            $table->dateTime('date')->nullable();
            $table->integer('status')->nullable()->default(0);
            $table->string('country', 50)->nullable();
            $table->string('dial_code', 20)->nullable();
            $table->boolean('Isreferal')->default(false);
            $table->string('referalId', 150)->nullable();
            $table->string('zipcode', 20)->nullable();
            $table->mediumText('address')->nullable();
            $table->mediumText('aboutme')->nullable();
            $table->string('imgName', 200)->nullable();
            $table->string('education', 150)->nullable();
            $table->string('industry', 150)->nullable();
            $table->string('financial_industry', 150)->nullable();
            $table->string('forex_exp', 20)->nullable();
            $table->string('monthly_transaction', 10)->nullable();
            $table->string('investment_plan', 10)->nullable();
            $table->string('funds_source', 15)->nullable();
            $table->string('investment_purpose', 20)->nullable();
            $table->string('total_value', 20)->nullable();
            $table->integer('annual_income')->nullable();
            $table->string('polotically_person', 5)->nullable();
            $table->string('bankruptcy', 5)->nullable();
            $table->string('usa_resident', 5)->nullable();
            $table->string('usa_tax', 5)->nullable();
            $table->string('dob', 20)->nullable();
            $table->string('emailToken', 200)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('lang', 50)->default('english');
            $table->dateTime('email_token_time')->nullable()->useCurrent();
            $table->string('profile_image', 50)->nullable();
            $table->string('gender', 50)->nullable();
            $table->string('referral', 50)->nullable();
            $table->string('mail_otp', 50)->nullable();
            $table->string('employee_status', 100)->nullable();
            $table->string('cfd', 100)->nullable();
            $table->string('other', 100)->nullable();
            $table->string('kyc_type', 100)->nullable();
            $table->string('kyc_front', 100)->nullable();
            $table->string('kyc_back', 100)->nullable();
            $table->string('bank_detail', 100)->nullable();
            $table->string('account_holder_name', 100)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_no', 100)->nullable();
            $table->string('IFSC_Code', 100)->nullable();
            $table->string('swift_code', 100)->nullable();
            $table->integer('kyc_verify')->default(0);
            $table->integer('client_status')->default(0);
            $table->string('wallet_address', 100)->nullable();
            $table->timestamp('reg_date')->useCurrentOnUpdate()->useCurrent();
            $table->integer('bank_status')->default(0);
            $table->integer('personal_status')->nullable()->default(0);
            $table->integer('employemnet_status')->nullable()->default(0);
            $table->integer('trading_status')->nullable()->default(0);
            $table->string('ib1', 150)->nullable()->default('noIB');
            $table->string('ib2')->nullable();
            $table->string('ib3')->nullable();
            $table->string('ib4')->nullable();
            $table->string('ib5')->nullable();
            $table->string('ib6')->nullable();
            $table->string('ib7')->nullable();
            $table->string('ib8')->nullable();
            $table->string('ib9')->nullable();
            $table->string('ib10')->nullable();
            $table->string('ib11')->nullable();
            $table->string('ib12')->nullable();
            $table->string('ib13')->nullable();
            $table->string('ib14')->nullable();
            $table->string('ib15')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->integer('wallet_requested')->nullable();
            $table->integer('wallet_enabled')->nullable()->default(1);
            $table->dateTime('wallet_requested_at')->nullable();
            $table->dateTime('wallet_approved_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aspnetusers');
    }
};
