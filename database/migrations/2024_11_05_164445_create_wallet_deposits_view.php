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
        DB::statement("DROP VIEW IF EXISTS `wallet_deposits`");
        DB::statement("CREATE VIEW `wallet_deposits` AS select `" . DB::getDatabaseName() . "`.`wallet_deposit`.`id` AS `raw_id`,concat('WDID',`" . DB::getDatabaseName() . "`.`wallet_deposit`.`id`) AS `id`,`" . DB::getDatabaseName() . "`.`aspnetusers`.`number` AS `number`,`" . DB::getDatabaseName() . "`.`aspnetusers`.`fullname` AS `fullname`,'email' AS `code`,`" . DB::getDatabaseName() . "`.`wallet_deposit`.`email` AS `email`,`" . DB::getDatabaseName() . "`.`wallet_deposit`.`deposit_amount` AS `deposit_amount`,`" . DB::getDatabaseName() . "`.`wallet_deposit`.`deposit_type` AS `deposit_type`,`" . DB::getDatabaseName() . "`.`wallet_deposit`.`deposted_date` AS `deposit_date`,`" . DB::getDatabaseName() . "`.`wallet_deposit`.`Status` AS `status`,'wallet' AS `TYPE` from (`" . DB::getDatabaseName() . "`.`wallet_deposit` join `" . DB::getDatabaseName() . "`.`aspnetusers` on((`" . DB::getDatabaseName() . "`.`aspnetusers`.`email` = `" . DB::getDatabaseName() . "`.`wallet_deposit`.`email`)))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `wallet_deposits`");
    }
};
