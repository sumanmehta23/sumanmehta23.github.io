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
        // DB::statement("DROP VIEW IF EXISTS `internal_transfers_list`");
        // DB::statement("CREATE VIEW `internal_transfers_list` AS select `" . DB::getDatabaseName() . "`.`trade_deposits`.`email` AS `email`,`" . DB::getDatabaseName() . "`.`trade_deposits`.`id` AS `raw_id`,'TDID' AS `source`,`" . DB::getDatabaseName() . "`.`trade_deposits`.`code` AS `it_to`,`" . DB::getDatabaseName() . "`.`trade_deposits`.`deposit_from` AS `it_from`,`" . DB::getDatabaseName() . "`.`trade_deposits`.`deposit_amount` AS `amount`,`" . DB::getDatabaseName() . "`.`trade_deposits`.`deposted_date` AS `date`,`" . DB::getDatabaseName() . "`.`trade_deposits`.`Status` AS `status`,`" . DB::getDatabaseName() . "`.`trade_deposits`.`deposit_type` AS `type` from `" . DB::getDatabaseName() . "`.`trade_deposits` where (`" . DB::getDatabaseName() . "`.`trade_deposits`.`deposit_type` in ('Internal Transfer','Wallet Transfer', 'CRM'))");


        DB::statement("DROP VIEW IF EXISTS `internal_transfers_list`");
        DB::statement("
            CREATE VIEW `internal_transfers_list` AS
            SELECT
                `" . DB::getDatabaseName() . "`.`trade_deposits`.`email` AS `email`,
                `" . DB::getDatabaseName() . "`.`trade_deposits`.`id` AS `raw_id`,
                'TDID' AS `source`,
                `" . DB::getDatabaseName() . "`.`trade_deposits`.`account_id` AS `it_to`,
                `" . DB::getDatabaseName() . "`.`trade_deposits`.`deposit_from` AS `it_from`,
                `" . DB::getDatabaseName() . "`.`trade_deposits`.`deposit_amount` AS `amount`,
                `" . DB::getDatabaseName() . "`.`trade_deposits`.`deposted_date` AS `date`,
                `" . DB::getDatabaseName() . "`.`trade_deposits`.`status` AS `status`,
                `" . DB::getDatabaseName() . "`.`trade_deposits`.`deposit_type` AS `type`
            FROM `" . DB::getDatabaseName() . "`.`trade_deposits`
            WHERE
                `" . DB::getDatabaseName() . "`.`trade_deposits`.`deposit_type` IN ('Internal Transfer', 'Wallet Transfer', 'CRM','IB Withdraw')

            UNION

            SELECT
                `" . DB::getDatabaseName() . "`.`trade_withdrawal`.`email` AS `email`,
                `" . DB::getDatabaseName() . "`.`trade_withdrawal`.`id` AS `raw_id`,
                'TWID' AS `source`,
                `" . DB::getDatabaseName() . "`.`trade_withdrawal`.`withdraw_to` AS `it_to`,
                `" . DB::getDatabaseName() . "`.`trade_withdrawal`.`account_id` AS `it_from`,
                `" . DB::getDatabaseName() . "`.`trade_withdrawal`.`withdrawal_amount` AS `amount`,
                `" . DB::getDatabaseName() . "`.`trade_withdrawal`.`withdraw_date` AS `date`,
                `" . DB::getDatabaseName() . "`.`trade_withdrawal`.`status` AS `status`,
                `" . DB::getDatabaseName() . "`.`trade_withdrawal`.`withdraw_type` AS `type`
            FROM `" . DB::getDatabaseName() . "`.`trade_withdrawal`
            WHERE
                `" . DB::getDatabaseName() . "`.`trade_withdrawal`.`withdraw_type` IN ('Internal Transfer', 'Wallet Withdrawal')

            ORDER BY `raw_id` DESC
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `internal_transfers_list`");
    }
};
