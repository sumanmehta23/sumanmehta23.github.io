<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ib1;
use App\Models\User;
use App\Models\TradeDeposit;
use Illuminate\Http\Request;
use App\Models\WalletDeposit;
use App\Services\MailService;
use App\Models\WalletWithdraw;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class Dashboard extends Controller
{
    public function index()
    {
        $rmCondition = '';
        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];
        // dd($alogin);
        if ($role != "Super Admin") {
            $rmCondition .= " left join aspnetusers user on(user.email=trs.email) WHERE ";
        } else {
            $rmCondition .= " where (1) and ";
        }
        if ($role == "Relationship Manager") {
            $rmCondition .= "  left join relationship_manager rm on(rm.user_id=trs.user_id) where rm.rm_id='" . $alogin . "' and ";
        }

        $userCondition = " ";
        if ($role != "Super Admin") {
            if ($role == "Relationship Manager") {
                $userCondition = "  left join relationship_manager rm on(rm.user_id=asp.id) where rm.rm_id='" . $alogin . "'";
            }
        }
        // dd($rmCondition);

        $sql = "select COALESCE(SUM(trs.deposit_amount), 0) as deposit from trade_deposits trs" . $rmCondition . " trs.status=1 and trs.deposit_type NOT IN('Wallet Transfer')";
        // dd($sql);

        $trade_deposit = DB::select($sql)[0];

        $sql = "select COALESCE(SUM(trs.deposit_amount), 0) as deposit from wallet_deposit trs " . $rmCondition . " trs. status=1";
        $wallet_deposit = DB::select($sql)[0];

        $sql = "select COALESCE(SUM(trs.withdrawal_amount), 0) as withdraw from trade_withdrawal trs" . $rmCondition . " trs.status=1 and trs.withdraw_type IN('Wallet Withdrawal','CRM')";
        $trade_withdrawal = DB::select($sql)[0];

        $sql = "select COALESCE(SUM(trs.withdraw_amount), 0) as withdraw from wallet_withdraw  trs" . $rmCondition . " trs.status=1 and trs.withdraw_type IN('Wallet Withdrawal')";
        $wallet_withdrawal = DB::select($sql)[0];

        $sql = "SELECT count(*) as counts from wallet_deposits trs " . $rmCondition . " trs.Status = 0";
        $pending_wd = DB::select($sql)[0];

        $sql = "SELECT count(*) as counts from trade_deposits trs " . $rmCondition . " trs.Status = 0";
        $pending_td = DB::select($sql)[0];

        $sql = "SELECT count(*) as counts from trade_withdrawal trs " . $rmCondition . " trs.Status = 0";
        $pending_tw = DB::select($sql)[0];

        $sql = "SELECT count(*) as counts from wallet_withdraw  trs " . $rmCondition . " trs.Status = 0";
        $pending_ww = DB::select($sql)[0];

        $sql = "SELECT count(*) as counts from ib1 trs " . $rmCondition . " trs.status = 0";
        $pending_ib = DB::select($sql)[0];

        if ($role == "Relationship Manager") {
            $sql = "SELECT count(*) as counts from aspnetusers trs left join aspnetusers user on(user.email=trs.email)  left join relationship_manager rm on(rm.user_id=trs.id) where rm.rm_id='" . $alogin . "' and trs.wallet_enabled = 1";
        }else{
            $sql = "SELECT count(*) as counts from aspnetusers trs " . $rmCondition . " trs.wallet_enabled = 1";
        }

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

        // $sql = "SELECT trs.* from wallet_withdraws trs " . $rmCondition . " (trs.status=0) order by trs.raw_id desc limit 10";
        $sql = "SELECT trs.*, au.id AS user_id FROM wallet_withdraws trs LEFT JOIN aspnetusers au ON trs.email = au.email " . $rmCondition . " trs.status = 0 ORDER BY trs.raw_id DESC LIMIT 10";
        $wallet_withdraws = DB::select($sql);

        return view('admin.dashboard', compact('trade_deposit', 'trade_withdrawal', 'wallet_deposit', 'wallet_withdrawal', 'pending_wd', 'pending_td', 'pending_tw', 'pending_ww', 'pending_ib', 'wallet_users', 'total_clients', 'rmCondition', 'results', 'wallet_withdraws'));
    }
    public function sendMarketingEmail(MailService $mailService)
    {
        ini_set("memory_limit", "-1");
        ini_set('max_execution_time', 0);
        return;
        // Process users in chunks to save memory
        User::select('email')
            // ->whereIn('email',['tech2@lqhmarkets.com'])
            // ->where('id','<','9dc8c7dd-3a0d-4b4f-a226-b4000fab7fe2')
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->chunk(100, function ($users) use ($mailService) {
                foreach ($users as $user) {
                    // if(in_array($user->email,['Sayedrihaad@gmail.com'])){
                        $this->sendmail($user->email, $mailService);
                    // }
                }
            });
    }
    private function sendmail($userEmail,MailService $mailService){

        $settings = settings();
        $from = $settings['email_from_address'];
        $emailSubject =  'Scheduled Server Maintenance – Important Update';
         $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content = '<p>As part of our ongoing commitment to improving your trading experience and delivering enhanced services, we will be performing scheduled server maintenance over the weekend.</p>
<p>To ensure a seamless transition, we kindly request your cooperation with the following:</p>

          <ul>
          <li><b>Close all open positions and pending orders at least 30 minutes before the market close on Friday.</b></li>
          <li>Please note that <b>10 minutes prior to market close,</b> the server will automatically close all trading operations.</li>

          </ul>
          <divThis maintenance is a crucial step in optimizing our systems to provide you with a better trading environment.</div>
          <div>Should you have any questions or concerns regarding your account, please do not hesitate to contact our support team. We are here to assist you.</div>
          <div>Thank you for your understanding and support.</div>
          <p>Best Regards.</p>
          <p>LQH Markets Team</p>
          ';
        $templateVars = [
            'name' => 'Valued Client',
            'email' => $settings['email_from_address'],
            "content" => $content,
            "title_right" => "",
            "subtitle_right" => "",
        ];

        $mailService->sendEmail($userEmail, $emailSubject, $headers, '', $templateVars);
    }
}
