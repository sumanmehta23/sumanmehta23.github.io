<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TradeDeposits;
use App\Models\TradeWithdrawals;
use App\Models\WalletDeposit;
use App\Models\WalletWithdraw;
use App\Models\User;
use App\Models\Ib1;
use Illuminate\Support\Facades\DB;


class Dashboard extends Controller
{
    public function index()
    {
        $rmCondition = '';
        if (session('userData')['role_id'] != 1) {
            $rmCondition .= " left join aspnetusers user on(user.email=trs.email) ";
        } else {
            $rmCondition .= " where (1) and ";
        }
        if (session('userData')['role_id'] == 2) {
            $rmCondition .= "  left join relationship_manager rm on(rm.user_id=trs.email) where rm.rm_id='" . session('alogin') . "' and ";
        }

        $userCondition = " ";
        if (session('userData')['role_id'] != 1) {
            if (session('userData')['role_id'] == 2) {
                $userCondition = "  left join relationship_manager rm on(rm.user_id=asp.email) where rm.rm_id='" . session('alogin') . "'";
            }
        }


        $sql = "select COALESCE(SUM(trs.deposit_amount), 0) as deposit from trade_deposit trs" . $rmCondition . " trs.status=1 and trs.deposit_type NOT IN('Wallet Transfer')";
        $trade_deposit = DB::select($sql)[0];

        $sql = "select COALESCE(SUM(trs.deposit_amount), 0) as deposit from wallet_deposit trs " . $rmCondition . " trs. status=1";
        $wallet_deposit = DB::select($sql)[0];

        $sql = "select COALESCE(SUM(trs.withdrawal_amount), 0) as withdraw from trade_withdrawal trs" . $rmCondition . " trs.status=1 and trs.withdraw_type IN('Wallet Withdrawal','CRM')";
        $trade_withdrawal = DB::select($sql)[0];

        $sql = "select COALESCE(SUM(trs.withdraw_amount), 0) as withdraw from wallet_withdraw  trs" . $rmCondition . " trs.status=1 and trs.withdraw_type IN('Wallet Withdrawal')";
        $wallet_withdrawal = DB::select($sql)[0];

        $sql = "SELECT count(*) as counts from wallet_deposits trs " . $rmCondition . " trs.Status = 0";
        $pending_wd = DB::select($sql)[0];

        $sql = "SELECT count(*) as counts from trade_deposit trs " . $rmCondition . " trs.Status = 0";
        $pending_td = DB::select($sql)[0];

        $sql = "SELECT count(*) as counts from trade_withdrawal trs " . $rmCondition . " trs.Status = 0";
        $pending_tw = DB::select($sql)[0];

        $sql = "SELECT count(*) as counts from wallet_withdraw  trs " . $rmCondition . " trs.Status = 0";
        $pending_ww = DB::select($sql)[0];

        $sql = "SELECT count(*) as counts from ib1 trs " . $rmCondition . " trs.status = 0";
        $pending_ib = DB::select($sql)[0];


        $sql = "SELECT count(*) as counts from aspnetusers trs " . $rmCondition . " trs.wallet_enabled = 1";
        $wallet_users = DB::select($sql)[0];

        $sql = "SELECT
            SUM(CASE WHEN asp.status = 0 THEN 1 ELSE 0 END) AS inactive_users,
            SUM(CASE WHEN asp.status = 1 THEN 1 ELSE 0 END) AS active_users
        FROM aspnetusers asp" . $userCondition;
        $total_clients = DB::select($sql)[0];


        $eid = session('alogin');
        $sql = "SELECT trs.* from wallet_deposits trs " . $rmCondition . " (trs.status=0) order by trs.raw_id desc limit 10";
        // echo "<!-- ".$sql." --->";
        $results = DB::select($sql);

        $sql = "SELECT trs.* from wallet_withdraws trs " . $rmCondition . " (trs.status=0) order by trs.raw_id desc limit 10";
        $wallet_withdraws = DB::select($sql);

        return view('admin.dashboard', compact('trade_deposit', 'trade_withdrawal', 'wallet_deposit', 'wallet_withdrawal', 'pending_wd', 'pending_td', 'pending_tw', 'pending_ww', 'pending_ib', 'wallet_users', 'total_clients', 'rmCondition', 'results', 'wallet_withdraws'));
    }
}
