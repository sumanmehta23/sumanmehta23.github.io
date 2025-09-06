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
use App\Services\UniversalMT5Service;
use App\Models\Ib1Commission;
use App\Models\IbPlanDetails;
use App\Services\MailService;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ActivateCompetitionAccounts extends Command
{

    protected $api;
    protected $mailService;
    protected $mt5Service;

    public function __construct(UniversalMT5Service $mt5Service, MailService $mailService)
    {
        parent::__construct(); // ← THIS IS REQUIRED
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        // Do NOT connect or getApi here; defer until handle()
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
        // Defer MT5 API instantiation until handle is called
        if (!$this->mt5Service->connect()) {
            $this->error('Failed to connect to MT5 via pool.');
            return 1;
        }
        $this->api = $this->mt5Service->getApi();
        // ...existing code...
        Account::with('accountType')
            ->where('demo', 1)
            ->whereNotNull('competition_start_date')
            ->whereNotNull('competition_end_date')
            // ->whereNull('competition_status')
            ->where('competition_email', 0)
            ->whereHas('accountType', function ($query) {
                $query->where('competition_start_date', '<=', Carbon::now('UTC'));
            })
            // ->whereDate('competition_end_date', '>=', Carbon::today())
            // ->where('code',NULL)
            ->chunk(100, function ($accounts) {
                foreach ($accounts as $account) {
                    $settings = settings();
                    $user = User::where('id', $account->user_id)->first();
                    $group = AccountType::where('id', $account->account_type_id)->first();
                    $ib = $user ? $user->ib1 : null;
                    $ibdata = '';
                    if ($ib) {
                        $ibdata = Ib1::where('referral_code', $ib)->first();
                    }
                    if ($account->code == NULL) {
                        $new_user = $this->api->UserCreate();
                        $new_user->MainPassword = $this->generatePassword();
                        $new_user->Group = $group ? $group->ac_group : '';
                        $new_user->type = $group ? $group->ac_name : '';
                        $new_user->Leverage = $account->leverage;
                        $new_user->ZipCode = $user ? $user->zipcode : '';
                        $new_user->Country = $user ? $user->country : '';
                        $new_user->State = $user ? $user->state : '';
                        $new_user->City = $user ? $user->city : '';
                        $new_user->Address = $user ? $user->address : '';
                        $new_user->Phone = $user ? $user->number : '';
                        $new_user->currency = 'USD';
                        $new_user->Company = $settings['mt5_company_name'] ?? '';
                        $new_user->Name = $user ? ($user->fullname ?? $user->email) : '';
                        $new_user->Email = $user ? $user->email : '';
                        $new_user->LeadSource = $user ? ($user->ib1 ?? "") : "";
                        $new_user->Agent = $ibdata ? ($ibdata->indexId ?? "") : "";
                        $new_user->PhonePassword = $this->generatePassword();
                        $new_user->InvestPassword = $this->generatePassword();
                        $new_user->Login = $this->generateRandomNumber();
                        $user_server = null;
                        $response = $this->CreateCompetition($new_user, $user_server, 'Live');

                        if ($response['status']) {
                            $acc = Account::where('id', $account->id)->first();
                            if ($acc) {
                                $acc->update([
                                    'user_id' => $user ? $user->id : null,
                                    'name' => $new_user->Name,
                                    'demo' => true,
                                    'email' => $new_user->Email,
                                    'code' => $new_user->Login,
                                    'account_type_id' => $account->account_type_id,
                                    'leverage' => $new_user->Leverage,
                                    'currency' => $new_user->currency,
                                    'trader_password' => $new_user->MainPassword,
                                    'invester_password' => $new_user->InvestPassword,
                                    'phone_password' => $new_user->PhonePassword,
                                    'balance' => $account->balance,
                                    'account_request_status' => 1,
                                    'competition_status' => 'Active',
                                ]);
                                $ticket = null;
                                $errorCode = $this->api->TradeBalance(
                                    $new_user->Login,
                                    MTEnDealAction::DEAL_BALANCE,
                                    $account->balance,
                                    'Deposit',
                                    $ticket,
                                    true
                                );
                                if ($errorCode != MTRetCode::MT_RET_OK) {
                                    $error = MTRetCode::GetError($errorCode);
                                    Log::error('MT5 demo account : ' . $error . ' for user ' . ($user ? $user->id : 'unknown'));
                                } else {
                                    $data = [
                                        'user_id' => $user ? $user->id : null,
                                        'account_id' => $account->id,
                                        'email' => $new_user->Email,
                                        'code' => $new_user->Login,
                                        'deposit_amount' => $account->balance,
                                        'Status' => 1
                                    ];
                                    DemoDeposit::create($data);
                                }
                                $this->sendMail($new_user, 'Demo', $account);
                                $account->competition_email = 1;
                                $account->save();
                            }
                        }
                    } else {
                        $type = 'Competition';
                        $toEmail = $user ? $user->email : '';
                        $from = $settings['email_from_address'] ?? '';
                        $emailSubject = ($settings['admin_title'] ?? '') . ' - Competition Account Details';
                        $headers = "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                        $headers .= 'From:' . ($settings['admin_title'] ?? '') . '<' . $from . '>' . "\r\n";
                        $content = "
                                        <div style='font-family: Montserrat, sans-serif; color: #000000;'>
                                            <p style='color: #000000;'>The wait is over — the LQH Markets " . ($account->accountType->ac_name ?? '') . " is officially underway!</p>
                                            <hr style='border: none; border-top: 0.3px solid rgb(183, 182, 182); margin: 20px 0;'>
                                            <p style='color: #000000;'>Now is your chance to put your trading strategies to the test and aim for the top of the leaderboard.</p>
                                            <p style='color: #000000;'>Log in to your account, start trading on your preferred instruments, and stay ahead of the market.</p>
                                            <p style='color: #000000;'>We wish you the best of luck throughout the competition!</p>
                                            <p>Your MT5 account is ready! You are all set to dive into the exciting world of trading.</p>
                                        </div>
                                    ";
                        $templateVars = [
                            'name' => $user ? $user->fullname : '',
                            'type' => $type,
                            'code' => $account->code,
                            'trader_password' => $account->trader_password,
                            'investor_password' => $account->invester_password,
                            'leverage' => "1:" . $account->leverage,
                            'server_name' => $settings['mt5_company_name'] ?? '',
                            'email' => $settings['email_from_address'] ?? '',
                            "title_right" => "",
                            "subtitle_right" => "Your " . $type . " Competition is Ready!",
                            "acc_type" => 'Demo',
                            "content" => $content
                        ];
                        $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);
                        $account->competition_email = 1;
                        $account->save();
                    }
                }
            });
    }
    function CreateCompetition($user, &$user_server, $type)
    {
        $settings = settings();
        // Ensure API is initialized if not already
        if (!$this->api) {
            if (!$this->mt5Service->connect()) {
                Log::error('MT5 Competition create connection error for user ' . json_encode($user));
                return ["status" => false, "message" => "Failed to connect to MT5"];
            }
            $this->api = $this->mt5Service->getApi();
        }
        if (($error_code = $this->api->UserAdd($user, $user_server)) != MTRetCode::MT_RET_OK) {
            $error = MTRetCode::GetError($error_code);
            Log::error('Competition create error : ' . $error . ' for user ' . json_encode($user));
            return ["status" => false, "message" => $error];
        } else {
            Log::info('Competition created successfully for user ' . json_encode($user) . ' with server response ' . json_encode($user_server));
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

    public function sendMail($new_user, $type, $account)
    {
        $settings = settings();
        $toEmail = $new_user->Email;
        $from = $settings['email_from_address'];
        $emailSubject = $settings['admin_title'] . ' - ' . 'Competition Account Details';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content = "
                    <div style='font-family: Montserrat, sans-serif; color: #000000;'>
                        <p style='color: #000000;'>The wait is over — the LQH Markets {$account->accountType->ac_name} is officially underway!</p>
                        <hr style='border: none; border-top: 0.3px solid rgb(183, 182, 182); margin: 20px 0;'>
                        <p style='color: #000000;'>Now is your chance to put your trading strategies to the test and aim for the top of the leaderboard.</p>
                        <p style='color: #000000;'>Log in to your account, start trading on your preferred instruments, and stay ahead of the market.</p>
                        <p style='color: #000000;'>We wish you the best of luck throughout the competition!</p>
                        <p>Your MT5 account is ready! You are all set to dive into the exciting world of trading.</p>
                    </div>
                    ";
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
            "acc_type" => $new_user->type,
            "content" => $content
        ];
        $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);
    }
}
