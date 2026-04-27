<?php

namespace App\Http\Controllers;

use App\Models\Ib1;
use App\Models\User;
use App\Enums\PlatformEnum;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\Leverage;
use App\Models\AccountType;
use App\Models\DemoDeposit;
use App\Models\ToggleGroup;
use App\MT5\MTEnDealAction;
use App\Models\TotalBalance;
use App\Services\UniversalMT5Service;
use App\Services\X9Service;
use Illuminate\Http\Request;
use App\Models\Ib1Commission;
use App\Models\WalletDeposit;
use App\MT5\MTProtocolConsts;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Controllers\Controller;
use App\Services\MailService as MailService;

class MT5Accounts extends Controller
{
    protected $api;
    protected $mailService;
    protected $mt5Service;
    protected $x9Service;
    public function __construct(MailService $mailService, X9Service $x9Service)
    {
        $this->mailService = $mailService;
        $this->x9Service = $x9Service;
        // MT5 service will be initialized on demand to avoid startup hangs
        // MT5 connection deferred - use ensureMT5Connection() in methods that need it
    }

    /**
     * Ensure MT5 connection is established
     */
    private function ensureMT5Connection(): bool
    {
        if (!$this->api) {
            // Initialize MT5 service on demand to avoid startup hangs
            if (!$this->mt5Service) {
                $this->mt5Service = app(UniversalMT5Service::class);
            }

            if (!$this->mt5Service->connect()) {
                Log::error('Failed to connect to MT5 via pool.');
                return false;
            }
            $this->api = $this->mt5Service->getApi();
        }
        return $this->api !== null;
    }
    public function liveAccounts()
    {

        $email = auth()->user()->email;
        $results = Account::with('accountType')
            ->where('user_id', auth()->user()->id)
            ->where('competition_start_date', NULL)
            ->where('competition_end_date', NULL)
            ->where('competition_status', NULL)
            ->where('demo', false)
            ->orderByRaw('CASE WHEN account_request_status = 0 THEN 1 ELSE 0 END, id DESC')
            ->paginate(5);
        return view('live_accounts', compact('results'));
    }

    public function demoAccounts()
    {

        $results = Account::where('user_id', auth()->user()->id)
            ->where('demo', true)
            ->whereHas('accountType', function ($q) {
                $q->whereNull('competition_start_date')
                    ->whereNull('competition_end_date');
            })
            ->with('accountType')
            ->orderBy('id', 'desc')
            ->paginate(5);
        return view('demo_accounts', compact('results'));
    }
    public function viewAccountDetails(Account $account)
    {
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server');
        }

        session()->remove('error');
        $user = auth()->user();
        if ($user->id != $account->user_id) {
            return redirect()->route('liveAccounts')->with('error', 'User Details Not Matching');
        }
        $code = $account->code;
        $type = $account->demo ? 'Demo' : 'Live';
        // $account=Account::where('id',$id)->where('user_id',$user->id)->firstOrFail();
        $settings = settings();
        $results = [];

        $getUser = [];
        $equity = '';
        $margin = '';
        $marginlevel = '';
        $accountSwap = '';
        $freemargin = '';
        $profit = '';

