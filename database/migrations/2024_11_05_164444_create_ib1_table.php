<?php

use App\Models\User;
use App\Models\IbCategory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ib1', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('indexId');
            $table->foreignIdFor(IbCategory::class)->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->integer('acc_type')->nullable();
            $table->string('uid', 150)->nullable()->unique('uniqueid');
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique('email');
            $table->string('number', 50)->nullable();
            $table->string('username', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('password')->nullable();
            $table->integer('status')->default(0);
            $table->string('website', 100)->nullable();
            $table->string('company_name')->nullable();
            $table->date('dob')->nullable();
            $table->string('profile_pic')->nullable();
            $table->string('accountCurrencyBase', 5)->default('USD');
            $table->string('address')->nullable();
            $table->boolean('email_confirmed')->default(false);
            $table->string('emailToken', 200)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('zipcode', 50)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('ib_ref_code', 50)->default('noCode');
            $table->string('kyc_type', 100)->nullable();
            $table->string('kyc_frontside', 100)->nullable();
            $table->string('front_image', 100)->nullable();
            $table->string('kyc_backside', 100)->nullable();
            $table->string('back_image', 100)->nullable();
            $table->string('registered_date_js', 100)->nullable();
            $table->string('Admin_Remark', 100)->nullable();
            $table->string('Admin_Remark_Date', 100)->nullable();
            $table->integer('kyc_status')->nullable()->default(0);
            $table->string('bank_detail', 100)->nullable();
            $table->string('account_holder_name', 100)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_no', 100)->nullable();
            $table->string('bank_branch', 100)->nullable();
            $table->string('IFSC_Code', 100)->nullable();
            $table->string('bank_status', 100)->nullable();
            $table->timestamp('reg_date')->useCurrentOnUpdate()->useCurrent();
            $table->string('ib1', 100)->nullable();
            $table->string('ib2', 100)->nullable();
            $table->string('ib3', 100)->nullable();
            $table->string('ib4', 100)->nullable();
            $table->string('ib5', 100)->nullable();
            $table->string('ib6', 100)->nullable();
            $table->string('ib7', 100)->nullable();
            $table->string('ib8', 100)->nullable();
            $table->string('ib9', 100)->nullable();
            $table->string('ib10', 100)->nullable();
            $table->string('ib11')->nullable();
            $table->string('ib12')->nullable();
            $table->string('ib13')->nullable();
            $table->string('ib14')->nullable();
            $table->string('ib15')->nullable();
            $table->string('ib_wallet', 100)->nullable();
            $table->foreignIdFor(User::class)->constrained((new User())->getTable())->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ib1');
    }
};
