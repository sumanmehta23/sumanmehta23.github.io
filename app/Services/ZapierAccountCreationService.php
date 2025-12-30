<?php

namespace App\Services;

use App\Models\User;
use App\Models\Account;
use App\Models\DemoDeposit;
use App\Models\TradeDeposit;
use App\Models\AccountType;
use App\Models\BonusTransaction;
use App\Models\Ib1;
use App\MT5\MTRetCode;
use App\MT5\MTEnDealAction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service for handling Zapier webhook account creation
 * 
 * Uses existing project methods for:
 * - User creation (same as LoginController::addUser)
 * - MT5 Demo account creation (same as MT5Accounts::createMT5DemoAccount)
 * - Bonus deposit (same as MT5Controller::bonusToAccount)
 */
class ZapierAccountCreationService
{
    protected $mt5Service;
    protected $mailService;
    protected $settings;
    protected $api;
    protected const BONUS_AMOUNT = 50;
    protected const BONUS_CURRENCY = 'USD';
    protected const BONUS_TYPE = 'Bonus In';
    protected const CREATED_FROM = 'zapier';

    public function __construct(UniversalMT5Service $mt5Service, MailService $mailService)
    {
        $this->mt5Service = $mt5Service;
        $this->mailService = $mailService;
        $this->settings = settings();
        $this->api = null;
    }

    /**
     * Ensure MT5 connection is established (same as project methods)
     */
    private function ensureMT5Connection(): bool
    {
        if (!$this->api) {
            if (!$this->mt5Service->connect()) {
                Log::error('Zapier: Failed to connect to MT5 via pool.');
                return false;
            }
            $this->api = $this->mt5Service->getApi();
        }
        return $this->api !== null;
    }

