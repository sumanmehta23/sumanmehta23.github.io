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
        if (session('userData')['userRole'] != "Super Admin") {
            $rmCondition .= " left join aspnetusers user on(user.email=trs.email) WHERE";
        } else {
            $rmCondition .= " where (1) and ";
        }
        if (session('userData')['userRole'] == "Relationship Managwer") {
            $rmCondition .= "  left join relationship_manager rm on(rm.user_id=trs.email) where rm.rm_id='" . session('alogin') . "' and ";
        }

        $userCondition = " ";
        if (session('userData')['userRole'] != "Super Admin") {
            if (session('userData')['userRole'] == "Relationship Managesr") {
                $userCondition = "  left join relationship_manager rm on(rm.user_id=asp.email) where rm.rm_id='" . session('alogin') . "'";
            }
        }


        $sql = "select COALESCE(SUM(trs.deposit_amount), 0) as deposit from trade_deposits trs" . $rmCondition . " trs.status=1 and trs.deposit_type NOT IN('Wallet Transfer')";
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

        // $sql = "SELECT trs.* from wallet_withdraws trs " . $rmCondition . " (trs.status=0) order by trs.raw_id desc limit 10";
        $sql = "SELECT trs.*, au.id AS user_id FROM wallet_withdraws trs LEFT JOIN aspnetusers au ON trs.email = au.email " . $rmCondition . " trs.status = 0 ORDER BY trs.raw_id DESC LIMIT 10";
        $wallet_withdraws = DB::select($sql);

        return view('admin.dashboard', compact('trade_deposit', 'trade_withdrawal', 'wallet_deposit', 'wallet_withdrawal', 'pending_wd', 'pending_td', 'pending_tw', 'pending_ww', 'pending_ib', 'wallet_users', 'total_clients', 'rmCondition', 'results', 'wallet_withdraws'));
    }
    public function sendMarketingEmail(MailService $mailService){
        $users = User::where('status',1)
        ->whereIn('email',['tech2@lqhmarkets.com'])->get();
        foreach($users as $user){
            $this->sendmail($user->email,$mailService);
        }
    }
    private function sendmail($userEmail,MailService $mailService){
        
        $settings = settings();
        $from = $settings['email_from_address'];
        $emailSubject = $settings['admin_title'] . ' - New Payment Options Now Available at LQH Markets! 🎉';
         $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content = '<div>Just in time for <b>New Year\'s,</b> we\'re excited to announce that <b>LQH Markets</b> has expanded our payment options to make trading more convenient for you!
</div>
          <div>We now accept:</div>
          <ul>
          <li><b>Credit Card</b> payments</li>
          <li><b>Apple Pay</b></li>
          <li><b>Crypto</b> deposits</li>
          </ul>
          <div>Ready to fund your account? Visit our secure deposit page: <a href="'.$settings['copyright_site_name_text'].'/wallet_deposit">'.$settings['copyright_site_name_text'].'/wallet_deposit</a></div>
          <div>Start trading today with these <b>flexible payment options!</b></div>
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
