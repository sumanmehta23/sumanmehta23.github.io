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
use App\Models\RelationshipManager;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class Dashboard extends Controller
{
    public function index()
    {
        $role = session('userData')['userRole'];
        $adminId = session('userData')['id'];

        // Build base queries with relationship manager filtering
        $tradeDepositQuery = $this->buildBaseQuery(TradeDeposit::query(), $role, $adminId);
        $walletDepositQuery = $this->buildBaseQuery(WalletDeposit::query(), $role, $adminId);
        $tradeWithdrawalQuery = $this->buildBaseQuery(TradeWithdrawals::query(), $role, $adminId);
        $walletWithdrawQuery = $this->buildBaseQuery(WalletWithdraw::query(), $role, $adminId);
        $ibQuery = $this->buildBaseQuery(Ib1::query(), $role, $adminId);

        // Calculate trade deposits (excluding specific types)
        $trade_deposit = (object)[
            'deposit' => (clone $tradeDepositQuery)
                ->where('status', 1)
                ->whereNotIn('deposit_type', ['Wallet Transfer', 'CryptoChill', 'CreditCardPayissa'])
                ->sum('deposit_amount') ?? 0
        ];

        // Calculate new trade deposits (specific types only)
        $new_trade_deposit = (object)[
            'deposit' => (clone $tradeDepositQuery)
                ->where('status', 1)
                ->whereIn('deposit_type', ['CryptoChill', 'CreditCardPayissa'])
                ->sum('deposit_amount') ?? 0
        ];

        // Calculate wallet deposits
        $wallet_deposit = (object)[
            'deposit' => (clone $walletDepositQuery)
                ->where('status', 1)
                ->sum('deposit_amount') ?? 0
        ];

        // Calculate trade withdrawals (Wallet Withdrawal and CRM)
        $trade_withdrawal = (object)[
            'withdraw' => (clone $tradeWithdrawalQuery)
                ->where('status', 1)
                ->whereIn('withdraw_type', ['Wallet Withdrawal', 'CRM'])
                ->sum('withdrawal_amount') ?? 0
        ];

        // Calculate new trade withdrawals (Trade Withdrawal only)
        $new_trade_withdrawal = (object)[
            'withdraw' => (clone $tradeWithdrawalQuery)
                ->where('status', 1)
                ->where('withdraw_type', 'Trade Withdrawal')
                ->sum('withdrawal_amount') ?? 0
        ];

        // Calculate wallet withdrawals
        $wallet_withdrawal = (object)[
            'withdraw' => (clone $walletWithdrawQuery)
                ->where('status', 1)
                ->where('withdraw_type', 'Wallet Withdrawal')
                ->sum('withdraw_amount') ?? 0
        ];

        // Calculate pending counts
        $pending_wd = (object)['counts' => (clone $walletDepositQuery)->where('status', 0)->count()];
        $pending_td = (object)['counts' => (clone $tradeDepositQuery)->where('status', 0)->count()];
        $pending_tw = (object)['counts' => (clone $tradeWithdrawalQuery)->where('status', 0)->count()];
        $pending_ww = (object)['counts' => (clone $walletWithdrawQuery)->where('status', 0)->where('verified', 0)->count()];
        $pending_ib = (object)['counts' => (clone $ibQuery)->where('status', 0)->count()];

        // Calculate wallet users
        $walletUsersQuery = User::where('wallet_enabled', 1);
        if ($role == "Relationship Manager") {
            $walletUsersQuery->whereHas('relationshipManager', function ($query) use ($adminId) {
                $query->where('rm_id', $adminId);
            });
        }
        $wallet_users = (object)['counts' => $walletUsersQuery->count()];

        // Calculate total clients (active/inactive)
        $clientsQuery = User::select(
            DB::raw('SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as inactive_users'),
            DB::raw('SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_users')
        );

        if ($role == "Relationship Manager") {
            $clientsQuery->whereHas('relationshipManager', function ($query) use ($adminId) {
                $query->where('rm_id', $adminId);
            });
        }
        $total_clients = $clientsQuery->first();

        // Get pending wallet deposits
        $results = (clone $walletDepositQuery)
            ->where('status', 0)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // Get pending wallet withdrawals with user info
        $wallet_withdraws = (clone $walletWithdrawQuery)
            ->with('user:id,email,fullname')
            ->where('status', 0)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($withdrawal) {
                $withdrawal->user_id = $withdrawal->user?->id;
                return $withdrawal;
            });

        // Get pending trade withdrawals with user info
        $trade_withdrawals = (clone $tradeWithdrawalQuery)
            ->with('user:id,email,fullname')
            ->where('status', 0)
            ->where('email_verified', 1)
            ->where('withdraw_type', 'Trade Withdrawal')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($withdrawal) {
                $withdrawal->user_id = $withdrawal->user?->id;
                return $withdrawal;
            });

        // Keep rmCondition for legacy view compatibility (if needed)
        $rmCondition = '';

        return view('admin.dashboard', compact(
            'trade_deposit',
            'trade_withdrawal',
            'wallet_deposit',
            'wallet_withdrawal',
            'pending_wd',
            'pending_td',
            'pending_tw',
            'pending_ww',
            'pending_ib',
            'wallet_users',
            'total_clients',
            'rmCondition',
            'results',
            'wallet_withdraws',
            'new_trade_deposit',
            'new_trade_withdrawal',
            'trade_withdrawals'
        ));
    }

    /**
     * Build base query with relationship manager filtering
     */
    private function buildBaseQuery($query, $role, $adminId)
    {
        if ($role == "Relationship Manager") {
            return $query->whereHas('user.relationshipManager', function ($q) use ($adminId) {
                $q->where('rm_id', $adminId);
            });
        }

        return $query;
    }
    public function sendMarketingEmail(MailService $mailService)
    {

        ini_set("memory_limit", "-1");
        ini_set('max_execution_time', 0);
        // return;

        // Process users in chunks to save memory
        User::select('email')
            ->whereIn('email', [
                'abhay@lqhmarkets.com',
                'Jalelwabou@gmail.com',
                'tech2@lqhmarkets.com',
                'lqhmarkets@gmail.com'
            ])
            // ->where('id','<','9dc8c7dd-3a0d-4b4f-a226-b4000fab7fe2')
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->chunk(100, function ($users) use ($mailService) {
                foreach ($users as $user) {
                    // if(in_array($user->email,['abhay@lqhmarkets.com'])){
                    $this->sendmail($user->email, $mailService);
                    // }
                }
            });
    }
    private function sendmail($userEmail, MailService $mailService)
    {
        $settings = settings();
        $from = $settings['email_from_address'];
        $emailSubject =  'Exciting News: PAMM Accounts Are Now Live!';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content = '<p>LQH Markets is thrilled to announce the launch of PAMM accounts!</p>

          <ul>
          <li><b>Traders</b> can now become managers and allow others to invest in their trading strategies. Start here:


                <a href="https://my.lqhmarkets.com/pamm/manager" target="_blank">PAMM Manager</a>

          </li>
          <li><b>Investors</b> can allocate funds to their favorite traders or choose from the leaderboard of top-performing managers:

                <a href="https://my.lqhmarkets.com/pamm/investor" target="_blank">PAMM Investor</a>

          </li>

          </ul>

          <p>To get started, you <b>must create a new MT5 live account named "PAMM"</b>. This is how you can become a manager or investor.  </p>

          <p>Don’t miss this exciting opportunity!</p>
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