    /**
     * Create account from Zapier webhook
     * Uses exact same flow as manual user registration
     * 
     * @param array $data Validated data from request
     * @return array Response with status, user, account and bonus details
     * @throws Exception
     */
    public function createAccount(array $data): array
    {
        try {
            // Step 1: Create LQH User Account (using project's user creation method)
            $user = $this->createUserAccount($data);
            if (!$user) {
                throw new Exception('Failed to create user account');
            }

            Log::info("Zapier: User account created", [
                'user_id' => $user->id,
                'email' => $user->email,
                'created_from' => self::CREATED_FROM
            ]);

            // Step 2: Create MT5 Live Standard Account (Zapier should create live accounts)
            $accountType = $this->getDefaultLiveAccountType();
            if (!$accountType) {
                throw new Exception('No live account type available for Zapier users');
            }

            $account = $this->createMT5LiveAccount($user, $accountType, $data);
            if (!$account) {
                throw new Exception('Failed to create MT5 account');
            }

            Log::info("Zapier: MT5 live account created", [
                'account_id' => $account->id,
                'user_id' => $user->id,
                'code' => $account->code
            ]);

            // Step 3: Credit $50 non-withdrawable bonus (using project's bonus method)
            // This is outside the transaction to prevent rollback if MT5 isn't available
            $bonus = $this->creditZapierBonus($user, $account);
            if (!$bonus) {
                Log::warning("Zapier: Bonus credit failed, but user and account created", [
                    'user_id' => $user->id,
                    'account_id' => $account->id
                ]);
                // Don't throw - user and account are already created
            } else {
                Log::info("Zapier: Bonus deposit credited", [
                    'account_id' => $account->id,
                    'user_id' => $user->id,
                    'bonus_amount' => self::BONUS_AMOUNT
                ]);
            }

            // Step 4: Send welcome email
            $this->sendWelcomeEmail($user, $account);

            return [
                'success' => true,
                'message' => 'Account successfully created with $' . self::BONUS_AMOUNT . ' bonus',
                'user' => $user,
                'account' => $account,
                'bonus' => $bonus,
            ];
        } catch (Exception $e) {
            Log::error("Zapier account creation failed", [
                'email' => $data['email'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Create LQH user account
     * Uses EXACT same method as LoginController::addUser
     */
    protected function createUserAccount(array $data): ?User
    {
        try {
            // Build user data same way as LoginController::addUser
            $userData = [];
            $number = ($data['country_code'] ?? '+1') . ($data['phone'] ?? '');
            $code = Str::random(60);

            $userData['email'] = strtolower($data['email']);
            $userData['fullname'] = $data['name'];
            $userData['password'] = Hash::make($this->generateSecurePassword());
            $userData['country_code'] = $data['country_code'] ?? '+1';
            $userData['number'] = $number;
            $userData['username'] = strtolower($data['email']);
            $userData['country'] = $data['country'] ?? 'Unknown';
            $userData['emailToken'] = $code;
            $userData['created_from'] = self::CREATED_FROM;
            $userData['created_at'] = now();
            $userData['updated_at'] = now();
            $userData['client_ip'] = request()->ip() ?? '0.0.0.0';

            $user = User::create($userData); 
        
            if ($user) {
            event(new \Illuminate\Auth\Events\Registered($user));

                $settings = settings();
                $from = $settings['email_from_address'];
                $toEmail = $user->email;
                $uid = uniqid();
                $emailSubject = $settings['admin_title'] . ' - Email Address Verification';
                $htmlContent = "";
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $content =
                    '<p>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</p>' .
                    '<p></p>' .
                    '<p>You are receiving this email because you have registered for a LQH Markets Account.</p>' .
                    '<p></p>' .
                    '<p>Click the link below to activate your Account</p>';

                $templateVars = [
                    'name' => $user->fullname,
                    'server_name' => $settings['mt5_company_name'],
                    'site_link' => $settings['copyright_site_name_text'] . "/email_verify?id={$user->id}&code=$code",
                    'email' => $settings['email_from_address'],
                    "content" => $content,
                    "title_right" => "Activate",
                    "subtitle_right" => "Your Account",
                    "btn_text" => "Activate",
                ];
                $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);
            }    

            return $user;
        } catch (Exception $e) {
            Log::error("Zapier: Failed to create user account", [
                'email' => $data['email'],
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get default live account type
     * Prefer 'Standard' account type when available, fallback to any live mt5 group
     */
    protected function getDefaultLiveAccountType(): ?AccountType
    {
        try {
            $accountType = AccountType::with('mt5Group')
                ->whereHas('mt5Group', function ($query) {
                    $query->where('mt5_group_type', 'live');
                })
                ->where('is_client_group', 1)
                ->where('ac_group', 'LM\B-Book\STD\DF-B')
                ->orderBy('display_priority', 'desc')
                ->first();

            return $accountType;
        } catch (Exception $e) {
            Log::error("Zapier: Failed to get live account type", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create MT5 Live Account (for Zapier-created users)
     */
    protected function createMT5LiveAccount(User $user, AccountType $accountType, array $data): ?Account
    {
        try {
            $new_user_data = [];
            $new_user_data['MainPassword'] = $this->generatePassword();
            $new_user_data['Group'] = $accountType->ac_group;
            $new_user_data['type'] = $accountType->ac_name;
            $new_user_data['Leverage'] = $accountType->ac_max_leverage;
            $new_user_data['ZipCode'] = $user->zipcode ?? '';
            $new_user_data['Country'] = $user->country ?? '';
            $new_user_data['State'] = $user->state ?? '';
            $new_user_data['City'] = $user->city ?? '';
            $new_user_data['Address'] = $user->address ?? '';
            $new_user_data['Phone'] = $user->number ?? '';
            $new_user_data['Currency'] = self::BONUS_CURRENCY; // USD
            $new_user_data['Company'] = $this->settings['mt5_company_name'];
            $new_user_data['Name'] = $user->fullname ?? $user->email;
            $new_user_data['Email'] = $user->email;
            $new_user_data['LeadSource'] = $user->ib1 ?? "";
            $new_user_data['PhonePassword'] = $this->generatePassword();
            $new_user_data['InvestPassword'] = $this->generatePassword();
            $new_user_data['Login'] = $this->generateRandomNumber();


            $mt5Success = false;
            $serverAssignedLogin = $new_user_data['Login'];
            try {
                // Build the MT5 user object (same fields as in MT5Accounts::createLiveAccount)
                $new_user = $this->mt5Service->userCreate();
                $new_user->MainPassword = $new_user_data['MainPassword'];
                $new_user->Group =  $new_user_data['Group'];
                $new_user->type = $accountType->ac_name ?? $new_user_data['type'];
                $new_user->Leverage = $new_user_data['Leverage'];
                $new_user->ZipCode = $new_user_data['ZipCode'];
                $new_user->Country = $new_user_data['Country'];
                $new_user->State = $new_user_data['State'];
                $new_user->City = $new_user_data['City'];
                $new_user->Address = $new_user_data['Address'];
                $new_user->Phone = $new_user_data['Phone'];
                $new_user->Currency = $new_user_data['Currency'];
                $new_user->Company = $new_user_data['Company'];
                $new_user->Name = $new_user_data['Name'];
                $new_user->Email = $new_user_data['Email'];
                $new_user->LeadSource = $new_user_data['LeadSource'];
                $new_user->PhonePassword = $new_user_data['PhonePassword'];
                $new_user->InvestPassword = $new_user_data['InvestPassword'];
                $new_user->Login = $new_user_data['Login'];

                
                $mt5Controller = app(\App\Http\Controllers\MT5Accounts::class);
                $created_server_user = null;
                $response = $mt5Controller->CreateAccount($new_user, $created_server_user, 'Live');

                if (!empty($response['status'])) {
                    $mt5Success = true;
                    // prefer server-assigned login if available
                    if (isset($created_server_user->Login) && $created_server_user->Login) {
                        $serverAssignedLogin = $created_server_user->Login;
                        // ensure new_user->Login is set for sendMail
                        $new_user->Login = $serverAssignedLogin;
                    }
                    Log::info('Zapier: MT5 live account created via MT5Accounts::CreateAccount', [
                        'login' => $serverAssignedLogin,
                        'email' => $user->email,
                        'group' => $new_user_data['Group'],
                    ]);
                } else {
                    Log::warning('Zapier: MT5Accounts::CreateAccount returned failure, creating local records', [
                        'response' => $response,
                        'email' => $user->email,
                        'group' => $new_user_data['Group']
                    ]);
                }
            } catch (\Exception $mtEx) {
                Log::warning('Zapier: Exception while creating MT5 account via MT5Accounts controller', [
                    'error' => $mtEx->getMessage(),
                    'email' => $user->email
                ]);
            }

            $account = Account::create([
                'user_id' => $user->id,
                'name' => $new_user_data['Name'],
                'demo' => false, // Live account for Zapier
                'platform' => Account::PLATFORM_MT5,
                'email' => $new_user_data['Email'],
                'account_nick_name' => 'Zapier Live Account',
                'code' => $serverAssignedLogin,
                'account_type_id' => $accountType->id,
                'leverage' => $new_user_data['Leverage'],
                'currency' => $new_user_data['Currency'],
                'trader_password' => $new_user_data['MainPassword'],
                'invester_password' => $new_user_data['InvestPassword'],
                'phone_password' => $new_user_data['PhonePassword'],
                'ib1' => $user->ib1 ?? null,

                'account_request_status' => ($mt5Success ? 1 : 0),
                'created_from' => self::CREATED_FROM,
                'balance' => 0,
                'ac_group' =>  $new_user_data['Group'],
            ]);

            if ($mt5Success) {
                try {
                    $mt5Controller = app(\App\Http\Controllers\MT5Accounts::class);
                    $mt5Controller->sendMail($new_user, 'Live', $account->platform);
                } catch (\Exception $mailEx) {
                    Log::warning('Zapier: Failed to send MT5 account email via MT5Accounts::sendMail', [
                        'error' => $mailEx->getMessage(),
                        'email' => $user->email
                    ]);
                }
            }

            return $account;
        } catch (Exception $e) {
            Log::error("Zapier: Failed to create MT5 live account", [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Credit Zapier Bonus Deposit
     * Uses EXACT same flow as MT5Controller::bonusToAccount but simpler
     * Adds $50 bonus directly to MT5 account
     */
    protected function creditZapierBonus(User $user, Account $account): ?BonusTransaction
    {
        try {
            $login = (int)$account->code;
            $amount = self::BONUS_AMOUNT;
            $comment = 'Zapier Bonus Deposit';
            $ticket = null;
            $operation = MTEnDealAction::DEAL_BALANCE;
            $comment = 'Bonus Deposit';

            if ($this->ensureMT5Connection()) {
                try {
                    $errorCode = $this->mt5Service->tradeBalance(
                        (int)$login,
                        $operation,
                        $amount,
                        $comment,
                        $ticket,
                        true
                    );

                    if ($errorCode !== MTRetCode::MT_RET_OK) {
                        Log::warning('Zapier: MT5 deposit operation failed, but will create local records', [
                            'error_code' => $errorCode,
                            'account_id' => $account->id
                        ]);
                    }
                } catch (\Exception $mtEx) {
                    Log::warning('Zapier: MT5 connection error during deposit, creating local bonus records', [
                        'error' => $mtEx->getMessage()
                    ]);
                }
            } else {
                Log::info('Zapier: MT5 not available, creating bonus records without MT5 sync');
            }

            $bonusData = [
                'email' => $user->email,
                'user_id' => $user->id,
                'account_id' => $account->id,
                'code' => $account->code,
                'bonus_amount' => $amount,
                'bonus_type' => self::BONUS_TYPE,
                'bonus_currency' => self::BONUS_CURRENCY,
                'status' => 1, // Active
                'admin_remark' => 'Non-Withdrawable Bonus - Zapier',
                'bonus_date' => now()->toDateString(),
            ];
            
            $bonusTransaction = BonusTransaction::create($bonusData);

            activity()
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'admin_email' => auth()->guard('admin')->user()->email,
                    'userRole' => auth()->guard('admin')->user()->userRole,
                    'username' => auth()->guard('admin')->user()->username,
                    'admin_id' => auth()->guard('admin')->user()->id,
                    'client_id' => $user->id,
                    'client_email' => $bonusData['email'],
                    'bonus_amount' => $amount,
                    'bonus_type' => self::BONUS_TYPE,
                    'code' => $account->code,
                    'account_id' => $account->id,
                    'platform' => $account->platform,
                    'remark' => 'CRM Zapier Deposit Bonus'
                ])
                ->event('create')
                ->log('CRM Zapier Bonus');

            return $bonusTransaction;
        } catch (Exception $e) {
            Log::error("Zapier: Failed to credit bonus", [
                'user_id' => $user->id ?? 'unknown',
                'account_id' => $account->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Send welcome email to newly created user
    */
    protected function sendWelcomeEmail(User $user, Account $account): void
    {
        try {
            $emailSubject = $this->settings['admin_title'] . ' - Welcome to LQH Markets';
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From:' . $this->settings['admin_title'] . '<' . $this->settings['email_from_address'] . '>' . "\r\n";

            $content = '<p>Welcome to ' . htmlspecialchars($this->settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</p>' .
                '<p>Your account has been successfully created.</p>' .
                '<p><strong>Account Details:</strong></p>' .
                '<ul>' .
                '<li><strong>Email:</strong> ' . $user->email . '</li>' .
                '<li><strong>Trading Account:</strong> ' . $account->code . '</li>' .
                '<li><strong>Welcome Bonus:</strong> $' . self::BONUS_AMOUNT . ' (Non-Withdrawable)</strong></li>' .
                '</ul>' .
                '<p>You can now start trading with your welcome bonus. Remember, the bonus is for trading purposes only.</p>' .
                '<p>If you have any questions, please contact our support team at ' . $this->settings['email_from_address'] . '</p>';

            $templateVars = [
                'name' => $user->fullname,
                'server_name' => $this->settings['mt5_company_name'],
                'email' => $this->settings['email_from_address'],
                'content' => $content,
                'title_right' => 'Welcome',
                'subtitle_right' => 'To LQH Markets',
                'btn_text' => 'Dashboard',
                'site_link' => $this->settings['copyright_site_name_text'],
            ];

            $this->mailService->sendEmail($user->email, $emailSubject, $headers, '', $templateVars);
        } catch (Exception $e) {
            Log::warning("Failed to send welcome email", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            // Don't throw - email is not critical to account creation
        }
    }

    /**
     * Generate secure password for user
     */
    protected function generateSecurePassword(): string
    {
        // Generate a password that meets requirements:
        // At least 8 characters, lowercase, uppercase, number, special character
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*-_=+';

        $password = '';
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $special[rand(0, strlen($special) - 1)];

        // Fill the rest with random characters
        $all = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < 12; $i++) {
            $password .= $all[rand(0, strlen($all) - 1)];
        }

        // Shuffle the password
        return str_shuffle($password);
    }

    /**
     * Generate MT5 password
     */
    protected function generatePassword(): string
    {
        // Use the same password generation logic as MT5Accounts controller
        try {
            $mt5Controller = app(\App\Http\Controllers\MT5Accounts::class);
            if (method_exists($mt5Controller, 'generatePassword')) {
                return $mt5Controller->generatePassword();
            }
        } catch (\Exception $e) {
            Log::warning('Zapier: Failed to use MT5Accounts::generatePassword, falling back', ['error' => $e->getMessage()]);
        }

        return Str::random(12);
    }

    /**
     * Generate random MT5 account login number
     */
    protected function generateRandomNumber(): int
    {
        return rand(100000, 999999);
    }
}
