<?php
namespace App\Console\Commands;

use Exception;
use App\Models\Ib1;
use App\Models\User;
use App\Models\Symbol;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\IbWallet;
use App\Models\AccountType;
use App\Models\DemoDeposit;
use App\MT5\MTEnDealAction;
use Illuminate\Support\Str;
use App\Services\MT5Service;
use App\Models\Ib1Commission;
use App\Models\IbPlanDetails;
use App\Services\MailService;
use Illuminate\Console\Command;
use App\Jobs\SyncAccountTradesJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ActivateCompetitionAccounts extends Command
{

    protected $api;
    protected $mailService;
    protected $mt5Service;

    public function __construct(MT5Service $mt5Service, MailService $mailService)
    {
        parent::__construct(); // ← THIS IS REQUIRED

        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
    }


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:activate-competition-accounts';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Account::where('demo', 1)
            ->whereNotNull('competition_month')
            ->whereNotNull('competition_year')
            ->where('code',NULL)
            ->chunk(100, function ($accounts) {
                foreach ($accounts as $account) {
                    $settings = settings();
                    $user = User::where('id', $account->user_id)->first();
                    Log::info('Competition counts '.json_encode(count($accounts)));
                    $group = AccountType::where('id', $account->account_type_id)->firstOrFail();

                    $referral=$user->referral;
                    $ib=$user->ib1;
                    $account_type_id = $account->account_type_id;

                    $ibdata = '';
                    if($ib){
                        $ibdata = Ib1::where('referral_code',$ib)->first();
                    }

                    $new_user = $this->api->UserCreate();
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
                    $new_user->Name = $user->fullname??$user->email;
                    $new_user->Email = $user->email;
                    $new_user->LeadSource = $user->ib1?? "" ;
                    $new_user->Agent = $ibdata->indexId?? "" ;
                    $new_user->PhonePassword = $this->generatePassword();
                    $new_user->InvestPassword = $this->generatePassword();
                    $new_user->Login = $this->generateRandomNumber();
                    $response = $this->CreateCompetition($new_user, $user_server, 'Live');

                    if ($response['status']) {
                        $acc = Account::where('id', $account->id)->first();
                        // activity()->causedBy($user)
                        //     ->withProperties(
                        //         [
                        //             // 'ip' => $request->ip(),
                        //             'email' => $user->email,
                        //             'type' => 'Demo',
                        //             'code' => $new_user->Login,
                        //             'amount' => $account->balance,
                        //             'leverage' => $new_user->Leverage,
                        //             'remark' => 'Create Demo Account'
                        //         ])
                        // ->event('create')
                        // ->log('Create Demo Account');
                        if($acc)
                        {
                            $acc->update([
                                'user_id' => $user->id,
                                'name' => $new_user->Name,
                                'demo'=> true,
                                'email' => $new_user->Email,
                                'code' => $new_user->Login,
                                'account_type_id' => $account->account_type_id,
                                'leverage' => $new_user->Leverage,
                                'currency' => $new_user->Currency,
                                'trader_password' => $new_user->MainPassword,
                                'invester_password' => $new_user->InvestPassword,
                                'phone_password' => $new_user->PhonePassword,
                                'balance' => $account->balance,
                                'account_request_status' => 1,
                            ]);
                            $errorCode = $this->api->TradeBalance($new_user->Login, $type = MTEnDealAction::DEAL_BALANCE, $account->balance, 'Deposit', $ticket, $margin_check = true);
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
                                    'deposit_amount' => $account->balance,
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
                }
            });
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
            Log::error('Competition create error : ' . $error.' for user '.json_encode($user));
            return ["status" => false, "message" => $error];
        } else {
            Log::info('Competition created successfully for user '.json_encode($user).' with server response '.json_encode($user_server));
            return ["status" => true, "message" => $type . " Competition Created Successfully"];
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
            "subtitle_right" => "Your " . $type . " Competition is Ready!",
            "acc_type" => $new_user->type
        ];
        $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);

    }
}
