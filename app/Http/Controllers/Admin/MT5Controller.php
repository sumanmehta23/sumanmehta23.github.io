<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AccountHelper;
use App\Http\Controllers\Controller;
use App\Models\BonusTrans;
use App\Models\TradeDeposits;
use App\Models\TradeWithdrawals;
use App\MT5\MTEnDealAction;
use App\MT5\MTProtocolConsts;
use App\MT5\MTRetCode;
use Illuminate\Http\Request;
use DB;
use Mail;
use App\MT5\MTWebAPI;
use App\Services\MT5Service;
use App\Services\MailService as MailService;
use App\Models\User;


class MT5Controller extends Controller
{
    protected $api;
    protected $mailService;
    protected $mt5Service;
    public function __construct(MailService $mailService, MT5Service $mt5Service, MTWebAPI $api)
    {
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
        $this->mailService = $mailService;
        // $this->api = $api;

    }
    public function index(Request $request)
    {
        // Get the activeType and activeGroup from the request
        $activeType = $request->query('activeType');
        $activeGroup = $request->query('activeGroup');

        // Retrieve MT5 group categories of type 'type' with account type counts
        $results = DB::table('mt5_group_categories')
            ->leftJoin('account_types', 'account_types.ac_category', '=', 'mt5_group_categories.mt5_grp_cat_id')
            ->select('mt5_group_categories.*', DB::raw('SUM(IF(account_types.ac_index IS NOT NULL, 1, 0)) as count'))
            ->where('mt5_group_categories.mt5_grp_cat_type', 'type')
            ->groupBy('mt5_group_categories.mt5_grp_cat_id')
            ->orderBy('mt5_group_categories.mt5_grp_cat_id')
            ->get();

        // Retrieve MT5 group categories of type 'book'
        $grp_books = DB::table('mt5_group_categories')
            ->where('mt5_grp_cat_type', 'book')
            ->orderBy('mt5_grp_cat_id')
            ->get();

        // Retrieve all MT5 groups
        $mt5_groups = DB::table('mt5_groups')->get();

        // Retrieve account types with display priority
        $acc_priority = DB::table('account_types')
            ->whereNotNull('display_priority')
            ->get();

        // Return data to the view
        return view('admin.mt5.index', [
            'results' => $results,
            'grp_books' => $grp_books,
            'mt5_groups' => $mt5_groups,
            'acc_priority' => $acc_priority,
            'activeType' => $activeType,
            'activeGroup' => $activeGroup,
        ]);
    }
    public function updateAccountDetails(Request $request)
    {
        if ($request->has(['trade_id', 'account_type'])) {
            $trade_id = $request->input('trade_id');
            $account_type = $request->input('account_type');
            $leverage = $request->input('leverage');

            // Fetch user data from API (assume the API method and classes are available)
            // $trade_user = NULL;/
            // $this->api->UserGet($trade_id,$trade_user);
            // dd($trade_id);
            if (($error_code = $this->api->UserGet($trade_id, $trade_user)) != MTRetCode::MT_RET_OK) {
                //dd(MTRetCode::GetError($error_code));
                // return response()->json([
                //     'status' => 'warning',
                //     'message' => 'Something went wrong on Updating details',
                //     'error' => MTRetCode::GetError($error_code)
                // ], 400);
                return redirect()->back()->with('error', 'Something went wrong on Updating details' . MTRetCode::GetError($error_code));
            }
            // Fetch account type details
            $acc = DB::table('account_types')
                ->where('ac_index', $account_type)
                ->first();

            $trade_user->Group = $acc->ac_group;
            $trade_user->Leverage = $leverage;

            // Update user data via API
            $updated_user = "";
            if (($error_code = $this->api->UserUpdate($trade_user, $updated_user)) != MTRetCode::MT_RET_OK) {
                return redirect()->back()->with("error", "Something went wrong on Updating details" . MTRetCode::GetError($error_code));
            } else {
                // Update leverage and account type in the database
                DB::table('liveaccount')
                    ->where('trade_id', $trade_id)
                    ->update([
                        'leverage' => $leverage,
                        'account_type' => $account_type
                    ]);
                return redirect()->back()->with("success", "MT5 Account Details Successfully Updated");
            }
        }
    }