        // Handle different platforms
        if ($account->platform === PlatformEnum::X9->value) {
            return $this->viewX9AccountDetails($account, $code, $type, $settings);
        } else {
            // Default to MT5 handling
            return $this->viewMT5AccountDetails($account, $code, $type, $settings);
        }
    }

    private function viewMT5AccountDetails(Account $account, $code, $type, $settings)
    {
        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $this->api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );

        $results = [];
        $getUser = [];
        $equity = '';
        $balance = '';
        $margin = '';
        $marginlevel = '';
        $accountSwap = '';
        $freemargin = '';
        $profit = '';

        try {
            $login = $code;
            // // Fetch positions
            // if (($error_code = $this->api->PositionGetTotal($login, $total)) != MTRetCode::MT_RET_OK) {
            //     session()->flash('error', 'MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
            // }
            // $open_order_history = $total;
            $offset = 0;
            // $positions = [];
            // // Fetch position pages
            // if (($error_code = $this->api->PositionGetPage($login, $offset, $total, $positions)) != MTRetCode::MT_RET_OK) {
            //     session()->flash('error', 'MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
            // }
            // Fetch user account details
            $mt5account = null;
            if (($error_code = $this->mt5Service->userAccountGet($login, $mt5account)) != MTRetCode::MT_RET_OK) {
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
            // $from = 'March 01,2016';
            // $to = 'March 31,2080';
            // if (($error_code = $this->api->HistoryGetTotal($login, $from, $to, $total)) != MTRetCode::MT_RET_OK) {
            //     session()->flash('error', 'MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
            // }
            // $closed_order_history = $total;
            // // Fetch order pages
            // if (($error_code = $this->api->HistoryGetPage($login, $from, $to, $offset, $total, $orders)) != MTRetCode::MT_RET_OK) {
            //     session()->flash('error', 'MT5 ' . MTRetCode::GetError($error_code));
            // }
            $getUser = Account::with('accountType', 'BonusTransaction')
                ->where('id', $account->id)
                ->first();
            $accountSwap = $getUser->accountType ? $getUser->accountType->ac_swap : null;
            // Process orders
            // dd($orders);
            // if (!empty($orders)) {
            //     foreach ($orders as $item) {
            //         $volume = $item->VolumeInitial * 0.00001;
            //         $time_closed = gmdate("Y-m-d H:i:s", $item->TimeDone);
            //         // Insert commission data into DB
            //         Ib1Commission::updateOrCreate(
            //             [
            //                 'order_id' => $item->Order,
            //                 'code' => $item->Login,
            //             ],
            //             [
            //                 'user_id' => auth()->user()->id,
            //                 'account_id' => $account->id,

            //                 'volume' => $volume,
            //                 'time_closed' => $time_closed
            //             ]
            //         );
            //     }
            // }
        } catch (\Exception $e) {
            Log::error('Exception: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            session()->flash('error', 'Exception: ' . $e->getMessage());

            // Fallback to database values if MT5 API fails
            $balance = $account->balance ?? 0;
            $equity = $account->equity ?? $balance;
        }
        return view('view-account-details', compact('results', 'code', 'type', 'settings', 'account', 'getUser', 'equity', 'balance', 'margin', 'marginlevel', 'accountSwap', 'freemargin', 'profit'));
    }

    private function viewX9AccountDetails(Account $account, $code, $type, $settings)
    {
        $results = [];
        $getUser = [];
        $equity = '';
        $margin = '';
        $marginlevel = '';
        $accountSwap = '';
        $freemargin = '';
        $profit = '';
        $x9GroupName = '';
        try {
            // Get X9 account details
            $response = $this->x9Service->getUserDetails($code);

            if ($response['status']) {
                $x9AccountData = $response['data'];


                // Extract account information from X9 response using correct nested structure
                $balanceData = $x9AccountData['balance'] ?? [];

                if (isset($balanceData['balance'])) {
                    $balanceData['balance'] = str_replace(',', '', $balanceData['balance']);
                }
                if (isset($balanceData['equity'])) {
                    $balanceData['equity'] = str_replace(',', '', $balanceData['equity']);
                }
                if (isset($balanceData['free_margin'])) {
                    $balanceData['free_margin'] = str_replace(',', '', $balanceData['free_margin']);
                }
                $balance = floatval($balanceData['balance'] ?? $account->balance ?? 0);
                $credit = floatval($balanceData['credit'] ?? 0);
                $bonus = floatval($balanceData['bonus'] ?? 0);
                $equity = floatval($balanceData['equity'] ?? ($balance + $credit + $bonus));
                $profit = floatval($balanceData['floating_profit'] ?? 0);
                $margin = floatval($balanceData['margin'] ?? 0);
                $freemargin = floatval($balanceData['free_margin'] ?? ($equity - $margin));
                $marginlevel = $margin > 0 ? round(($equity / $margin) * 100, 2) : 0;

                // Try to update account with fresh data from X9 - be defensive about which fields to update
                try {
                    $updateData = [];
                    if (isset($balance)) $updateData['balance'] = $balance;
                    if (isset($credit)) $updateData['credit'] = $credit;
                    if (isset($equity)) $updateData['equity'] = $equity;
                    if (isset($freemargin)) $updateData['free_margin'] = $freemargin;

                    // Calculate and add margin level if we have the necessary data
                    if (isset($margin) && isset($equity) && $margin > 0) {
                        $updateData['margin_level'] = round(($equity / $margin) * 100, 2);
                    }

                    if (!empty($updateData)) {
                        $account->update($updateData);
                    }
                } catch (\Exception $updateError) {
                    // Log the update error but continue with display
                    Log::warning('Failed to update X9 account in database: ' . $updateError->getMessage());
                }
            } else {
                // If X9 API fails, use database values
                $balance = $account->balance ?? 0;
                $credit = $account->credit ?? 0;
                $equity = $account->equity ?? $balance + $credit;
                $margin = $account->margin ?? 0;
                $freemargin = $account->free_margin ?? $equity - $margin;
                $profit = 0;
                $marginlevel = $account->margin_level ?? 0;

                // session()->flash('warning', 'Unable to fetch live data from X9 server. Showing last known values.');
            }

            $getUser = Account::with('accountType', 'BonusTransaction')
                ->where('id', $account->id)
                ->first();
            $accountSwap = $getUser->accountType ? $getUser->accountType->ac_swap : null;
            $x9GroupName = $this->x9Service->getClientGroupName($account->accountType->x9_group_id ?? 1);
        } catch (\Exception $e) {
            Log::error('X9 Account Details Exception: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            session()->flash('error', 'Unable to fetch account details from X9: ' . $e->getMessage());

            // Fallback to database values
            $balance = $account->balance ?? 0;
            $credit = $account->credit ?? 0;
            $equity = $account->equity ?? $balance + $credit;
            $margin = $account->margin ?? 0;
            $freemargin = $account->free_margin ?? $equity - $margin;
            $profit = 0;
            $marginlevel = $account->margin_level ?? 0;

            $getUser = Account::with('accountType', 'BonusTransaction')
                ->where('id', $account->id)
                ->first();
            $accountSwap = $getUser->accountType ? $getUser->accountType->ac_swap : null;

            // Get X9 group name for display
            $x9GroupName = $this->x9Service->getClientGroupName($account->accountType->x9_group_id ?? 1);
        }
        return view('view-account-details', compact('results', 'code', 'type', 'settings', 'account', 'getUser', 'equity', 'balance', 'margin', 'marginlevel', 'accountSwap', 'freemargin', 'profit', 'x9GroupName'));
    }

    public function showLiveAccountForm()
    {
        $user = auth()->user();
        $email  = $user->email;
        $results = AccountType::whereHas('mt5Group', function ($query) {
            $query->where('mt5_group_type', 'live')
                ->orWhere('mt5_group_type', 'real');
        })->where('is_client_group', 1)
            // ->where('competition_start_date',NULL)
            // ->where('competition_end_date',NULL)
            ->orderBy('display_priority', 'DESC')
            ->with('mt5Group:mt5_group_id,mt5_group_type')
            ->get();

        return view('create-live-account', compact('user', 'results'));
    }
    public function showDemoAccountForm()
    {
        $user = auth()->user();
        $email  = $user->email;
        // $user = User::where('email', $email)->first();
        $results = AccountType::with('mt5Group')
            ->whereHas('mt5Group', function ($query) {
                $query->where('mt5_group_type', 'demo');
            })
            ->where('is_client_group', 1)
            ->where('competition_start_date', NULL)
            ->where('competition_end_date', NULL)
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
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server');
        }

        // dump($user);
        $login = $request->accountId;
        $account_code = Account::where('id', $login)->value('code');
        $accountTypeId = $request->modalAccountId;
        $newLeverage = $request->leverage;
        $comment = $request->update_leverage;
        $updated_user = "";
        $trade_user = null;
        $total_positions = null;

        if (($error_code = $this->mt5Service->userGet($account_code, $trade_user)) != MTRetCode::MT_RET_OK) {
            return redirect()->back()->with('error', 'Something went wrong on Updating details' . MTRetCode::GetError($error_code));
        }
        if (($error_code = $this->mt5Service->positionGetTotal($account_code, $total_positions)) != MTRetCode::MT_RET_OK) {
            return redirect()->back()->with('error', 'Something went wrong on Updating details' . MTRetCode::GetError($error_code));
        }

        if ($total_positions == 0) {
            $trade_user->Leverage = $newLeverage;

            $error_code = $this->mt5Service->userUpdate($trade_user, $updated_user);
            if ($error_code != MTRetCode::MT_RET_OK) {
                return redirect()->back()->with("error", "Something went wrong on Updating details" . MTRetCode::GetError($error_code));
            } else {
                Account::where('id', $login)->update([
                    'leverage' => $newLeverage
                ]);
            }

            return redirect()->back()->with('success', 'Leverage updated successfully!');
        } else {
            return redirect()->back()->with('warning', 'Trades need to be closed!');
        }
    }
    public function createLiveAccount(Request $request)
    {
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server');
        }

        $settings = settings();
        $validatedData = $request->validate([
            'options' => 'required|string',
            'leverage' => 'required|string',
        ]);

        $user = auth()->user();
        $nick_name = $request->nick_name;

        $email = $user->email;
        $group = AccountType::with('mt5Group')->where('id', $validatedData['options'])->firstOrFail();
        if (!$group->mt5Group) {
            return redirect()->back()->with('error', 'MT5 Group not found for the selected account type.');
        }
        if ($group->mt5Group->mt5_group_type != 'live' && $group->mt5Group->mt5_group_type != 'real') {
            return redirect()->back()->with('error', 'Selected account type is not valid for live accounts.');
        }
        $referral = $user->referral;
        $ib = $user->ib1;
        $account_type_id = $validatedData['options'];
        // dd('ssss');

        //wealthytrades
        if (($referral == "wealthytrades" || $ib == "wealthytrades") && $group->ac_group != 'LM\B-Book\10x\DF-B') {
            $groupCode = str_replace("DF", "SNSI", $group->ac_group);
            $group = AccountType::where('ac_group', $groupCode)->first();

            if ($group) {
                $_POST["options"] = $group->id;
                $account_type_id = $group->id;
            }
        } elseif ((strtolower($referral) == "swingtradinglab" || strtolower($ib) == "swingtradinglab") && $group->ac_group != 'LM\B-Book\10x\DF-B') {
            $groupCode = str_replace("DF", "ALEX", $group->ac_group);
            $group = AccountType::where('ac_group', $groupCode)->first();
            if ($group) {
                $_POST["options"] = $group->id;
                $account_type_id = $group->id;
            }
        } else {
            $groupCode = $group->ac_group;
        }

        $toggleGroup = ToggleGroup::get()->first();

        if ($toggleGroup->a_book == 1) {
            if (str_contains($groupCode, 'B-Book')) {
                $groupCode = str_replace('B-Book', 'A-Book', $groupCode);
                $groupCode = preg_replace('/-B($|\\\)/', '-A$1', $groupCode);
            }
        } elseif ($toggleGroup->b_book == 1) {
            if (str_contains($groupCode, 'A-Book')) {
                $groupCode = str_replace('A-Book', 'B-Book', $groupCode);
                $groupCode = preg_replace('/-A($|\\\)/', '-B$1', $groupCode);
            }
        }

        $group = AccountType::where('ac_group', $groupCode)->first();

        if ($email == 'juanpipkin@gmail.com') {
            $groupCode = 'LM\B-Book\PRO\LeverageTest';
            $group = AccountType::where('ac_group', $groupCode)->first();
            $account_type_id = $group->id;
        }



        $userAcc = Account::where('user_id', $user->id)->where('demo', 0)->get();

        $ibdata = '';
        if ($ib) {
            $ibdata = Ib1::where('referral_code', $ib)->first();
        }

        if (count($userAcc) < 2) {
            $new_user = $this->mt5Service->userCreate();
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
            $new_user->Name = $user->fullname ?? $user->email;
            $new_user->Email = $user->email;
            $new_user->LeadSource = $user->ib1 ?? "";
            $new_user->Agent = $ibdata->indexId ?? "";
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
                            'remark' => 'Create Live Account',
                            'request_data' => $request->all()
                        ]
                    )
                    ->event('create')
                    ->log('Create Live Account');
                $account = Account::create([
                    'user_id' => $user->id,
                    'name' => $new_user->Name,
                    'demo' => false,
                    'platform' => Account::PLATFORM_MT5,
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
                $this->sendMail($new_user, 'Live', $account->platform);
                // return redirect()->back()->with('success', $response['message']);
                return redirect()->back()->with('success', $response['message']);
            } else {
                return redirect()->back()->with('error', $response['message']);
            }
        } else {
            activity()->causedBy($user->id)
                ->withProperties(
                    [
                        'ip' => $request->ip(),
                        'email' => $user->email,
                        'type' => 'Live',
                        'code' => 'Pending',
                        'leverage' => $validatedData['leverage'],
                        'ib' => $ib,
                        'remark' => 'Create Live Account',
                        'request_data' => $request->all()
                    ]
                )
                ->event('create')
                ->log('Create Live Account');
            $useraccount = Account::create([
                'user_id' => $user->id,
                'name' => $user->fullname ?? $user->email,
                'demo' => false,
                'platform' => Account::PLATFORM_MT5,
                'email' => $user->email,
                'account_nick_name' =>  $nick_name,
                'account_type_id' => $account_type_id,
                'leverage' => $validatedData['leverage'],
                'currency' => 'USD',
                'ib1' => $user->ib1 ?? "",
                'account_request_status' => '0',

            ]);
            if ($useraccount) {

                $from = $settings['email_from_address'];
                $emailSubject = 'Trading Account Requested';
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $content =
                    '<div>Thank you for choosing LQH Markets. Your request for a new trading account will be approved  within 24-48 hours.</div>

                    <p>If you need any assistance, our support team is available 24/7 at <span style="color: #00b98e;">support@lqhmarkets.com</span></p>
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
    }
    public function activateAccount(Request $request)
    {
        // Ensure MT5 connection before proceeding
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server. Please try again.');
        }

        $settings = settings();
        if ($request->accountType == 0) {
            $validatedData = $request->validate([
                'options' => 'required|string',
                'leverage' => 'required|string',
            ]);

            // $user = auth()->user();
            $user = User::where('id', $request->client_id)->first();

            $group = AccountType::where('id', $validatedData['options'])->firstOrFail();

            $referral = $user->referral;
            $ib = $user->ib1;
            $account_type_id = $validatedData['options'];

            //wealthytrades
            if ($group->ac_group != 'LM\B-Book\10x\DF-B') {
                if ($referral == "wealthytrades" || $ib == "wealthytrades") {
                    $groupCode = str_replace("DF", "SNSI", $group->ac_group);
                    $group = AccountType::where('ac_group', $groupCode)->first();

                    if ($group) {
                        $_POST["options"] = $group->id;
                        $account_type_id = $group->id;
                    }
                } elseif (strtolower($referral) == "swingtradinglab" || strtolower($ib) == "swingtradinglab") {
                    $groupCode = str_replace("DF", "ALEX", $group->ac_group);
                    $group = AccountType::where('ac_group', $groupCode)->first();
                    if ($group) {
                        $_POST["options"] = $group->id;
                        $account_type_id = $group->id;
                    }
                }
            } else {
                $groupCode = $group->ac_group;
            }
            if ($user->email == 'juanpipkin@gmail.com') {
                $groupCode = 'LM\B-Book\PRO\LeverageTest';
                $group = AccountType::where('ac_group', $groupCode)->first();
                $account_type_id = $group->id;
            }
            $ibdata = '';
            if ($ib) {
                $ibdata = Ib1::where('referral_code', $ib)->first();
            }
            if ($request->request_status == 1) {
                $new_user = $this->mt5Service->userCreate();
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
                $new_user->Name = $user->fullname ?? $user->email;
                $new_user->Email = $user->email;
                $new_user->LeadSource = $user->ib1 ?? "";
                $new_user->Agent = $ibdata->indexId ?? "";
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
                            ]
                        )
                        ->event('create')
                        ->log('Create Live Account');
                    if ($account) {
                        $account->update([
                            'user_id' => $user->id,
                            'name' => $new_user->Name,
                            'demo' => false,
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
                        $this->sendMail($new_user, 'Live', $account->platform);
                        return redirect()->back()->with('success', $response['message']);
                    } else {
                        return redirect()->back()->with('error', 'No account found to update.');
                    }
                } else {
                    return redirect()->back()->with('error', $response['message']);
                }
            } elseif ($request->request_status == 2) {
                $account = Account::where('id', $request->account_id)->first();
                // dd($account);
                if ($account) {
                    $account->update([
                        'user_id' => $user->id,
                        'name' => $user->fullname ?? $user->email,
                        'demo' => false,
                        'email' => $user->email,
                        'account_type_id' => $account_type_id,
                        'leverage' => $validatedData['leverage'],
                        'currency' => 'USD',
                        'ib1' => $user->ib1 ?? "",
                        'code' => 'Rejected',
                        'account_request_status' => 0,
                    ]);
                    return redirect()->back()->with('success', 'Account Rejected');
                } else {
                    return redirect()->back()->with('error', 'No account found to update.');
                }
            }
        }
    }

    public function bulkActivateAccount(Request $request)
    {
        // Ensure MT5 connection before proceeding
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server. Please try again.');
        }

        $settings = settings();
        $validatedData = $request->validate([
            'client_id' => 'required|string',
            'request_status' => 'required|string',
        ]);

        $accountIds = explode(',', $request->client_id);
        $successCount = 0;
        $failCount = 0;

        foreach ($accountIds as $accountId) {
            $account = Account::where('id', $accountId)->first();

            if (!$account) {
                $failCount++;
                continue;
            }

            $user = User::where('id', $account->user_id)->first();
            $group = AccountType::where('id', $account->account_type_id)->firstOrFail();

            $referral = $user->referral;
            $ib = $user->ib1;
            $account_type_id = $account->account_type_id;

            if ($group->ac_group != 'LM\B-Book\10x\DF-B') {
                if ($referral == "wealthytrades" || $ib == "wealthytrades") {
                    $groupCode = str_replace("DF", "SNSI", $group->ac_group);
                    $group = AccountType::where('ac_group', $groupCode)->first();
                    if ($group) {
                        $_POST["options"] = $group->id;
                        $account_type_id = $group->id;
                    }
                } elseif (strtolower($referral) == "swingtradinglab" || strtolower($ib) == "swingtradinglab") {
                    $groupCode = str_replace("DF", "ALEX", $group->ac_group);
                    $group = AccountType::where('ac_group', $groupCode)->first();
                    if ($group) {
                        $_POST["options"] = $group->id;
                        $account_type_id = $group->id;
                    }
                }
            }

            if ($user->email == 'juanpipkin@gmail.com') {
                $groupCode = 'LM\B-Book\PRO\LeverageTest';
                $group = AccountType::where('ac_group', $groupCode)->first();
            }

            $ibdata = '';
            if ($ib) {
                $ibdata = Ib1::where('referral_code', $ib)->first();
            }
            if ($request->request_status == 1) {
                $new_user = $this->mt5Service->userCreate();
                $new_user->MainPassword = $this->generatePassword();
                $new_user->Group = $group->ac_group;
                $new_user->type = $group->ac_name;
                $new_user->Leverage = $account->leverage;
                $new_user->ZipCode = $user->zipcode;
                $new_user->Country = $user->country;
                $new_user->State = $user->state;
                $new_user->City = $user->city;
                $new_user->Address = $user->address;
                $new_user->Phone = $user->number;
                $new_user->Currency = 'USD';
                $new_user->Company = $settings['mt5_company_name'];
                $new_user->Name = $user->fullname ?? $user->email;
                $new_user->Email = $user->email;
                $new_user->LeadSource = $user->ib1 ?? "";
                $new_user->Agent = $ibdata->indexId ?? "";
                $new_user->PhonePassword = $this->generatePassword();
                $new_user->InvestPassword = $this->generatePassword();
                $new_user->Login = $this->generateRandomNumber();

                $response = $this->CreateAccount($new_user, $user_server, 'Live');

                if ($response['status']) {
                    $account = Account::where('id', $account->id)->first();

                    activity()->causedBy(auth()->user()->id)
                        ->withProperties([
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

                    if ($account) {
                        $account->update([
                            'user_id' => $user->id,
                            'name' => $new_user->Name,
                            'demo' => false,
                            'email' => $new_user->Email,
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

                        $this->sendMail($new_user, 'Live', $account->platform);
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                } else {
                    return redirect()->back()->with('error', $response['message']);
                }
            } elseif ($request->request_status == 2) {
                $account->update([
                    'user_id' => $user->id,
                    'name' => $user->fullname ?? $user->email,
                    'demo' => false,
                    'email' => $user->email,
                    'account_type_id' => $account_type_id,
                    'leverage' => $account->leverage,
                    'currency' => 'USD',
                    'ib1' => $user->ib1 ?? "",
                    'code' => 'Rejected',
                    'account_request_status' => 0,
                ]);
                $successCount++;
            }
        }

        if ($request->request_status == 1) {
            return redirect()->back()->with('success', "$successCount account(s) activated successfully. $failCount failed.");
        } elseif ($request->request_status == 2) {
            return redirect()->back()->with('success', "$successCount account(s) rejected successfully.");
        }

        return redirect()->back()->with('info', 'No action taken.');
    }



    public function deleteAccounts(Request $request)
    {
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server');
        }

        $validatedData = $request->validate([
            'id' => 'required',
            'email' => 'required|email',
        ]);

        $account = Account::with('user')->where('id', $request->id)->first();

        $settings = settings();

        $platform = $account->platform;

        try {
            $login = (int)$account->code;

            if ($platform === PlatformEnum::X9->value) {
                $response = $this->x9Service->getUserDetails($login);
                if ($response['data']['trading_account']['trading_account_balance']['balance'] > 0) {
                    if ($response['data']['trading_account']['client_group_type'] != 'DEMO') {
                        return redirect()->back()->with('error', 'Account has balance, please transfer amount to another account.');
                    }
                }
                // X9 deletion logic
                $response = $this->x9Service->disableAccount($account);
                if (!$response['status']) {
                    return redirect()->back()->with('error', 'X9 Account Deletion Failed: ' . $response['message']);
                }
                // Delete from local database
                $account->delete();
            } elseif ($platform === PlatformEnum::MT5->value) {

                $trade_user = null;
                if (($error_code = $this->api->UserGet($login, $trade_user) != MTRetCode::MT_RET_OK)) {
                    return redirect()->back()->with('error', 'MT5 Account Deletion Failed: ' . MTRetCode::GetError($error_code));
                }

                if ($trade_user->Balance > 0) {
                    if ($account->demo != 1) {
                        return redirect()->back()->with('error', 'Account has balance, please transfer amount to another account.');
                    }
                }

                // Close all trades and disable trading before deletion
                $error_code = $this->api->DisableTradingOrDeleteUser($login);
                if (!$error_code['status']) {
                    return redirect()->back()->with('error', 'MT5 Account Deletion Failed during cleanup: ' . $error_code['message']);
                }

                // MT5 deletion logic
                // if (($error_code = $this->api->UserDelete($login)) != MTRetCode::MT_RET_OK) {
                //     return redirect()->back()->with('error', 'MT5 Account Deletion Failed: ' . MTRetCode::GetError($error_code));
                // }
                // Delete from local database
                $account->delete();
            }

            activity()
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'admin_email' => auth()->guard('admin')->user()->email,
                    'userRole' => auth()->guard('admin')->user()->userRole,
                    'username' => auth()->guard('admin')->user()->username,
                    'admin_id' => auth()->guard('admin')->user()->id,
                    'client_id' => $account->user_id,
                    'account_id' => $account->id,
                    'code' => $account->code,
                    'account_email' => $account->email,
                    'remark' => 'Delete Account'
                ])
                ->event('delete')
                ->log('Delete Account');

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

            return redirect()->route('admin.dashboard')->with('success', 'MT5 Account Deleted Successfully');
        } catch (\Exception $e) {
            Log::error('Exception: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            session()->flash('error', 'Exception: ' . $e->getMessage());
        }
    }

    public function createDemoAccount(Request $request)
    {
        $this->ensureMT5Connection();

        $settings = settings();

        // Rate limiting to prevent duplicate account creation
        $key = 'create-demo-account:' . (auth()->id() ?: $request->ip());

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retryAfter = RateLimiter::availableIn($key);
            return redirect()->back()
                ->with('error', "Please wait {$retryAfter} seconds before creating another demo account.");
        }

        RateLimiter::hit($key, 10); // Lock for 10 seconds

        // Validate platform selection
        $request->validate([
            'platform' => 'required|in:' . implode(',', PlatformEnum::all()),
        ]);

        $platform = $request->input('platform');
        if ($platform === PlatformEnum::MT5->value) {
            return $this->createMT5DemoAccount($request);
        } else {
            return $this->createX9DemoAccount($request);
        }
    }

    private function createMT5DemoAccount(Request $request)
    {
        // Additional rate limiting specific to MT5 demo account creation
        $key = 'create-mt5-demo-account:' . (auth()->id() ?: $request->ip());

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retryAfter = RateLimiter::availableIn($key);
            return redirect()->back()
                ->with('error', "Please wait {$retryAfter} seconds before creating another MT5 demo account.");
        }

        RateLimiter::hit($key, 10); // Lock for 10 seconds

        $settings = settings();
        $validatedData = $request->validate([
            'options' => 'required|string',
            'leverage' => 'required|string',
            'demo_deposit' => 'required|numeric|min:1',
        ]);

        $user = auth()->user();
        $email = $user->email;

        $group = AccountType::where('id', $validatedData['options'])->firstOrFail();

        if ($group->ac_min_deposit) {
            $validatedData = $request->validate([
                'options' => 'required|string',
                'leverage' => 'required|string',
                'demo_deposit' => 'required|numeric|min:' . $group->ac_min_deposit,
            ]);
        }

        $userAcc = Account::where('user_id', $user->id)->where('demo', 1)->get();

        if ($userAcc) {
            $new_user = $this->mt5Service->userCreate();
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
            $new_user->Name =  $user->fullname ?? $user->email;
            $new_user->Email = $user->email;
            $new_user->LeadSource = $user->ib1 ?? "";
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
                            'platform' => PlatformEnum::MT5->displayName(),
                            'code' => $new_user->Login,
                            'amount' => $validatedData['demo_deposit'],
                            'leverage' => $new_user->Leverage,
                            'remark' => 'Create Demo Account'
                        ]
                    )
                    ->event('create')
                    ->log('Create Demo Account');

                $account = Account::create([
                    'user_id' => $user->id,
                    'name' => $new_user->Name,
                    'demo' => true,
                    'platform' => Account::PLATFORM_MT5,
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
                $ticket = null;
                $errorCode = $this->mt5Service->tradeBalance($new_user->Login, $type = MTEnDealAction::DEAL_BALANCE, $validatedData['demo_deposit'], 'Deposit', $ticket, $margin_check = true);
                if ($errorCode != MTRetCode::MT_RET_OK) {
                    $error = MTRetCode::GetError($errorCode);
                    Log::error('MT5 demo account : ' . $error . ' for user ' . $user->id);
                    return redirect()->back()->with('success', $error);
                } else {
                    $data = [
                        'user_id' => $user->id,
                        'account_id' => $account->id,
                        'email' => $new_user->Email,
                        'code' => $new_user->Login,
                        'deposit_amount' => $validatedData['demo_deposit'],
                        'Status' => 1
                    ];

                    DemoDeposit::create($data);
                }
                $this->sendMail($new_user, 'Demo', $account->platform);
                return redirect()->back()->with('success', $response['message']);
            } else {
                return redirect()->back()->with('error', $response['message']);
            }
        }
    }

    private function createX9DemoAccount(Request $request)
    {
        // Additional rate limiting specific to X9 demo account creation
        $key = 'create-x9-demo-account:' . (auth()->id() ?: $request->ip());

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retryAfter = RateLimiter::availableIn($key);
            return redirect()->back()
                ->with('error', "Please wait {$retryAfter} seconds before creating another X9 demo account.");
        }

        RateLimiter::hit($key, 10); // Lock for 10 seconds

        $validatedData = $request->validate([
            'x9_options' => 'required|string',
            'leverage' => 'required|string',
            'demo_deposit' => 'required|numeric|min:1',
        ]);

        $user = auth()->user();

        // Get the account type for X9 group ID
        $accountType = AccountType::where('id', $validatedData['x9_options'])->first();

        if (!$accountType) {
            return redirect()->back()->with('error', 'Invalid account type selected');
        }

        // Use the x9_group_id from account_types table, fallback to 1 if not set
        $x9GroupId = $accountType->x9_group_id ?? 1;

        // Generate random passwords for X9
        $masterPassword = $this->generatePassword();
        $investorPassword = $this->generatePassword();

        // Prepare X9 user data
        $fullname = $user->fullname ?? $user->email;
        $nameParts = explode(' ', $fullname, 2);
        $firstName = $nameParts[0] ?? 'User';
        $lastName = $nameParts[1] ?? 'Demo';

        $x9UserData = [
            'preferred_login' => 'default',
            'client_id' => null,
            'client_group_type_id' => 1, // Always 1 for Demo account
            'client_group_id' => $x9GroupId, // Use x9_group_id from account_types table
            'first_name' => $firstName,
            'middle_name' => null,
            'last_name' => $lastName,
            'company' => null,
            'email' => $user->email,
            'phone' => $user->number ?? null,
            'master_password' => $masterPassword,
            'investor_password' => $investorPassword,
            'country_id' => 5 // Default country ID
        ];
        
        // Create user in X9
        $response = $this->x9Service->createUser($x9UserData);

        if ($response['status']) {
            $x9AccountData = $response['data']['trading_account'] ?? $response['data'];
            $loginId = $x9AccountData['account_number'] ?? null;
            $tradingAccountId = $x9AccountData['trading_account_id'] ?? null;

            if (!$loginId) {
                return redirect()->back()->with('error', 'Failed to create X9 account: No account number returned');
            }

            // Log activity
            activity()->causedBy($user->id)
                ->withProperties([
                    'ip' => $request->ip(),
                    'email' => $user->email,
                    'type' => 'Demo',
                    'platform' => PlatformEnum::X9->displayName(),
                    'code' => $loginId,
                    'amount' => $validatedData['demo_deposit'],
                    'leverage' => $validatedData['leverage'],
                    'remark' => 'Create X9 Demo Account'
                ])
                ->event('create')
                ->log('Create X9 Demo Account');

            // Create account record in our database
            $account = Account::create([
                'user_id' => $user->id,
                'name' => $user->fullname ?? $user->email,
                'demo' => true,
                'platform' => Account::PLATFORM_X9,
                'email' => $user->email,
                'code' => $loginId,
                'account_type_id' => $validatedData['x9_options'], // Use selected account type like MT5
                'leverage' => $validatedData['leverage'],
                'currency' => 'USD',
                'trader_password' => $masterPassword,
                'invester_password' => $investorPassword,
                'phone_password' => null,
                'balance' => $validatedData['demo_deposit'],
                'equity' => $validatedData['demo_deposit'],
                'account_request_status' => 1,
            ]);

            // Deposit demo balance in X9
            $balanceResponse = $this->x9Service->manageBalance(
                $loginId,
                'balance',
                'Deposit',
                $validatedData['demo_deposit'],
                'Demo Account Initial Deposit'
            );
            $new_user = json_decode(json_encode([
                "Email" => $user->email,
                "Name" => $user->fullname,
                "Login" => $loginId,
                "MainPassword" => $masterPassword,
                "InvestPassword" => $investorPassword,
                "Leverage" => $validatedData['leverage'],
                "type" => $accountType->ac_name,
            ]));
            $this->sendMail($new_user, 'Demo', PlatformEnum::X9->displayName());
            if ($balanceResponse['status']) {
                // Create demo deposit record
                $data = [
                    'user_id' => $user->id,
                    'account_id' => $account->id,
                    'email' => $user->email,
                    'code' => $loginId,
                    'deposit_amount' => $validatedData['demo_deposit'],
                    'Status' => 1
                ];

                DemoDeposit::create($data);

                return redirect()->back()->with('success', 'X9 Demo account created successfully! Login ID: ' . $loginId);
            } else {
                // Account created but deposit failed
                Log::error('X9 Demo Balance Deposit Failed: ' . $balanceResponse['message'] . ' for user ' . $user->id);
                return redirect()->back()->with('warning', 'X9 Demo account created but initial deposit failed. Please contact support.');
            }
        } else {
            Log::error('X9 Demo Account Creation Failed: ' . $response['message'] . ' for user ' . $user->id);
            return redirect()->back()->with('error', 'Failed to create X9 demo account.Please try again later.');
        }
    }

    public function sendMail($new_user, $type, $platform)
    {

        $settings = settings();
        $toEmail = $new_user->Email;
        $from = $settings['email_from_address'];
        $emailSubject = $settings['admin_title'] . ' - ' . $type . ' Account Details';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content = '
                        <p>Your ' . strtoupper($platform) . ' account is ready! You are all set to dive into the exciting world of trading.</p>
                        <p>Here are your ' . strtoupper($platform) . ' account details</p>
                    ';
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
            "acc_type" => $new_user->type,
            "content" => $content,
            "platform" => $platform,
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
        if (!$this->ensureMT5Connection()) {
            return ["status" => false, "message" => "Failed to connect to MT5 server"];
        }

        if ($user->Country == "United Arab Emirates" || $user->Country == "UAE") {
            $user->Country = "India";
        }
        if (($error_code = $this->mt5Service->userAdd($user, $user_server)) != MTRetCode::MT_RET_OK) {
            $error = MTRetCode::GetError($error_code);
            Log::error('MT5 live account create error : ' . $error . ' for user ' . json_encode($user));
            return ["status" => false, "message" => $error];
        } else {
            // Log::info('MT5 live account created successfully for user '.json_encode($user).' with server response '.json_encode($user_server));
            return ["status" => true, "message" => $type . " Account Created Successfully"];
        }
    }
    public function changeMt5Password(Request $request, Account $account)
    {
        // Ensure MT5 connection before proceeding
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server. Please try again.');
        }

        $request->validate([
            'account_id' => 'required',
            'password_type' => 'required|in:main,investor',
            'password' => 'required|min:6',
        ]);

        $code = $account->code;
        $pass_type = $request->input('password_type');
        $new_password = $request->input('password');
        $type = $request->input('type', 'live');

        // Handle password update based on platform
        if ($account->platform === PlatformEnum::X9->value) {
            return $this->updateX9Password($account, $code, $pass_type, $new_password);
        } else {
            return $this->updateMT5Password($account, $code, $pass_type, $new_password);
        }
    }

    private function updateX9Password($account, $code, $pass_type, $new_password)
    {
        try {
            // Map password types for X9 API
            $x9PasswordType = $pass_type === 'main' ? 'master' : $pass_type;

            // Update password in X9
            $response = $this->x9Service->resetUserPassword(intval($code), $x9PasswordType, $new_password);

            if (!$response['status']) {
                return redirect()->back()->with('error', 'Failed to update password in X9: ' . $response['message']);
            }

            // Update in local database
            if ($pass_type === 'main') {
                $account->trader_password = $new_password;
            } elseif ($pass_type === 'investor') {
                $account->invester_password = $new_password;
            }
            $account->save();

            // Log activity
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_email' => Auth::user()->email,
                    'username' => Auth::user()->username,
                    'user_id' => Auth::user()->id,
                    'code' => $code,
                    'new_password' => $new_password,
                    'platform' => PlatformEnum::X9->value,
                    'password_type' => $x9PasswordType,
                    'remark' => 'Update ' . ucfirst($pass_type) . ' Password (X9)'
                ])
                ->event('update')
                ->log('Update ' . ucfirst($pass_type) . ' Password (X9)');

            $passwordTypeName = $pass_type === 'main' ? 'Master' : ucfirst($pass_type);
            return redirect()->back()->with('success', "Your {$passwordTypeName} Password Successfully Updated");
        } catch (\Exception $e) {
            Log::error('X9 Password Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating X9 password: ' . $e->getMessage());
        }
    }

    private function updateMT5Password($account, $code, $pass_type, $new_password)
    {
        if ($pass_type == 'main') {
            $error_code = $this->mt5Service->userPasswordChange($code, $new_password, MTProtocolConsts::WEB_VAL_USER_PASS_MAIN);
        } else {
            $error_code = $this->mt5Service->userPasswordChange($code, $new_password, MTProtocolConsts::WEB_VAL_USER_PASS_INVESTOR);
        }

        // Check if the password change was successful
        if ($error_code != MTRetCode::MT_RET_OK) {
            return redirect()->back()->with('error', 'MT5: ' . MTRetCode::GetError($error_code));
        }

        // Update the password in the database
        $account->update([
            $pass_type == 'main' ? 'trader_password' : 'invester_password' => $new_password
        ]);

        // Log activity
        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'ip' => request()->ip(),
                'user_email' => Auth::user()->email,
                'username' => Auth::user()->username,
                'user_id' => Auth::user()->id,
                'code' => $code,
                'new_password' => $new_password,
                'platform' => PlatformEnum::MT5->value,
                'password_type' => $pass_type,
                'remark' => 'Update ' . ucfirst($pass_type) . ' Password (MT5)'
            ])
            ->event('update')
            ->log('Update ' . ucfirst($pass_type) . ' Password (MT5)');

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

        $user = Auth::user();

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

    public function resendCredentials(Request $request)
    {
        $request->validate([
            'code' => 'required|exists:accounts,code'
        ]);
        $account = Account::where('code', $request->code)
            ->with('user')
            ->firstOrFail();

        // Determine Master Password
        $masterPassword = $account->trader_password;
        $platform = $account->platform ?? config('platforms.default');

        // Send notification email
        try {
            $settings = settings();
            $toEmail = $account->user->email;
            $from = $settings['email_from_address'];
            $emailSubject = $settings['admin_title'] . ' - Account Details';
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
            $content = '
                            <p>You have requested your trading account credentials.</p>
                            <p>Below are your accounts details for full access to your trading account.</p>
                        ';
            $templateVars = [
                'account' => $account,
                'server_name' => $settings['mt5_company_name'],
                'email' => $settings['email_from_address'],
                // "title_right" => "",
                // "subtitle_right" => "Your " . $type . " Account is Ready!",
                "content" => $content,
                "platform" => $platform,
            ];
            $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);

            return response()->json([
                'success' => true,
                'message' => 'Credentials sent successfully to ' . $account->user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to resend credentials for account ' . $account->code . ': ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please try again later.'
            ], 500);
        }
    }
}
