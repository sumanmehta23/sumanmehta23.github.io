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
        DB::statement("DROP VIEW IF EXISTS `internal_transfers_list`");
        DB::statement("CREATE VIEW `internal_transfers_list` AS select `" . DB::getDatabaseName() . "`.`trade_deposits`.`email` AS `email`,`" . DB::getDatabaseName() . "`.`trade_deposits`.`id` AS `raw_id`,'TDID' AS `source`,`" . DB::getDatabaseName() . "`.`trade_deposits`.`trade_id` AS `it_to`,`" . DB::getDatabaseName() . "`.`trade_deposits`.`deposit_from` AS `it_from`,`" . DB::getDatabaseName() . "`.`trade_deposits`.`deposit_amount` AS `amount`,`" . DB::getDatabaseName() . "`.`trade_deposits`.`deposted_date` AS `date`,`" . DB::getDatabaseName() . "`.`trade_deposits`.`Status` AS `status`,`" . DB::getDatabaseName() . "`.`trade_deposits`.`deposit_type` AS `type` from `" . DB::getDatabaseName() . "`.`trade_deposits` where (`" . DB::getDatabaseName() . "`.`trade_deposits`.`deposit_type` in ('Internal Transfer','Wallet Transfer', 'CRM'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `internal_transfers_list`");
    }
};
