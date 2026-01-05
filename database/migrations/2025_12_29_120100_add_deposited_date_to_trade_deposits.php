<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepositedDateToTradeDeposits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trade_deposits', function (Blueprint $table) {
            if (!Schema::hasColumn('trade_deposits', 'deposited_date')) {
                $table->dateTime('deposited_date')->nullable()->after('admin_remark');
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
        Schema::table('trade_deposits', function (Blueprint $table) {
            if (Schema::hasColumn('trade_deposits', 'deposited_date')) {
                $table->dropColumn('deposited_date');
            }
        });
    }
}
