<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\Leverage;
use App\Models\AccountType;
use App\Models\DemoDeposit;
use App\MT5\MTEnDealAction;
use App\Services\MT5Service;
use Illuminate\Http\Request;
use App\Models\Ib1Commission;
use App\MT5\MTProtocolConsts;
use App\Http\Controllers\Controller;
use App\Models\Ib1;
use App\Services\MailService as MailService;
use Illuminate\Support\Facades\Log;
use App\Models\TradeWithdrawals;
use App\Models\TotalBalance;
use App\Models\WalletDeposit;
use Illuminate\Support\Facades\DB;
class MT5Accounts extends Controller
{
    protected $api;
    protected $mailService;
    protected $mt5Service;
    public function __construct(MT5Service $mt5Service, MailService $mailService)
    {
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
    }
    public function liveAccounts()
    {

        $email = auth()->user()->email;
        $results = Account::with('accountType')
            ->where('user_id', auth()->user()->id)
            ->where('demo', false)
            ->orderBy('id', 'desc')
            ->get();
        return view('live_accounts', compact('results'));
    }

    public function demoAccounts()
    {

        $results = Account::where('user_id', auth()->user()->id)
            ->where('demo', true)
            ->orderBy('id', 'desc')
            ->get();
        return view('demo_accounts', compact('results'));
    }
    public function viewAccountDetails(Account $account)
    {

        session()->remove('error');
        $user= auth()->user();
        if($user->id != $account->user_id){
            return redirect()->route('liveAccounts')->with('error', 'User Details Not Matching');
        }
        $code=$account->code;
        $type=$account->demo ? 'Demo' : 'Live';
        // $account=Account::where('id',$id)->where('user_id',$user->id)->firstOrFail();
        $settings = settings();
        $results = [];
        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $this->api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );
        $getUser = [];
        $equity = '';
        $margin = '';
        $marginlevel = '';
        $accountSwap = '';
        $freemargin = '';
        $profit = '';
        try {
            $login = $code;
            // Fetch positions
            if (($error_code = $this->api->PositionGetTotal($login, $total)) != MTRetCode::MT_RET_OK) {
                session()->flash('error', 'MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
            }
            $open_order_history = $total;
            $offset = 0;
            $positions = [];
            // Fetch position pages
            if (($error_code = $this->api->PositionGetPage($login, $offset, $total, $positions)) != MTRetCode::MT_RET_OK) {
                session()->flash('error', 'MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
            }
            // Fetch user account details
            if (($error_code = $this->api->UserAccountGet($login, $mt5account)) != MTRetCode::MT_RET_OK) {
                session()->flash('error', 'MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
            }
            if ($mt5account) {
                // account login get
                $mt5account->Login;
                // balance get
                $balance = $mt5account->Balance;
                // balance get
                $balance = $mt5account->Balance;
                // Credit get
                $credit = $mt5account->Credit;
                // profit get
                $profit = $mt5account->Floating;
                // Free Margin get
                $freemargin = $mt5account->MarginFree;
                // credit get
                $credit = $mt5account->Credit;
                // equity --  $balance + $Credit+$Profit
                $equity = ($balance + $credit + $profit);
                // margin level get
                $margin = $mt5account->Margin;
                $marginlevel = round((($balance - $freemargin) / (1000)), 2);
                // Update live account with new data
                $account->update([
                    'balance' => $mt5account->Balance,
                    'credit' => $mt5account->Credit,
                    'margin_free' => $mt5account->MarginFree,
                    'margin_level' => $mt5account->MarginLevel,
                    'equity' => $mt5account->Equity
                ]);
            }
            // Fetch order history
            $from = 'March 01,2016';
            $to = 'March 31,2080';
            if (($error_code = $this->api->HistoryGetTotal($login, $from, $to, $total)) != MTRetCode::MT_RET_OK) {
                session()->flash('error', 'MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
            }
            $closed_order_history = $total;
            // Fetch order pages
            if (($error_code = $this->api->HistoryGetPage($login, $from, $to, $offset, $total, $orders)) != MTRetCode::MT_RET_OK) {
                session()->flash('error', 'MT5 ' . MTRetCode::GetError($error_code));
            }
            $getUser =Account::with('accountType', 'BonusTransaction')
            ->where('id', $account->id)
            ->first();
            $accountSwap = $getUser->accountType ? $getUser->accountType->ac_swap : null;
            // Process orders
            // dd($orders);
            if (!empty($orders)) {
                foreach ($orders as $item) {
                    $volume = $item->VolumeInitial * 0.00001;
                    $time_closed = gmdate("Y-m-d H:i:s", $item->TimeDone);
                    // Insert commission data into DB
                    Ib1Commission::updateOrCreate(['order_id' => $item->Order,
                        'code' => $item->Login,],
                    [
                        'user_id' => auth()->user()->id,
                        'account_id' => $account->id,

                        'volume' => $volume,
                        'time_closed' => $time_closed
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Exception: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            session()->flash('error', 'Exception: ' . $e->getMessage());
        }
        return view('view-account-details', compact('results', 'code', 'type', 'settings', 'account', 'getUser', 'equity', 'margin', 'marginlevel', 'accountSwap', 'freemargin', 'profit'));

    }
    public function showLiveAccountForm()
    {
        $user=auth()->user();
        $email  = $user->email;
        $results = AccountType::whereHas('mt5Group', function ($query) {
            $query->where('mt5_group_type', 'live')
                ->orWhere('mt5_group_type', 'real');
        })->where('is_client_group', 1)
            ->orderBy('display_priority', 'DESC')
            ->with('mt5Group:mt5_group_id,mt5_group_type')
            ->get();
        return view('create-live-account', compact('user', 'results'));
    }
    public function showDemoAccountForm()
    {
        $user=auth()->user();
        $email  = $user->email;
        // $user = User::where('email', $email)->first();
        $results = AccountType::with('mt5Group')
            ->whereHas('mt5Group', function ($query) {
                $query->where('mt5_group_type', 'demo');
            })
            ->where('is_client_group', 1)
            ->orderBy('display_priority', 'desc')
            ->get();
        return view('create-demo-account', compact('user', 'results'));
    }
    public function getLeverage(Request $request)
    {
        $accountTypeId = $request->query('id');

        $leverage = Leverage::where('account_type_id', $accountTypeId)->get();
        return response()->json($leverage);
    }
    public function updateLeverage(Request $request)
    {
        // dump($user);
        $login = $request->accountId;
        $account_code = Account::where('id', $login)->value('code');
        $accountTypeId = $request->modalAccountId;
        $newLeverage = $request->leverage;
        $comment = $request->update_leverage;
        $updated_user = "";

        if (($error_code = $this->api->UserGet($account_code, $trade_user)) != MTRetCode::MT_RET_OK) {
            return redirect()->back()->with('error', 'Something went wrong on Updating details' . MTRetCode::GetError($error_code));
        }
        if (($error_code = $this->api->PositionGetTotal($account_code, $total_positions)) != MTRetCode::MT_RET_OK) {
            return redirect()->back()->with('error', 'Something went wrong on Updating details' . MTRetCode::GetError($error_code));
        }

        if($total_positions == 0){
            $trade_user->Leverage = $newLeverage;

            $error_code = $this->api->UserUpdate($trade_user, $updated_user);
                if ($error_code != MTRetCode::MT_RET_OK) {
                    return redirect()->back()->with("error", "Something went wrong on Updating details" . MTRetCode::GetError($error_code));
                } else {
                    Account::where('id', $login)->update([
                        'leverage' => $newLeverage
                    ]);
                }

            return redirect()->back()->with('success', 'Leverage updated successfully!');
        }else{
            return redirect()->back()->with('warning', 'Trades need to be closed!');
        }


    }
    public function createLiveAccount(Request $request)
    {

        $settings = settings();
        $validatedData = $request->validate([
            'options' => 'required|string',
            'leverage' => 'required|string',
        ]);

        $user = auth()->user();
        $nick_name = $request->nick_name;

        $email = $user->email;
        $group = AccountType::where('id', $validatedData['options'])->firstOrFail();
        $referral=$user->referral;
        $ib=$user->ib1;
        $account_type_id = $validatedData['options'];
        //wealthytrades
        if($referral=="wealthytrades" || $ib=="wealthytrades") {
            $groupCode = str_replace("DF","SNSI",$group->ac_group);
            $group = AccountType::where('ac_group', $groupCode)->first();

            if($group){
                $_POST["options"] =$group->id;
                $account_type_id = $group->id;
            }
        }elseif(strtolower($referral)=="swingtradinglab" || strtolower($ib)=="swingtradinglab") {
            $groupCode = str_replace("DF","ALEX",$group->ac_group);
            $group = AccountType::where('ac_group', $groupCode)->first();
            if($group){
                $_POST["options"] =$group->id;
                $account_type_id = $group->id;
            }

        }else{
            $groupCode = $group->ac_group;
        }

         $userAcc = Account::where('user_id', $user->id)->where('demo',0)->get();

        $ibdata = '';
        if ($ib) {
            $ibdata = Ib1::where('referral_code',$ib)->first();
        }

        if ($userAcc && count($userAcc) < 2) {
            $new_user = $this->api->UserCreate();
            $new_user->MainPassword = $this->generatePassword();
            $new_user->Group = $group->ac_group;
            $new_user->type = $group->ac_name;
            $new_user->Leverage = $validatedData['leverage'];
            $new_user->ZipCode = $user->zipcode;
            $new_user->Country = $user->country;
            $new_user->State = $user->state;
            $new_user->City = $user->city;
            $new_user->Address = $user->address;
            $new_user->Phone = $user->number;
            $new_user->Currency = 'USD';
            $new_user->Company = $settings['mt5_company_name'];
            $new_user->Name = $user->fullname??$user->email;
            $new_user->Email = $user->email;
            $new_user->LeadSource = $user->ib1?? "" ;
            $new_user->Agent = $ibdata->indexId?? "" ;
            $new_user->PhonePassword = $this->generatePassword();
            $new_user->InvestPassword = $this->generatePassword();
            $new_user->Login = $this->generateRandomNumber();
            $response = $this->CreateAccount($new_user, $user_server, 'Live');

            if ($response['status']) {
                activity()->causedBy($user->id)
                    ->withProperties(
                        [
                            'ip' => $request->ip(),
                            'email' => $user->email,
                            'type' => 'Live',
                            'code' => $new_user->Login,
                            'leverage' => $new_user->Leverage,
                            'ib' => $ib,
                            'remark' => 'Create Live Account'
                        ])
                ->event('create')
                ->log('Create Live Account');
                Account::create([
                    'user_id' => $user->id,
                    'name' => $new_user->Name,
                    'demo'=> false,
                    'email' => $new_user->Email,
                    'account_nick_name' =>  $nick_name,
                    'code' => $new_user->Login,
                    'account_type_id' => $account_type_id,
                    'leverage' => $new_user->Leverage,
                    'currency' => $new_user->Currency,
                    'trader_password' => $new_user->MainPassword,
                    'invester_password' => $new_user->InvestPassword,
                    'phone_password' => $new_user->PhonePassword,
                    'ib1' => $new_user->LeadSource,
                    'account_request_status' => '1',
                ]);
                $this->sendMail($new_user, 'Live');
                // return redirect()->back()->with('success', $response['message']);
                return redirect()->back()->with('success', $response['message']);
            } else {
                return redirect()->back()->with('error', $response['message']);
            }
        }else {
            $settings = settings();
            activity()->causedBy($user->id)
                ->withProperties(
                    [
                        'ip' => $request->ip(),
                        'email' => $user->email,
                        'type' => 'Live',
                        'code' => 'Pending',
                        'leverage' => $validatedData['leverage'],
                        'ib' => $ib,
                        'remark' => 'Create Live Account'
                    ])
            ->event('create')
            ->log('Create Live Account');
            $useraccount = Account::create([
                'user_id' => $user->id,
                'name' => $user->fullname??$user->email,
                'demo'=> false,
                'email' => $user->email,
                'account_nick_name' =>  $nick_name,
                'account_type_id' => $account_type_id,
                'leverage' => $validatedData['leverage'],
                'currency' => 'USD',
                'ib1' => $user->ib1?? "",
                'account_request_status' => '0',

            ]);
            if($useraccount){

                $from = $settings['email_from_address'];
                $emailSubject = 'Trading Account Requested';
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $content =
                    '<div>Thank you for choosing LQH Markets. Your request for a new trading account will be approved  within 24-48 hours.</div>

                    <p>If you need any assistance, our support team is available 24/7 at support@lqhmarkets.com</p>
                    <p>Best Regards.</p>
                <p>LQH Markets Team</p>';
                $templateVars = [
                    'name' => $user->fullname,
                    'email' => $settings['email_from_address'],
                    "content" => $content,
                    "title_right" => "Account Creation Request Pending",
                    "subtitle_right" => "",
                ];
                $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
                return redirect()->back()->with('success', 'Account Request Received Your request has been submitted.');
            } else {
                return redirect()->back()->with('error', 'Account not created');
            }

        }

        // $new_user = $this->api->UserCreate();
        // $new_user->MainPassword = $this->generatePassword();
        // $new_user->Group = $group->ac_group;
        // $new_user->type = $group->ac_name;
        // $new_user->Leverage = $validatedData['leverage'];
        // $new_user->ZipCode = $user->zipcode;
        // $new_user->Country = $user->country;
        // $new_user->State = $user->state;
        // $new_user->City = $user->city;
        // $new_user->Address = $user->address;
        // $new_user->Phone = $user->number;
        // $new_user->Currency = 'USD';
        // $new_user->Company = $settings['mt5_company_name'];
        // $new_user->Name = $user->fullname??$user->email;
        // $new_user->Email = $user->email;
        // $new_user->LeadSource = $user->ib1?? "" ;
        // $new_user->PhonePassword = $this->generatePassword();
        // $new_user->InvestPassword = $this->generatePassword();
        // $new_user->Login = $this->generateRandomNumber();
        // $response = $this->CreateAccount($new_user, $user_server, 'Live');

        // if ($response['status']) {
        //     Account::create([
        //         'user_id' => $user->id,
        //         'name' => $new_user->Name,
        //         'demo'=> false,
        //         'email' => $new_user->Email,
        //         'name' => $new_user->Name,
        //         'code' => $new_user->Login,
        //         'account_type_id' => $account_type_id,
        //         'leverage' => $new_user->Leverage,
        //         'currency' => $new_user->Currency,
        //         'trader_password' => $new_user->MainPassword,
        //         'invester_password' => $new_user->InvestPassword,
        //         'phone_password' => $new_user->PhonePassword,
        //         'ib1' => $new_user->LeadSource,
        //     ]);
        //     $this->sendMail($new_user, 'Live');
        //     return redirect()->back()->with('success', $response['message']);
        // } else {
        //     return redirect()->back()->with('error', $response['message']);
        // }
    }
    public function activateAccount(Request $request)
    {
        dd($request->all());
        $settings = settings();
        if($request->accountType == 0)
        {
            $validatedData = $request->validate([
                'options' => 'required|string',
                'leverage' => 'required|string',
            ]);

            // $user = auth()->user();
            $user = User::where('id', $request->client_id)->first();

            $group = AccountType::where('id', $validatedData['options'])->firstOrFail();

            $referral=$user->referral;
            $ib=$user->ib1;
            $account_type_id = $validatedData['options'];

            //wealthytrades
            if($referral=="wealthytrades" || $ib=="wealthytrades") {
                $groupCode = str_replace("DF","SNSI",$group->ac_group);
                $group = AccountType::where('ac_group', $groupCode)->first();

                if($group){
                    $_POST["options"] =$group->id;
                    $account_type_id = $group->id;
                }
            }elseif(strtolower($referral)=="swingtradinglab" || strtolower($ib)=="swingtradinglab") {
                $groupCode = str_replace("DF","ALEX",$group->ac_group);
                $group = AccountType::where('ac_group', $groupCode)->first();
                if($group){
                    $_POST["options"] =$group->id;
                    $account_type_id = $group->id;
                }
            }else{
                $groupCode = $group->ac_group;
            }
            $ibdata = '';
            if($ib){
                $ibdata = Ib1::where('referral_code',$ib)->first();
            }
            if ($request->request_status == 1) {
                $new_user = $this->api->UserCreate();
                $new_user->MainPassword = $this->generatePassword();
                $new_user->Group = $group->ac_group;
                $new_user->type = $group->ac_name;
                $new_user->Leverage = $validatedData['leverage'];
                $new_user->ZipCode = $user->zipcode;
                $new_user->Country = $user->country;
                $new_user->State = $user->state;
                $new_user->City = $user->city;
                $new_user->Address = $user->address;
                $new_user->Phone = $user->number;
                $new_user->Currency = 'USD';
                $new_user->Company = $settings['mt5_company_name'];
                $new_user->Name = $user->fullname??$user->email;
                $new_user->Email = $user->email;
                $new_user->LeadSource = $user->ib1?? "" ;
                $new_user->Agent = $ibdata->indexId?? "" ;
                $new_user->PhonePassword = $this->generatePassword();
                $new_user->InvestPassword = $this->generatePassword();
                $new_user->Login = $this->generateRandomNumber();
                $response = $this->CreateAccount($new_user, $user_server, 'Live');

                if ($response['status']) {
                    $account = Account::where('id', $request->account_id)->first();

                    activity()->causedBy(auth()->user()->id)
                            ->withProperties(
                                [
                                    'ip' => $request->ip(),
                                    'email' => auth()->user()->email,
                                    'client_email' => $user->email,
                                    'type' => 'Live',
                                    'code' => $new_user->Login,
                                    'leverage' => $new_user->Leverage,
                                    'ib' => $ib,
                                    'remark' => 'Create Live Account'
                                ])
                        ->event('create')
                        ->log('Create Live Account');
                    if($account)
                    {
                        $account->update([
                            'user_id' => $user->id,
                            'name' => $new_user->Name,
                            'demo'=> false,
                            'email' => $new_user->Email,
                            // 'name' => $new_user->Name,
                            'code' => $new_user->Login,
                            'account_type_id' => $account_type_id,
                            'leverage' => $new_user->Leverage,
                            'currency' => $new_user->Currency,
                            'trader_password' => $new_user->MainPassword,
                            'invester_password' => $new_user->InvestPassword,
                            'phone_password' => $new_user->PhonePassword,
                            'ib1' => $new_user->LeadSource,
                            'account_request_status' => 1,
                        ]);
                        $this->sendMail($new_user, 'Live');
                        return redirect()->back()->with('success', $response['message']);
                    }else{
                        return redirect()->back()->with('error', 'No account found to update.');
                    }
                } else {
                    return redirect()->back()->with('error', $response['message']);
                }
            }elseif($request->request_status == 2){
                $account = Account::where('id', $request->account_id)->first();
                // dd($account);
                if($account)
                {
                    $account->update([
                        'user_id' => $user->id,
                        'name' => $user->fullname??$user->email,
                        'demo'=> false,
                        'email' => $user->email,
                        'account_type_id' => $account_type_id,
                        'leverage' => $validatedData['leverage'],
                        'currency' => 'USD',
                        'ib1' => $user->ib1?? "",
                        'code' => 'Rejected',
                        'account_request_status' => 0,
                    ]);
                    return redirect()->back()->with('success', 'Account Rejected');
                }else{
                    return redirect()->back()->with('error', 'No account found to update.');
                }
            }
        }
        // else{

        //     $validatedData = $request->validate([
        //         'options' => 'required|string',
        //         'leverage' => 'required|string',
        //         'demo_deposit' => 'required|numeric|min:1',
        //     ]);
        //     // $user = auth()->user();
        //     $user = User::where('id', $request->client_id)->first();

        //     $email = $user->email;

        //     $group = AccountType::where('id', $validatedData['options'])->firstOrFail();

        //     if($group->ac_min_deposit){
        //         $validatedData = $request->validate([
        //             'options' => 'required|string',
        //             'leverage' => 'required|string',
        //             'demo_deposit' => 'required|numeric|min:'.$group->ac_min_deposit,
        //         ]);
        //     }

        //     if ($request->request_status == 1) {

        //         $new_user = $this->api->UserCreate();
        //         $new_user->MainPassword = $this->generatePassword();
        //         $new_user->Group = $group->ac_group;
        //         $new_user->type = $group->ac_name;
        //         $new_user->Leverage = $validatedData['leverage'];
        //         $new_user->ZipCode = $user->zipcode;
        //         $new_user->Country = $user->country;
        //         $new_user->State = $user->state;
        //         $new_user->City = $user->city;
        //         $new_user->Address = $user->address;
        //         $new_user->Phone = $user->number;
        //         $new_user->Currency = 'USD';
        //         $new_user->Company = $settings['mt5_company_name'];
        //         $new_user->Name =  $user->fullname??$user->email;
        //         $new_user->Email = $user->email;
        //         $new_user->LeadSource = $user->ib1 ?? "" ;
        //         $new_user->PhonePassword = $this->generatePassword();
        //         $new_user->InvestPassword = $this->generatePassword();
        //         $new_user->Login = $this->generateRandomNumber();
        //         $response = $this->CreateAccount($new_user, $user_server, 'Demo');

        //         if ($response['status']) {

        //             $account = Account::where('id', $request->account_id)->first();
        //             // dd($account);
        //             if($account)
        //             {
        //                 $account->update([
        //                     'user_id' => $user->id,
        //                     'name' => $new_user->Name,
        //                     'demo' => true,
        //                     'email' => $new_user->Email,
        //                     'code' => $new_user->Login,
        //                     'account_type_id' => $validatedData['options'],
        //                     'leverage' => $new_user->Leverage,
        //                     'currency' => $new_user->Currency,
        //                     'trader_password' => $new_user->MainPassword,
        //                     'invester_password' => $new_user->InvestPassword,
        //                     'phone_password' => $new_user->PhonePassword,
        //                     'balance' => $validatedData['demo_deposit'],
        //                     'account_request_status' => 1,
        //                 ]);
        //             }
        //             $errorCode = $this->api->TradeBalance($new_user->Login, $type = MTEnDealAction::DEAL_BALANCE, $validatedData['demo_deposit'], 'Deposit', $ticket, $margin_check = true);
        //             if ($errorCode != MTRetCode::MT_RET_OK) {
        //                 $error = MTRetCode::GetError($errorCode);
        //                 Log::error('MT5 demo account : ' . $error.' for user '.$user->id);
        //                 return redirect()->back()->with('success', $error);
        //             } else {

        //                 $data = [
        //                     'user_id' => $user->id,
        //                     'account_id'=>$account->id,
        //                     'email' => $new_user->Email,
        //                     'code' => $new_user->Login,
        //                     'deposit_amount' => $validatedData['demo_deposit'],
        //                     'Status' => 1
        //                 ];

        //                 DemoDeposit::create($data);
        //             }
        //             $this->sendMail($new_user, 'Demo');
        //             return redirect()->back()->with('success', $response['message']);
        //         } else {
        //             return redirect()->back()->with('error', $response['message']);
        //         }
        //     }elseif($request->request_status == 2){

        //         $account = Account::where('id', $request->account_id)->first();
        //         // dd($account);
        //         if($account)
        //         {
        //             $account->update([
        //                 'user_id' => $user->id,
        //                 'name' => $user->fullname??$user->email,
        //                 'demo' => true,
        //                 'email' => $user->email,
        //                 'code' => 'Rejected',
        //                 'account_type_id' => $validatedData['options'],
        //                 'leverage' => $validatedData['leverage'],
        //                 'currency' => 'USD',
        //                 'balance' => $validatedData['demo_deposit'],
        //                 'account_request_status' => 0,
        //             ]);
        //             return redirect()->back()->with('success', 'Account Rejected');
        //         }else{
        //             return redirect()->back()->with('error', 'No account found to update.');
        //         }
        //     }
        // }
    }


    public function deleteAccounts(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required',
            'email' => 'required|email',
        ]);

        $account = Account::with('user')->where('id', $request->id)->first();

        $settings = settings();
        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $this->api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );

        try {
            $login = $account->code;

            if($account->balance > 0) {
                $balance = abs((float)$account->balance) * -1;
                $comment = 'Withdraw';
                $ticket = NULL;
                $errorCode = $this->api->TradeBalance($login, $typed = MTEnDealAction::DEAL_BALANCE, $balance, $comment, $ticket, $margin_check = true);
                if ($errorCode != MTRetCode::MT_RET_OK) {
                    $error = MTRetCode::GetError($errorCode);
                    return response()->json([
                        'success' => false,
                        'message' => 'Something went wrong',
                        'error' => $error,
                    ], 400);
                } else {
                    try {
                        TradeWithdrawals::create([
                            'email' => $account->user->email,
                            'user_id' => $account->user->id,
                            'account_id' => $account->id,
                            'withdrawal_amount' => $account->balance ,
                            'withdraw_type' => 'Wallet Withdrawal',
                            // 'withdraw_to' => $to_account_id,
                            'wallet_qr' => '',
                            'Status' => 1
                        ]);
                        TotalBalance::create([
                            'account_id' => $account->id,
                            'email' => $account->user->email,
                            'user_id' => $account->user->id,
                            'deposit_amount' => $account->balance ,
                        ]);
                        WalletDeposit::create([
                            'email' => $account->user->email,
                            'user_id' => $account->user->id,
                            'deposit_amount' => $account->balance ,
                            'deposit_type' => 'Internal Transfer',
                            'status' => 1,
                        ]);
                        // RateLimiter::clear($key);
                    } catch (\Exception $e) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Something Went Wrong !!! Please Try Again'
                        ], 400);
                    }
                }
            }


            if (($error_code = $this->api->UserDelete($login)) != MTRetCode::MT_RET_OK) {

                $error = MTRetCode::GetError($error_code);
                Log::error('MT5 live account create error : ' . $error.' for user '.json_encode($login));
                return ["status" => false, "message" => $error];
            } else {
                Log::info('MT5 account deleted successfully'.json_encode($login).' with server response ');
            }

            if ($account) {
                $account->delete(); // Soft delete the account
                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'admin_email' => auth()->guard('admin')->user()->email,
                        'userRole' =>auth()->guard('admin')->user()->userRole,
                        'username' =>auth()->guard('admin')->user()->username,
                        'admin_id' =>auth()->guard('admin')->user()->id,
                        'client_id' => $account->user_id,
                        'account_id' => $account->id,
                        'code' => $account->code,
                        'account_email' => $account->email,
                        'remark' => 'Delete Account'
                    ])
                ->event('delete')
                ->log('Delete Account');
                // Refresh the model to include the `deleted_at` timestamp
                $account->refresh();

                $email = $validatedData['email'];
                $type = $account->demo == "1" ? "Demo account" : "Live account";

                $from = $settings['email_from_address'];
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $emailSubject = $settings['admin_title'] . ' - Account Deleted';
                $content = '<div>We would like to inform you that your account has been deleted.</div>
                            <div> Below are the details for your reference:</div>
                            <br>
                            <div><b>Account code: </b>' . $account->code . '</div>
                            <div><b>Account type: </b>' . $type . '</div>
                            <div><b>Created On: </b>' . $account->created_at . '</div>
                            <div><b>Deleted On: </b>' . $account->deleted_at . '</div>
                            <br>
                            <div>If this action was performed in error or if you have any questions, please don’t hesitate to contact our support team.</div>
                            <br>
                            <div>If you need any assistance, our support team is available 24/7 at support@lqhmarkets.com.</div>
                            <br>
                            <div>Best regards,</div>
                            <div>LQH Markets Team</div>';
                $templateVars = [
                    'name' => $account->name,
                    'site_link' => $settings['copyright_site_name_text'],
                    'email' => $settings['email_from_address'],
                    'content' => $content,
                    'title_right' => 'Account',
                    'subtitle_right' => 'Deleted',
                    'btn_text' => 'Go To Dashboard',
                ];
                $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);

                return redirect()->back()->with('success', 'Account deleted successfully.');
            } else {
                return redirect()->back()->with('error', 'Account not found.');
            }
        } catch (\Exception $e) {
            Log::error('Exception: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            session()->flash('error', 'Exception: ' . $e->getMessage());
        }
    }

    public function createDemoAccount(Request $request)
    {
        $settings = settings();
        $validatedData = $request->validate([
            'options' => 'required|string',
            'leverage' => 'required|string',
            'demo_deposit' => 'required|numeric|min:1',
        ]);

        $user = auth()->user();
        $email = $user->email;

        $group = AccountType::where('id', $validatedData['options'])->firstOrFail();

        if($group->ac_min_deposit){
            $validatedData = $request->validate([
                'options' => 'required|string',
                'leverage' => 'required|string',
                'demo_deposit' => 'required|numeric|min:'.$group->ac_min_deposit,
            ]);

        }

        $userAcc = Account::where('user_id', $user->id)->where('demo',1)->get();
        // dd(count($userAcc));

        if($userAcc)
        {

            $new_user = $this->api->UserCreate();
            $new_user->MainPassword = $this->generatePassword();
            $new_user->Group = $group->ac_group;
            $new_user->type = $group->ac_name;
            $new_user->Leverage = $validatedData['leverage'];
            $new_user->ZipCode = $user->zipcode;
            $new_user->Country = $user->country;
            $new_user->State = $user->state;
            $new_user->City = $user->city;
            $new_user->Address = $user->address;
            $new_user->Phone = $user->number;
            $new_user->Currency = 'USD';
            $new_user->Company = $settings['mt5_company_name'];
            $new_user->Name =  $user->fullname??$user->email;
            $new_user->Email = $user->email;
            $new_user->LeadSource = $user->ib1 ?? "" ;
            $new_user->PhonePassword = $this->generatePassword();
            $new_user->InvestPassword = $this->generatePassword();
            $new_user->Login = $this->generateRandomNumber();
            $response = $this->CreateAccount($new_user, $user_server, 'Demo');

            if ($response['status']) {
                activity()->causedBy($user->id)
                    ->withProperties(
                        [
                            'ip' => $request->ip(),
                            'email' => $user->email,
                            'type' => 'Demo',
                            'code' => $new_user->Login,
                            'amount' => $validatedData['demo_deposit'],
                            'leverage' => $new_user->Leverage,
                            'remark' => 'Create Demo Account'
                        ])
                ->event('create')
                ->log('Create Demo Account');
                $account=Account::create([
                    'user_id' => $user->id,
                    'name' => $new_user->Name,
                    'demo' => true,
                    'email' => $new_user->Email,
                    'code' => $new_user->Login,
                    'account_type_id' => $validatedData['options'],
                    'leverage' => $new_user->Leverage,
                    'currency' => $new_user->Currency,
                    'trader_password' => $new_user->MainPassword,
                    'invester_password' => $new_user->InvestPassword,
                    'phone_password' => $new_user->PhonePassword,
                    'balance' => $validatedData['demo_deposit'],
                    'account_request_status' => 1,
                ]);
                $errorCode = $this->api->TradeBalance($new_user->Login, $type = MTEnDealAction::DEAL_BALANCE, $validatedData['demo_deposit'], 'Deposit', $ticket, $margin_check = true);
                if ($errorCode != MTRetCode::MT_RET_OK) {
                    $error = MTRetCode::GetError($errorCode);
                    Log::error('MT5 demo account : ' . $error.' for user '.$user->id);
                    return redirect()->back()->with('success', $error);
                } else {
                    $data = [
                        'user_id' => $user->id,
                        'account_id'=>$account->id,
                        'email' => $new_user->Email,
                        'code' => $new_user->Login,
                        'deposit_amount' => $validatedData['demo_deposit'],
                        'Status' => 1
                    ];

                    DemoDeposit::create($data);
                }
                $this->sendMail($new_user, 'Demo');
                return redirect()->back()->with('success', $response['message']);
            } else {
                return redirect()->back()->with('error', $response['message']);
            }
        }
        // else{

        //     $account = Account::create([
        //         'user_id' => $user->id,
        //         'name' => $user->fullname??$user->email,
        //         'demo' => true,
        //         'email' => $user->email,
        //         'account_type_id' => $validatedData['options'],
        //         'leverage' => $validatedData['leverage'],
        //         'currency' => 'USD',
        //         'balance' => $validatedData['demo_deposit'],
        //         'account_request_status' => 0,
        //         // 'code' => $new_user->Login,
        //         // 'trader_password' => $new_user->MainPassword,
        //         // 'invester_password' => $new_user->InvestPassword,
        //         // 'phone_password' => $new_user->PhonePassword,
        //     ]);

        //     if($account){

        //         $settings = settings();

        //         $from = $settings['email_from_address'];
        //         $emailSubject = 'Account send for approval';
        //         $headers = "MIME-Version: 1.0" . "\r\n";
        //         $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        //         $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        //         $content =
        //             '<div>Thank you for choosing LQH Markets. Your request for new account will be approve within 2 days.</div>

        //             <p>If you need any assistance, our support team is available 24/7 at support@lqhmarkets.com</p>
        //             <p>Best Regards.</p>
        //         <p>LQH Markets Team</p>';
        //         $templateVars = [
        //             'name' =>  $user->fullname,
        //             'email' => $settings['email_from_address'],
        //             "content" => $content,
        //             "title_right" => "Account Creation Request Pending",
        //             "subtitle_right" => "",
        //         ];

        //         $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);

        //         return redirect()->back()->with('success', 'Account created successfully');

        //     } else {
        //         return redirect()->back()->with('error', 'Account not created');
        //     }
        // }
    }

    public function sendMail($new_user, $type)
    {
        $settings = settings();
        $toEmail = $new_user->Email;
        $from = $settings['email_from_address'];
        $emailSubject = $settings['admin_title'] . ' - ' . $type . ' Account Details';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $templateVars = [
            'name' => $new_user->Name,
            'type' => $type,
            'code' => $new_user->Login,
            'trader_password' => $new_user->MainPassword,
            'investor_password' => $new_user->InvestPassword,
            'leverage' => "1:" . $new_user->Leverage,
            'server_name' => $settings['mt5_company_name'],
            'email' => $settings['email_from_address'],
            "title_right" => "",
            "subtitle_right" => "Your " . $type . " Account is Ready!",
            "acc_type" => $new_user->type
        ];
        $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);

    }
    public function generatePassword($length = 9)
    {
        // Define character pools
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $specialChars = '!@#';
        // Ensure at least one character from each pool is included
        $password = '';
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $specialChars[rand(0, strlen($specialChars) - 1)];
        // Combine all pools for the remaining characters
        $allCharacters = $uppercase . $lowercase . $numbers . $specialChars;
        // Generate the remaining characters
        for ($i = 4; $i < $length; $i++) {
            $password .= $allCharacters[rand(0, strlen($allCharacters) - 1)];
        }
        // Shuffle the password to avoid predictable patterns
        $password = str_shuffle($password);

        return $password;
    }
    function generateRandomNumber($length = 6)
    {
        $min = pow(10, $length - 1); // Minimum value for an 8-digit number (10000000)
        $max = pow(10, $length) - 1;  // Maximum value for an 8-digit number (99999999)
        return rand($min, $max);
    }
    function CreateAccount($user, &$user_server, $type)
    {
        $settings = settings();
        if (!$this->api->IsConnected()) {
            $errorCode = $this->api->Connect(
                $settings['mt5_server_ip'],
                $settings['mt5_server_port'],
                300,
                $settings['mt5_server_web_login'],
                $settings['mt5_server_web_password']
            );
            if ($errorCode != MTRetCode::MT_RET_OK) {
                $error = MTRetCode::GetError($errorCode);
                Log::error('MT5 live account connection error : ' . $error.' for user '.json_encode($user));
                return ["status" => false, "message" => $error];
            }
        }
        if (($error_code = $this->api->UserAdd($user, $user_server)) != MTRetCode::MT_RET_OK) {
            $error = MTRetCode::GetError($error_code);
            Log::error('MT5 live account create error : ' . $error.' for user '.json_encode($user));
            return ["status" => false, "message" => $error];
        } else {
            Log::info('MT5 live account created successfully for user '.json_encode($user).' with server response '.json_encode($user_server));
            return ["status" => true, "message" => $type . " Account Created Successfully"];
        }
    }
    public function changeMt5Password(Request $request,Account $account)
    {
        $request->validate([
            'account_id' => 'required',
            'password_type' => 'required|in:main,investor',
            'password' => 'required|min:6',
        ]);
        $code = $account->code;
        $pass_type = $request->input('password_type');
        $new_password = $request->input('password');
        $type = $request->input('type', 'live');
        if ($pass_type == 'main') {
            $error_code = $this->api->UserPasswordChange($code, $new_password, MTProtocolConsts::WEB_VAL_USER_PASS_MAIN);
        } else {
            $error_code = $this->api->UserPasswordChange($code, $new_password, MTProtocolConsts::WEB_VAL_USER_PASS_INVESTOR);
        }

        // Check if the password change was successful
        if ($error_code != MTRetCode::MT_RET_OK) {
            return redirect()->back()->with('error', 'MT5: ' . MTRetCode::GetError($error_code));
        }

        // Update the password in the database
        $account->update([
            $pass_type == 'main' ? 'trader_password' : 'invester_password' => $new_password
        ]);


        // Display success message
        $message = $pass_type == 'main' ? 'Your Master Password Successfully Updated' : 'Your Investor Password Successfully Updated';
        return redirect()->back()->with('success', $message);
    }

   public function updateNickname(Request $request)
    {
        $request->validate([
            'nickname' => 'required|string|min:3|max:50',
            'account_id' => 'required|exists:accounts,id', // Ensure account_id exists in the database
        ]);

        $user = auth()->user();

        $account = Account::where('user_id', $user->id)
                        ->where('id', $request->account_id)
                        ->first();

        if (!$account) {
            return response()->json(['message' => 'Account not found or unauthorized'], 404);
        }

        $account->account_nick_name = $request->nickname;
        $account->save(); // Save the updated account nickname

        return response()->json(['message' => 'Nickname updated successfully!']);
    }

}
