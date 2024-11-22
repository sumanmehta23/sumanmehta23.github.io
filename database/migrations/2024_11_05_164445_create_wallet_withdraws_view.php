<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("DROP VIEW IF EXISTS `wallet_withdraws`");
        DB::statement("CREATE VIEW `wallet_withdraws` AS select `" . DB::getDatabaseName() . "`.`wallet_withdraw`.`id` AS `raw_id`,concat('WWID',`" . DB::getDatabaseName() . "`.`wallet_withdraw`.`id`) AS `id`,`" . DB::getDatabaseName() . "`.`aspnetusers`.`number` AS `number`,`" . DB::getDatabaseName() . "`.`aspnetusers`.`fullname` AS `fullname`,'email' AS `trade_id`,`" . DB::getDatabaseName() . "`.`wallet_withdraw`.`email` AS `email`,`" . DB::getDatabaseName() . "`.`wallet_withdraw`.`withdraw_amount` AS `withdraw_amount`,`" . DB::getDatabaseName() . "`.`wallet_withdraw`.`withdraw_type` AS `withdraw_type`,`" . DB::getDatabaseName() . "`.`wallet_withdraw`.`withdraw_date` AS `withdraw_date`,`" . DB::getDatabaseName() . "`.`wallet_withdraw`.`AdminRemark` AS `admin_remark`,`" . DB::getDatabaseName() . "`.`wallet_withdraw`.`Status` AS `status`,'wallet' AS `type` from (`" . DB::getDatabaseName() . "`.`wallet_withdraw` join `" . DB::getDatabaseName() . "`.`aspnetusers` on((`" . DB::getDatabaseName() . "`.`aspnetusers`.`email` = `" . DB::getDatabaseName() . "`.`wallet_withdraw`.`email`)))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `wallet_withdraws`");
    }
};
