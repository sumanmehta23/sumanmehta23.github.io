<?php

namespace App\Http\Controllers\admin;

use App\Models\Ib1;
use App\Models\User;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\Leverage;
use App\Models\AccountType;
use App\Models\DemoDeposit;
use App\MT5\MTEnDealAction;
use App\Models\TotalBalance;
use App\Services\MT5Service;
use Illuminate\Http\Request;
use App\Models\Ib1Commission;
use App\Models\WalletDeposit;
use App\MT5\MTProtocolConsts;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\MailService as MailService;


class Leaderboard extends Controller
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

    public function competiton_dashboard()
    {
        return view('admin.leaderboard');
    }

    public function requested_competition()
    {
        return view('admin.requested_competition');
    }

    public function activateCompetition(Request $request)
    {

        $settings = settings();
        if($request->accountType == 1)
        {
            $validatedData = $request->validate([
                'options' => 'required|string',
                'leverage' => 'required|string',
                'demo_deposit' => 'required|numeric|min:1',
            ]);
            // $user = auth()->user();
            $user = User::where('id', $request->client_id)->first();

            $group = AccountType::where('id', $validatedData['options'])->firstOrFail();

            $referral=$user->referral;
            $ib=$user->ib1;
            $account_type_id = $validatedData['options'];

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
                $response = $this->CreateCompetition($new_user, $user_server, 'Live');

                if ($response['status']) {
                    $account = Account::where('id', $request->account_id)->first();
                    activity()->causedBy($user)
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
                     if($account)
                    {
                        $account->update([
                            'user_id' => $user->id,
                            'name' => $new_user->Name,
                            'demo'=> true,
                            'email' => $new_user->Email,
                            // 'name' => $new_user->Name,
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
                        'demo'=> true,
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
    }

    function CreateCompetition($user, &$user_server, $type)
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
}