    public function updatePassword(Request $request)
    {
        if ($request->has(['trade_id', 'password_type'])) {
            $login = $request->input('trade_id');
            $pass_type = $request->input('password_type');
            $new_password = $request->input('password');
            $type = $request->input('type', 'live'); // default to 'live' if 'type' is not provided
            // Change main password
            if ($pass_type == 'main') {
                if (($error_code = $this->api->UserPasswordChange($login, $new_password, MTProtocolConsts::WEB_VAL_USER_PASS_MAIN)) != MTRetCode::MT_RET_OK) {
                    return redirect()->back()->with("error", 'Something went wrong on fetching details' . MTRetCode::GetError($error_code));
                } else {
                    $table = $type == 'demo' ? 'demoaccount' : 'liveaccount';
                    DB::table($table)
                        ->where('trade_id', $login)
                        ->update(['trader_password' => $new_password]);
                    return redirect()->back()->with("success", 'Your Master Password Successfully Updated');
                }
            }

            // Change investor password
            if ($pass_type == 'investor') {
                if (($error_code = $this->api->UserPasswordChange($login, $new_password, MTProtocolConsts::WEB_VAL_USER_PASS_INVESTOR)) != MTRetCode::MT_RET_OK) {
                    return redirect()->back()->with("error", 'Something went wrong on fetching details' . MTRetCode::GetError($error_code));
                } else {
                    $table = $type == 'demo' ? 'demoaccount' : 'liveaccount';
                    DB::table($table)
                        ->where('trade_id', $login)
                        ->update(['invester_password' => $new_password]);
                    return redirect()->back()->with('success', 'Your Investor Password Successfully Updated');
                }
            }
        }
    }

