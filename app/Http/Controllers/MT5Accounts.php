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
use App\Services\MailService as MailService;
use Illuminate\Support\Facades\Log;
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

        dd($request->all());
        $accountTypeId = $request->query('id');

        $leverage = Leverage::where('account_type_id', $accountTypeId)->get();
        return response()->json($leverage);
    }
    public function createLiveAccount(Request $request)
    {
        $settings = settings();
        $validatedData = $request->validate([
            'options' => 'required|string',
            'leverage' => 'required|string',
        ]);
        $user = auth()->user();
        $group = AccountType::where('id', $validatedData['options'])->firstOrFail();
        $referral=$user->referral;
        $ib=$user->ib1;
        //wealthytrades
        if($referral=="wealthytrades" || $ib=="wealthytrades") {
            $groupCode = str_replace("DF","SNSI",$group->ac_group);
            $group = AccountType::where('ac_group', $groupCode)->first();

            if($group){
                $_POST["options"] =$group->id;
            }
            // $sql = "select ac_index from account_types where ac_group = '" . $groupCode . "'";
            // $query = $dbh->prepare($sql);
            // $query->execute();
            // $group = $query->fetchColumn(PDO::FETCH_OBJ);
            // if($group){
            //     $_POST["options"] =$group;
            // }
        }else{
            $groupCode = $group->ac_group;
        }

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
        $new_user->PhonePassword = $this->generatePassword();
        $new_user->InvestPassword = $this->generatePassword();
        $new_user->Login = $this->generateRandomNumber();
        $response = $this->CreateAccount($new_user, $user_server, 'Live');
        if ($response['status']) {
            Account::create([
                'user_id' => $user->id,
                'name' => $new_user->Name,
                'demo'=> false,
                'email' => $new_user->Email,
                'name' => $new_user->Name,
                'code' => $new_user->Login,
                'account_type_id' => $validatedData['options'],
                'leverage' => $new_user->Leverage,
                'currency' => $new_user->Currency,
                'trader_password' => $new_user->MainPassword,
                'invester_password' => $new_user->InvestPassword,
                'phone_password' => $new_user->PhonePassword,
                'ib1' => $new_user->LeadSource,
            ]);
            $this->sendMail($new_user, 'Live');
            return redirect()->back()->with('success', $response['message']);
        } else {
            return redirect()->back()->with('error', $response['message']);
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
        $group = AccountType::where('id', $validatedData['options'])->firstOrFail();
        if($group->ac_min_deposit){
            $validatedData = $request->validate([
                'options' => 'required|string',
                'leverage' => 'required|string',
                'demo_deposit' => 'required|numeric|min:'.$group->ac_min_deposit,
            ]);
        }


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
                'balance' => $validatedData['demo_deposit']
            ]);
            $errorCode = $this->api->TradeBalance($new_user->Login, $type = MTEnDealAction::DEAL_BALANCE, $validatedData['demo_deposit'], 'Deposit', $ticket, $margin_check = true);
            if ($errorCode != MTRetCode::MT_RET_OK) {
                $error = MTRetCode::GetError($errorCode);
                Log::error('MT5 live account : ' . $error.' for user '.$user->id);
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
}
