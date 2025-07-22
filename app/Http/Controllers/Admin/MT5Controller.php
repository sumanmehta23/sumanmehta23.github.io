<?php

namespace App\Http\Controllers\Admin;

use DB;
use Mail;
use App\Models\User;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\Promocode;
use App\Models\AccountType;
use App\MT5\MTEnDealAction;
use App\Models\TotalBalance;
use App\Models\TradeDeposit;
use App\Services\MT5Service;
use Illuminate\Http\Request;
use App\MT5\MTProtocolConsts;
use App\Helpers\AccountHelper;
use Illuminate\Validation\Rule;
use App\Models\BonusTransaction;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\MailService as MailService;

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

    public function promocode(Request $request)
    {
        return view('admin.promocode', [
        ]);
    }

    public function get_promocode($id)
    {
        $promocode = Promocode::find($id);

        if (!$promocode) {
            return response()->json([
                'success' => false,
                'message' => 'Promocode not found.'
            ]);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'code' => $promocode->code,
                'percentage' => $promocode->promo_percentage,
                'status' => $promocode->status,
                'id' => $promocode->id,
                'max_deposit' => $promocode->max_deposit,
            ]
        ]);
    }

    public function edit_promocode(Request $request)
    {
        $id = $request->id;
        $promocode = Promocode::find($id);
        // dd($promocode);
        if (!$promocode) {
            return response()->json([
                'success' => false,
                'message' => 'Promocode not found.'
            ]);
        }
        // Validate input
        $validator = Validator::make($request->all(), [
            'promo_code' => 'required|string|unique:promocode,code,' . $id,
            'promo_percentage' => 'required|numeric|min:0|max:100',
            // 'max_deposit' => 'required',
            'promo_status' => 'required|boolean', // Updated to accept boolean value
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ]);
        }

        $promocode->code = $request->promo_code;
        $promocode->promo_percentage = $request->promo_percentage;
        $promocode->status = (bool)$request->promo_status;
        $promocode->max_deposit = $request->max_deposit ?? '';
        $promocode->save();
        return response()->json([
            'success' => true,
            'message' => 'Promocode updated successfully',
        ]);
    }

    public function createPromoCode(Request $request)
    {
        $request->validate([
            // “code” must be unique among NOT-deleted rows
            'promo_code'       => [
                'required',
                Rule::unique('promocode', 'code')->whereNull('deleted_at'),
            ],
            'promo_percentage' => 'required|numeric|min:1|max:1000',
            // 'max_deposit' => 'required',
            'promo_status'     => 'required|boolean',
        ]);

        try {
            Promocode::create([
                'code'             => $request->promo_code,
                'promo_percentage' => $request->promo_percentage,
                'status'           => $request->promo_status,
                'max_deposit'      => $request->max_deposit ?? '',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Promocode added successfully',
            ]);
        } catch (\Throwable $e) {
            // log the actual error for debugging
            Log::error('Promocode create error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error adding promocode',
            ], 500);
        }
    }

    public function update_promocode_status(Request $request)
    {
        $promocode = Promocode::find($request->id);
        if ($promocode) {
            $promocode->status = $request->status;
            $promocode->save();
            return response()->json(['message' => 'Status updated successfully!']);
        }
        return response()->json(['message' => 'Promocode not found'], 404);
    }

    public function delete_promocode(Request $request)
    {
        $promocode = Promocode::find($request->id);

        if ($promocode) {
            $promocode->delete();
            return response()->json(['message' => 'Promocode deleted successfully!']);
        }

        return response()->json(['message' => 'Promocode not found'], 404);
    }


    public function updateAccountDetails(Request $request)
    {

        if ($request->has(['code', 'account_type'])) {
            $code = $request->input('code');
            $account_type = $request->input('account_type');
            $leverage = $request->input('leverage');

            // Fetch user data from API (assume the API method and classes are available)
            $trade_user = NULL;
            $this->api->UserGet($code,$trade_user);

            if (($error_code = $this->api->UserGet($code, $trade_user)) != MTRetCode::MT_RET_OK) {

                //dd(MTRetCode::GetError($error_code));
                // return response()->json([
                //     'status' => 'warning',
                //     'message' => 'Something went wrong on Updating details',
                //     'error' => MTRetCode::GetError($error_code)
                // ], 400);
                return redirect()->back()->with('error', 'Something went wrong on Updating details' . MTRetCode::GetError($error_code));
            }


            // dump($account_type);
            // dump($this);

            // Fetch account type details
            $acc = DB::table('account_types')
                ->where('id', $account_type)
                ->first();
            $account =Account::with('user')->where('code',$code)->first();

            if($account){
                $referral = $account->user->ib1;

                // if($referral && ($referral=="wealthytrades")) {
                //     $groupCode = str_replace("DF","SNSI",$acc->ac_group);
                //     $group = AccountType::where('ac_group', $groupCode)->first();
                //     // dd($group);
                //     if($group){
                //         $_POST["options"] =$group->id;
                //         $account_type_id = $group->id;
                //     }
                // }elseif($referral && (strtolower($referral)=="swingtradinglab")) {
                //     $groupCode = str_replace("DF","ALEX",$acc->ac_group);
                //     $group = AccountType::where('ac_group', $groupCode)->first();
                //     if($group){
                //         $_POST["options"] =$group->id;
                //         $account_type_id = $group->id;
                //     }
                // }else{
                //     $groupCode = $acc->ac_group;
                //     $account_type_id = $acc->id;
                // }
                $groupCode = $acc->ac_group;
                $account_type_id = $acc->id;
            }

            $trade_user->Group = $groupCode;

            $trade_user->Leverage = $leverage;
            info("Updated User Details ", ['code' => $code, 'group' => $groupCode, 'leverage' => $leverage]);
            // dd($trade_user);
            // Update user data via API
            $updated_user = "";
            if (($error_code = $this->api->UserUpdate($trade_user, $updated_user)) != MTRetCode::MT_RET_OK) {
                return redirect()->back()->with("error", "Something went wrong on Updating details" . MTRetCode::GetError($error_code));
            } else {
                // Update leverage and account type in the database
                DB::table('accounts')
                    ->where('code', $code)
                    ->update([
                        'leverage' => $leverage,
                        'account_type_id' => $account_type_id
                    ]);
                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'admin_email' => auth()->guard('admin')->user()->email,
                        'userRole' =>auth()->guard('admin')->user()->userRole,
                        'username' =>auth()->guard('admin')->user()->username,
                        'admin_id' =>auth()->guard('admin')->user()->id,
                        'code' => $code,
                        'leverage' => $leverage,
                        'account_type_id' => $account_type_id,
                        'remark' => 'CRM Update Group Leverage'
                    ])
                ->event('update')
                ->log('CRM Update Group Leverage');
                return redirect()->back()->with("success", "MT5 Account Details Successfully Updated");
            }
        }
    }

    public function updatePassword(Request $request)
    {
        if ($request->has(['code', 'password_type'])) {
            $login = $request->input('code');
            $pass_type = $request->input('password_type');
            $new_password = $request->input('password');
            $type = $request->input('type', 'live'); // default to 'live' if 'type' is not provided
            // Change main password
            if ($pass_type == 'main') {
                if (($error_code = $this->api->UserPasswordChange($login, $new_password, MTProtocolConsts::WEB_VAL_USER_PASS_MAIN)) != MTRetCode::MT_RET_OK) {
                    return redirect()->back()->with("error", 'Something went wrong on fetching details' . MTRetCode::GetError($error_code));
                } else {

                    $account = Account::where('code', $login)->first();
                    if ($account) {
                        $account->trader_password = $new_password;
                        $account->save(); // Save will apply casting and encrypt the password
                    }
                    activity()
                        ->causedBy(auth()->guard('admin')->user())
                        ->withProperties([
                            'ip' => request()->ip(),
                            'admin_email' => auth()->guard('admin')->user()->email,
                            'userRole' =>auth()->guard('admin')->user()->userRole,
                            'username' =>auth()->guard('admin')->user()->username,
                            'admin_id' =>auth()->guard('admin')->user()->id,
                            'code' => $login,
                            'new_password' => $new_password,
                            'remark' => 'CRM Update Master Password'
                        ])
                    ->event('update')
                    ->log('CRM Update Master Password');
                    return redirect()->back()->with("success", 'Your Master Password Successfully Updated');
                }
            }

            // Change investor password
            if ($pass_type == 'investor') {
                if (($error_code = $this->api->UserPasswordChange($login, $new_password, MTProtocolConsts::WEB_VAL_USER_PASS_INVESTOR)) != MTRetCode::MT_RET_OK) {
                    return redirect()->back()->with("error", 'Something went wrong on fetching details' . MTRetCode::GetError($error_code));
                } else {
                    $account = Account::where('code', $login)->first();
                    if ($account) {
                        $account->invester_password = $new_password;
                        $account->save(); // Save will apply casting and encrypt the password
                    }
                    activity()
                        ->causedBy(auth()->guard('admin')->user())
                        ->withProperties([
                            'ip' => request()->ip(),
                            'admin_email' => auth()->guard('admin')->user()->email,
                            'userRole' =>auth()->guard('admin')->user()->userRole,
                            'username' =>auth()->guard('admin')->user()->username,
                            'admin_id' =>auth()->guard('admin')->user()->id,
                            'code' => $login,
                            'new_password' => $new_password,
                            'remark' => 'CRM Update Investor Password'
                        ])
                    ->event('update')
                    ->log('CRM Update Investor Password');
                    return redirect()->back()->with('success', 'Your Investor Password Successfully Updated');
                }
            }
        }
    }

    public function depositToAccount(Request $request)
    {
        $eid = $request->input('email');
        $user_id = $request->input('client_id');
        $user = User::find($user_id);
        $code = $request->input('code');
        $account = Account::where('code', $code)->first();
        if ($request->has('deposit_to_account')) {
            $amount = str_replace(',', '', $request->input('amount'));
            $description = $request->input('description');
            $deposit_type = 'CRM';
            $email = $eid;
            $deposit_currency = 'USD';
            $login = $code;
            $comment = 'CRM Deposited';
            $ticket = null;

            if (($error_code = $this->api->TradeBalance($login, MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
                return redirect()->back()->with('error', MTRetCode::GetError($error_code));
            } else {

                $tradeDeposit = TradeDeposit::create([
                    'user_id' => $user->id,
                    'account_id' => $account->id,
                    'email' => $email,
                    'code' => $code,
                    'deposit_amount' => $amount,
                    'deposit_type' => $deposit_type,
                    'status' => 1,
                    'admin_remark' => $description,
                    'deposit_currency' => $deposit_currency,
                    'created_by' => session('alogin')
                ]);
                $transid = "TDID" . str_pad($tradeDeposit->id, 4, '0', STR_PAD_LEFT);

                // Store in total_balance table
                // DB::table('total_balance')->insert([
                //     'email' => $email,
                //     'trading_deposited' => $amount
                // ]);
                TotalBalance::create([
                    'user_id' => $user->id,
                    'account_id' => $account->id,
                    'email' => $email,
                    'code' => $account->code,
                    'trading_deposited' => $amount,
                ]);
                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'admin_email' => auth()->guard('admin')->user()->email,
                        'userRole' =>auth()->guard('admin')->user()->userRole,
                        'username' =>auth()->guard('admin')->user()->username,
                        'admin_id' =>auth()->guard('admin')->user()->id,
                        'client_id' => $user->id,
                        'client_email' => $email,
                        'deposit_amount' => $amount,
                        'code' => $code,
                        'account_id' => $account->id,
                        'remark' => 'CRM Deposit'
                    ])
                ->event('create')
                ->log('CRM Deposit');

                $settings = settings();
                $emailSubject = $settings['admin_title'] . ' - Fund Deposit';
                $content = '<div>We are pleased to inform you that funds have been successfully deposited into your account.</div>
          <div><b>Transaction Details</b></div>
          <div><b>Amount: </b>$' . $amount . '</div>
          <div><b>Account ID: </b>' . $code . '</div>
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
        $key = 'deposit:' . (auth()->id() ?: $request->ip());

        // Check if the user has exceeded the rate limit
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retryAfter = RateLimiter::availableIn($key);
            return redirect()->back()->with(
                'error',
                "Too many requests. Please wait {$retryAfter} seconds before trying again."
            );
        }

        // Increment the rate limiter
        RateLimiter::hit($key, 10);

        $eid = $request->input('email');
        $user_id = $request->input('client_id');
        $user = User::find($user_id);
        $code = $request->input('code');
        $account = Account::where('code', $code)->first();
        if ($request->has('bonus_to_account')) {

            $amount = $request->input('amount');
            $description = $request->input('description');
            $type = $request->input('type');
            $deposit_type = $type === 'in' ? 'Bonus In' : 'Bonus Out';
            $amount = $type === 'in' ? $amount : -1 * $amount;
            $email = $eid;
            $deposit_currency = 'USD';
            $login = $code;
            // $comment = $description;
            $comment = $type === 'in' ? 'Bonus Deposit' : 'Bonus Withdraw';;
            $ticket = null;

            $loginss = [];

            if (in_array($login, $loginss)) {
                $operation = MTEnDealAction::DEAL_BONUS;
            } else {
                $operation = MTEnDealAction::DEAL_BALANCE;
            }

            if (($error_code = $this->api->TradeBalance($login, $operation, $amount, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
                return redirect()->back()->with('error', MTRetCode::GetError($error_code));
            } else {
                $deposit_details = BonusTransaction::create([
                    'email' => $email,
                    'user_id' => $user->id,
                    'account_id' => $account->id,
                    'code' => $code,
                    'bonus_amount' => $amount,
                    'bonus_type' => $deposit_type,
                    'status' => 1,
                    'admin_remark' => $comment,
                    'bonus_currency' => $deposit_currency,
                    // 'created_by' => session('alogin')
                ]);

                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'admin_email' => auth()->guard('admin')->user()->email,
                        'userRole' =>auth()->guard('admin')->user()->userRole,
                        'username' =>auth()->guard('admin')->user()->username,
                        'admin_id' =>auth()->guard('admin')->user()->id,
                        'client_id' => $user->id,
                        'client_email' => $email,
                        'bonus_amount' => $amount,
                        'bonus_type' => $comment,
                        'code' => $code,
                        'account_id' => $account->id,
                        'remark' => 'CRM Deposit Bonus'
                    ])
                ->event('create')
                ->log('CRM Bonus');

                $toEmail = $email;
                $from = settings()['email_from_address'];
                $transid = "BTID" . str_pad($deposit_details->id, 4, '0', STR_PAD_LEFT);
                $emailSubject = settings()['admin_title'] . ' - Bonus Transaction';
                if ($type == "in") {
                    $content = '<p>We are pleased to inform your that Bonus have been successfully deposited into your account.</p>';
                } else {
                    $content = '<p>This email to inform you, that Bonus credited out from your account.</p>';
                }

                $content .= '
                                <p></p>
                                <p></p>
                                <p><b>Transaction Details</b></p>
                                <p></p>
                                <p><b>Amount: </b>$' . $deposit_details->bonus_amount . '</p>
                                <p><b>Account ID: </b>' . $deposit_details->code . '</p>
                                <p><b>Transaction ID: </b>' . $transid . '</p>
                                <p><b>Bonus Date: </b>' . date("Y-m-d H:i:s") . '</p>'
                            ;

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
    public function creditBonusToAccount(Request $request)
    {
        $key = 'deposit:' . (auth()->id() ?: $request->ip());

        // Check if the user has exceeded the rate limit
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retryAfter = RateLimiter::availableIn($key);
            return redirect()->back()->with(
                'error',
                "Too many requests. Please wait {$retryAfter} seconds before trying again."
            );
        }

        // Increment the rate limiter
        RateLimiter::hit($key, 10);

        $eid = $request->input('email');
        $user_id = $request->input('client_id');
        $user = User::find($user_id);
        $code = $request->input('code');
        $account = Account::where('code', $code)->first();

        if ($request->has('bonus_to_account_credit')) {

            $amount = $request->input('amount');
            $description = $request->input('description');
            $type = $request->input('type');
            $deposit_type = $type === 'in' ? 'Bonus In' : 'Bonus Out';
            $amount = $type === 'in' ? $amount : -1 * $amount;
            $email = $eid;
            $deposit_currency = 'USD';
            $login = $code;
            // $comment = $description;
            $comment = $type === 'in' ? 'Bonus Credit In' : 'Bonus Credit Out';
            $ticket = null;
            // dd($comment);
            if (($error_code = $this->api->TradeBalance($login, MTEnDealAction::DEAL_BONUS, $amount, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
                return redirect()->back()->with('error', MTRetCode::GetError($error_code));
            } else {
                $deposit_details = BonusTransaction::create([
                    'email' => $email,
                    'user_id' => $user->id,
                    'account_id' => $account->id,
                    'code' => $code,
                    'bonus_amount' => $amount,
                    'bonus_type' => $deposit_type,
                    'status' => 1,
                    'admin_remark' => $comment,
                    'bonus_currency' => $deposit_currency,
                    // 'created_by' => session('alogin')
                ]);

                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'admin_email' => auth()->guard('admin')->user()->email,
                        'userRole' =>auth()->guard('admin')->user()->userRole,
                        'username' =>auth()->guard('admin')->user()->username,
                        'admin_id' =>auth()->guard('admin')->user()->id,
                        'client_id' => $user->id,
                        'client_email' => $email,
                        'bonus_amount' => $amount,
                        'bonus_type' => $comment,
                        'code' => $code,
                        'account_id' => $account->id,
                        'remark' => 'CRM Credit Bonus'
                    ])
                ->event('create')
                ->log('CRM Bonus');

                $toEmail = $email;
                $from = settings()['email_from_address'];
                $transid = "BTID" . str_pad($deposit_details->id, 4, '0', STR_PAD_LEFT);
                $emailSubject = settings()['admin_title'] . ' - Bonus Transaction';
                if ($type == "in") {
                    $content = '<p>We are pleased to inform your that Bonus have been successfully deposited into your account.</p>';
                } else {
                    $content = '<p>This email to inform you, that Bonus credited out from your account.</p>';
                }

                $content .= '
                                <p></p>
                                <p></p>
                                <p><b>Transaction Details</b></p>
                                <p></p>
                                <p><b>Amount: </b>$' . $deposit_details->bonus_amount . '</p>
                                <p><b>Account ID: </b>' . $deposit_details->code . '</p>
                                <p><b>Transaction ID: </b>' . $transid . '</p>
                                <p><b>Bonus Date: </b>' . date("Y-m-d H:i:s") . '</p>'
                            ;

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
        $user_id = $request->input('client_id');
        $user = User::find($user_id);
        $code = $request->input('code');
        $account = Account::where('code', $code)->first();
        // dd($user_id);
        // dd($user->id);
        if ($request->has('withdraw_from_account')) {
            $amount = $request->input('amount');
            $tw_amount = abs($request->input('amount')) * -1;
            $description = $request->input('description');
            $withdraw_type = 'CRM';
            $email = $eid;
            $login = $code;
            $comment = 'CRM Withdrawal';
            $ticket = null;
            if (($error_code = $this->api->TradeBalance($login, MTEnDealAction::DEAL_BALANCE, $tw_amount, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
                return redirect()->back()->with("error", MTRetCode::GetError($error_code));
            } else {
                $deposit_details = TradeWithdrawals::create([
                    'email' => $email,
                    'user_id' => $user->id,
                    'account_id' => $account->id,
                    'code' => $account->code,
                    'withdraw_to' => null,
                    'withdrawal_amount' => $amount,
                    'withdraw_type' => $withdraw_type,
                    'admin_remark' => $description,
                    'Status'=>1,
                    'created_by' => session('alogin')
                ]);
                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'admin_email' => auth()->guard('admin')->user()->email,
                        'userRole' =>auth()->guard('admin')->user()->userRole,
                        'username' =>auth()->guard('admin')->user()->username,
                        'admin_id' =>auth()->guard('admin')->user()->id,
                        'client_id' => $user->id,
                        'client_email' => $email,
                        'withdrawal_amount' => $amount,
                        'code' => $code,
                        'account_id' => $account->id,
                        'remark' => 'CRM Withdraw'
                    ])
                ->event('create')
                ->log('CRM Withdraw');
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
                <div><b>Account ID: </b>' . $deposit_details->code . '</div>
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
            'code' => $transaction->code,
            'date' => $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : date("Y-m-d H:i:s"),
        ];

        Mail::send('emails.transaction', $templateVars, function ($message) use ($toEmail, $subject) {
            $message->to($toEmail)
                ->subject($subject)
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
    }


    public function view(Request $request, $id)
    {

        $account = Account::where('id',$id)->with(['accountType','user','BonusTransaction'])->first();

        if($account){
            $code = $account->code;
        }else{
            $code ='';
        }

        if($account->demo == false){
            AccountHelper::updateLiveAndDemoAccounts($account->id);
            $type = "live";
        }else{
            $type = "demo";
        }
        $account = Account::where('id',$id)->with(['accountType','user','BonusTransaction'])->first();

        if (!$account) {
            alert()->error("The MT5 account does not exist or has been deleted. Please try again.");
            return redirect("/admin/dashboard");
        }

        // Total approved deposits
        // $total_deposit = DB::table('trade_deposit')
        //     ->where(DB::raw('code'), $account->code)
        //     ->where('status', 1)
        //     ->sum('deposit_amount');
        $total_deposit = TradeDeposit::where('account_id', $account->id)
            ->where('status', 1)
            ->sum('deposit_amount');

        // Total unapproved deposits
        $unapproved_deposit = TradeDeposit::where('account_id', $account->id)
            ->where('status', '!=', 1)
            ->sum('deposit_amount');

        // Total approved withdrawals
        $total_withdrawal = TradeWithdrawals::where('account_id', $account->id)
            ->where('status', 1)
            ->sum('withdrawal_amount');

        // Total unapproved withdrawals
        $unapproved_withdrawal = TradeWithdrawals::where('account_id', $account->id)
            ->where('status', '!=', 1)
            ->sum('withdrawal_amount');

        $bonus_trans = BonusTransaction::where('status', 1)
            ->where("account_id"  ,$account->id)
            ->get();
        $account_types = AccountType::where('status', 1)->get();

        // $account = AccountHelper::getAccount( $account->code);
        $accountHelper = AccountHelper::getAccount( $account->code);

        return view("admin.mt5.view", [
            "id" => $code,
            "getUser" =>  $account,
            "account" => $account,
            "accountHelper" => $accountHelper,
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