    public function depositToAccount(Request $request)
    {
        $eid = $request->input('email');
        $user = User::where('email', $eid)->first();
        $trade_id = $request->input('trade_id');
        if ($request->has('deposit_to_account')) {
            $amount = str_replace(',', '', $request->input('amount'));
            $description = $request->input('description');
            $deposit_type = 'CRM';
            $email = $eid;
            $deposit_currency = 'USD';
            $login = $trade_id;
            $comment = 'CRM Deposited';
            $ticket = null;

            if (($error_code = $this->api->TradeBalance($login, MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
                return redirect()->back()->with('error', MTRetCode::GetError($error_code));
            } else {
                $tradeDeposit = TradeDeposits::create([
                    'email' => $email,
                    'trade_id' => $trade_id,
                    'deposit_amount' => $amount,
                    'deposit_type' => $deposit_type,
                    'status' => 1,
                    'admin_remark' => $description,
                    'deposit_currency' => $deposit_currency,
                    'created_by' => session('alogin')
                ]);
                $transid = "TDID" . str_pad($tradeDeposit->id, 4, '0', STR_PAD_LEFT);

                // Store in total_balance table
                DB::table('total_balance')->insert([
                    'email' => $email,
                    'trading_deposited' => $amount
                ]);
                $settings = settings();
                $emailSubject = $settings['admin_title'] . ' - Fund Deposit';
                $content = '<div>We are pleased to inform you that funds have been successfully deposited into your account.</div>
          <div><b>Transaction Details</b></div>
          <div><b>Amount: </b>$' . $amount . '</div>
          <div><b>Account ID: </b>' . $trade_id . '</div>
          <div><b>Transaction ID: </b>' . $transid . '</div>
          <div><b>Deposited Date: </b>' . date("Y-m-d H:i:s") . '</div>
          <div><b>Deposit Type </b>' . $deposit_type . '</div>';
                $templateVars = [
                    'name' => $user->fullname,
                    'site_link' => settings()['copyright_site_name_text'],
                    "btn_text" => "Go To Dashboard",
                    'email' => settings()['email_from_address'],
                    "content" => $content,
                    "title_right" => "Fund",
                    "subtitle_right" => "Deposit"
                ];
                $this->mailService->sendEmail($email, $emailSubject, '', '', $templateVars);
                return redirect()->back()->with('success', 'Trade Deposit Successful');
            }
        }
    }

    public function bonusToAccount(Request $request)
    {
        $eid = $request->input('email');
        $user = User::where('email', $eid)->first();
        $trade_id = $request->input('trade_id');
        if ($request->has('bonus_to_account')) {

            $amount = $request->input('amount');
            $description = $request->input('description');
            $type = $request->input('type');
            $deposit_type = $type === 'in' ? 'Bonus In' : 'Bonus Out';
            $amount = $type === 'in' ? $amount : -1 * $amount;
            $email = $eid;
            $deposit_currency = 'USD';
            $login = $trade_id;
            // $comment = $description;
            $comment = $type === 'in' ? 'Bonus Deposit' : 'Bonus Withdraw';;
            $ticket = null;

            if (($error_code = $this->api->TradeBalance($login, MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
                return redirect()->back()->with('error', MTRetCode::GetError($error_code));
            } else {
                $deposit_details = BonusTrans::create([
                    'email' => $email,
                    'trade_id' => $trade_id,
                    'bonus_amount' => $amount,
                    'bonus_type' => $deposit_type,
                    'status' => 1,
                    'admin_remark' => $description,
                    'bonus_currency' => $deposit_currency,
                    'created_by' => session('alogin')
                ]);

                $toEmail = $email;
                $from = settings()['email_from_address'];
                $transid = "BTID" . str_pad($deposit_details->id, 4, '0', STR_PAD_LEFT);
                $emailSubject = settings()['admin_title'] . ' - Bonus Transaction';
                if ($type == "in") {
                    $content = '<div>We are pleased to inform your that Bonus have been successfully deposited into your account.</div>';
                } else {
                    $content = '<div>This email to inform you, that Bonus credited out from your account.</div>';
                }

                $content .= '<div><b>Transaction Details</b></div>
          <div><b>Amount: </b>$' . $deposit_details->bonus_amount . '</div>
          <div><b>Account ID: </b>' . $deposit_details->trade_id . '</div>
          <div><b>Transaction ID: </b>' . $transid . '</div>
          <div><b>Bonus Date: </b>' . date("Y-m-d H:i:s") . '</div>';

                $templateVars = [
                    'name' => $user->fullname,
                    'site_link' => settings()['copyright_site_name_text'],
                    'email' => settings()['email_from_address'],
                    "content" => $content,
                    "title_right" => "Bonus",
                    "subtitle_right" => "Credit Out",
                    "btn_text" => "Go To Dashboard",
                ];
                $this->mailService->sendEmail($email, $emailSubject, '', '', $templateVars);


                return redirect()->back()->with('success', 'Bonus ' . ($type === 'in' ? 'Credited' : 'Debited') . ' Successfully');
            }
        }
    }

    public function withdrawFromAccount(Request $request)
    {
        $eid = $request->input('email');
        $user = User::where('email', $eid)->first();
        $trade_id = $request->input('trade_id');
        if ($request->has('withdraw_from_account')) {
            $amount = $request->input('amount');
            $tw_amount = abs($request->input('amount')) * -1;
            $description = $request->input('description');
            $withdraw_type = 'CRM';
            $email = $eid;
            $login = $trade_id;
            $comment = 'CRM Withdrawal';
            $ticket = null;
            if (($error_code = $this->api->TradeBalance($login, MTEnDealAction::DEAL_BALANCE, $tw_amount, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
                return redirect()->back()->with("error", MTRetCode::GetError($error_code));
            } else {
                $deposit_details = TradeWithdrawals::create([
                    'email' => $email,
                    'trade_id' => $trade_id,
                    'withdrawal_amount' => $amount,
                    'withdraw_type' => $withdraw_type,
                    'admin_remark' => $description,
                    'created_by' => session('alogin')
                ]);

                // Update total_balance table
                // DB::table('total_balance')->insert([
                //     'email' => $email,
                //     'withdrawal_amount' => $amount
                // ]);

                // Send Email
                $transid = "TWID" . str_pad($deposit_details->id, 4, '0', STR_PAD_LEFT);
                $settings = settings();
                $emailSubject = $settings['admin_title'] . ' - Fund Withdrawal';
                $content = '<div>We are pleased to inform you that funds have been successfully withdrawn from your account.</div>
                <div><b>Withdrawal Details</b></div>
                <div><b>Amount: </b>$' . $deposit_details->withdrawal_amount . '</div>
                <div><b>Account ID: </b>' . $deposit_details->trade_id . '</div>
                <div><b>Transaction ID: </b>' . $transid . '</div>
                <div><b>Withdraw Date: </b>' . date("Y-m-d H:i:s") . '</div>
                <div><b>Withdraw Type </b>' . $deposit_details->withdraw_type . '</div>';
                $templateVars = [
                    'name' => $user->fullname,
                    'site_link' => settings()['copyright_site_name_text'],
                    'email' => settings()['email_from_address'],
                    "content" => $content,
                    "title_right" => "Fund",
                    "subtitle_right" => "Withdrawal",
                    "btn_text" => "Go To Dashboard",
                ];
                $this->mailService->sendEmail($email, $emailSubject, '', '', $templateVars);
                return redirect()->back()->with("success", "Withdrawal Successful");
            }
        }
    }

    private function sendEmail($toEmail, $subject, $content, $transaction)
    {
        $transid = strtoupper(substr($subject, 0, 2)) . 'ID' . str_pad($transaction->id, 4, '0', STR_PAD_LEFT);
        $templateVars = [
            'name' => $transaction->user->fullname ?? 'User',
            'site_link' => env('APP_URL'),
            'email' => env('MAIL_FROM_ADDRESS'),
            'content' => $content,
            'transid' => $transid,
            'amount' => $transaction->amount,
            'trade_id' => $transaction->trade_id,
            'date' => $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : date("Y-m-d H:i:s"),
        ];

        Mail::send('emails.transaction', $templateVars, function ($message) use ($toEmail, $subject) {
            $message->to($toEmail)
                ->subject($subject)
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
    }


    public function view(Request $request)
    {

        $trade_id = $request->query('id');
        AccountHelper::updateLiveAndDemoAccounts($trade_id);
        $type = "live";

        $sql = "
            SELECT
                liveaccount.*,
                aspnetusers.fullname,
                account_types.ac_group,
                IFNULL(SUM(bonus_trans.bonus_amount), 0) AS total_bonus_amount  -- Sum of bonus_amount from bonus_trans
            FROM liveaccount
            LEFT JOIN account_types ON account_types.ac_index = liveaccount.account_type
            LEFT JOIN aspnetusers ON aspnetusers.email = liveaccount.email
            LEFT JOIN bonus_trans ON bonus_trans.trade_id = liveaccount.trade_id  -- Join bonus_trans based on email
            WHERE (liveaccount.trade_id) = :trade_id
            GROUP BY liveaccount.id, aspnetusers.fullname, account_types.ac_group
        ";

        $query = DB::select($sql, ['trade_id' => $trade_id]);
        $getUser = isset($query[0]) ? $query[0] : [];

        if (!$getUser) {
            alert()->error("The MT5 account does not exist or has been deleted. Please try again.");
            return redirect("/admin/dashboard");
        }

        // Total approved deposits
        $total_deposit = DB::table('trade_deposit')
            ->where(DB::raw('trade_id'), $trade_id)
            ->where('status', 1)
            ->sum('deposit_amount');

        // Total unapproved deposits
        $unapproved_deposit = DB::table('trade_deposit')
            ->where(DB::raw('trade_id'), $trade_id)
            ->where('status', '!=', 1)
            ->sum('deposit_amount');

        // Total approved withdrawals
        $total_withdrawal = DB::table('trade_withdrawal')
            ->where(DB::raw('trade_id'), $trade_id)
            ->where('status', 1)
            ->sum('withdrawal_amount');

        // Total unapproved withdrawals
        $unapproved_withdrawal = DB::table('trade_withdrawal')
            ->where(DB::raw('trade_id'), $trade_id)
            ->where('status', '!=', 1)
            ->sum('withdrawal_amount');

        $bonus_trans = BonusTrans::where('status', 1)
            ->where(DB::raw('trade_id'), $trade_id)
            ->get();
        $account_types = DB::table('account_types')->where('status', 1)->get();

        $account = AccountHelper::getAccount($trade_id);

        return view("admin.mt5.view", [
            "id" => $trade_id,
            "getUser" => $getUser,
            "account" => $account,
            'total_deposit' => $total_deposit,
            'unapprove_deposit' => $unapproved_deposit,
            'total_withdrawl' => $total_withdrawal,
            'unapprove_withdrawl' => $unapproved_withdrawal,
            'bonus_trans' => $bonus_trans,
            'account_types' => $account_types,
            'type' => $type,
            'title' => 'MT5 Account Details'
        ]);
    }
}
