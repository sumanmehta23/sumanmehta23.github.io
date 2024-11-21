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
        Schema::create('emplist', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('client_index');
            $table->uuid('role_id')->index('role_id');
            $table->string('username', 30);
            $table->string('email', 100)->unique('email');
            $table->string('gender', 10)->default('0');
            $table->date('dob')->nullable();
            $table->string('password', 20);
            $table->string('number', 25)->nullable();
            $table->string('address', 200)->nullable();
            $table->string('website', 100)->nullable();
            $table->string('uid', 100)->index('client_unique_id');
            $table->string('company_name', 100)->nullable();
            $table->binary('company_address')->nullable();
            $table->string('company_number', 50)->nullable();
            $table->string('db_prefex', 50)->nullable();
            $table->integer('status')->nullable()->default(0);
            $table->binary('profile_pic');
            $table->string('empId', 100)->index('empid');
            $table->string('userDepartment', 100);
            $table->string('userRole', 100);
            $table->string('userAccessLevel', 50)->default('subAdmin');
            $table->string('emailToken', 50);
            $table->boolean('email_confirmed')->default(false);
            $table->timestamp('email_token_time')->nullable();
            $table->string('country', 100);
            $table->string('dial_code', 15)->nullable();
            $table->integer('zipcode')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emplist');
    }
};
