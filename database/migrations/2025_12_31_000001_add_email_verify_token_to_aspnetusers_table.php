<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('aspnetusers', function (Blueprint $table) {
            if (!Schema::hasColumn('aspnetusers', 'email_verify_token')) {
                $table->string('email_verify_token', 120)->nullable()->after('emailToken');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('aspnetusers', function (Blueprint $table) {
            if (Schema::hasColumn('aspnetusers', 'email_verify_token')) {
                $table->dropColumn('email_verify_token');
            }
        });
    }
};
