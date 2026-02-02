<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\Models\Ib1;
use App\Models\Role;
use App\Models\User;
use App\Models\IbPlan;
use App\Models\KycLog;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\Country;
use App\Models\UserLog;
use App\Models\IbWallet;
use App\Models\Mt5Group;
use App\Models\KycUpdate;
use App\Models\IbCategory;
use App\Models\TicketType;
use App\Models\AccountType;
use App\Models\EmployeeList;
use App\Models\TicketStatus;
use App\Models\TotalBalance;
use App\Models\TradeDeposit;
use App\Models\ClientNote;
use App\Services\UniversalMT5Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\IbPlanDetails;
use App\Models\WalletDeposit;
use App\Services\MailService;
use App\Models\WalletWithdraw;
use Illuminate\Validation\Rule;
use App\Models\ClientBankDetail;
use Illuminate\Auth\Access\Gate;
use App\Models\RelationshipManager;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;

class ClientController extends Controller
{
    protected $mailService;
    protected $api;
    protected $mt5Service;
    public function __construct(MailService $mailService, UniversalMT5Service $mt5Service)
    {
        $this->mailService = $mailService;
        // Gate::validate('view-client');
        $this->mt5Service = $mt5Service;
    }

    /**
     * Ensure MT5 connection is established
     */
    private function ensureMT5Connection(): bool
    {
        if (!$this->api) {
            if (!$this->mt5Service->connect()) {
                Log::error('Failed to connect to MT5 via pool.');
                return false;
            }
            $this->api = $this->mt5Service->getApi();
        }
        return $this->api !== null;
    }
    public function index()
    {
        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];
        // Fetch IB details
        $ib_details = DB::table('ib1')
            ->select('name', 'email', 'referral_code')
            ->orderBy('name')
            ->get();


        // Fetch RM details
        $rm_details = DB::table('emplist as emp')
            ->select('emp.client_index', 'emp.email', 'emp.username')
            ->where('emp.role_id', 2)
            ->get();

        // Fetch Countries
        $countries = Country::all();

        // Fetch Deposits and Withdrawals
        $trade_deposit = TradeDeposit::where('status', 1)
            ->whereNotIn('deposit_type', ['Wallet Transfer'])
            ->sum('deposit_amount');

        $wallet_deposit = DB::table('wallet_deposit')
            ->where('status', 1)
            ->sum('deposit_amount');

        $trade_withdrawal = DB::table('trade_withdrawal')
            ->where('status', 1)
            ->whereNotIn('withdraw_type', ['Wallet Withdrawal'])
            ->sum('withdrawal_amount');

        $wallet_withdrawal = DB::table('wallet_withdraw')
            ->where('status', 1)
            ->sum('withdraw_amount');
        $wallet_withdrawal_fee = DB::table('wallet_withdraw')
            ->where('status', 1)
            ->sum('withdraw_transaction_fee');
        $wallet_withdrawal = $wallet_withdrawal + $wallet_withdrawal_fee;
        // Total Clients & IBs count
        if ($role === "Relationship Manager") {
            $total_clients = DB::table("aspnetusers")
                ->leftJoin('relationship_manager as rm', 'aspnetusers.id', '=', 'rm.user_id')
                ->where('rm.rm_id', $alogin)
                ->count();
        } else {
            $total_clients = DB::table("aspnetusers")->count();
        }

        $total_ib = DB::table('ib1')
            ->leftJoin('relationship_manager as rm', 'rm.user_id', '=', 'ib1.email')
            ->when(Session::get('userData.role_id') == 2, function ($query) {
                $query->where('rm.rm_id', Session::get('alogin'));
            })
            ->count();

        // Total Balance Details
        $total_balance = DB::table('total_balance')
            ->select(
                DB::raw('COALESCE(SUM(deposit_amount), 0) as deposit_amount'),
                DB::raw('COALESCE(SUM(withdraw_amount), 0) as withdraw_amount'),
                DB::raw('COALESCE(SUM(trading_deposited), 0) as trading_deposited'),
                DB::raw('COALESCE(SUM(trading_withdrawal), 0) as trading_withdrawal')
            )
            ->leftJoin('relationship_manager as rm', 'rm.user_id', '=', 'total_balance.email')
            ->when(Session::get('userData.role_id') == 2, function ($query) {
                $query->where('rm.rm_id', Session::get('alogin'));
            })
            ->first();

        // Account Groups
        $acc_groups = DB::table('ib_plan_details')
            ->leftJoin('ib_categories', 'ib_categories.id', '=', 'ib_plan_details.ib_category_id')
            ->where('ib_plan_details.status', 1)
            ->select(DB::raw('ib_categories.ib_cat_name,ib_plan_details.id,ib_plan_details.ib_category_id'))
            ->groupBy('ib_plan_details.ib_category_id')
            ->get();

        // dd($acc_groups);

        return view("admin.client_list", compact(
            'ib_details',
            'rm_details',
            'countries',
            'trade_deposit',
            'wallet_deposit',
            'trade_withdrawal',
            'wallet_withdrawal',
            'total_clients',
            'total_ib',
            'total_balance',
            'acc_groups',
        ));
    }
    public function updateRM(Request $request)
    {
        $role = Role::find(Session::get('userData.role_id'));

        if ($request->has('rmUpdate') && $role && $role->name = "Super Admin") {
            $user_id = $request->input('user_id');
            // $result = DB::table('aspnetusers')
            //     ->select('id')
            //     ->where('email', '=', $email)
            //     ->first();
            // $user_id = $result->id;
            $rm_id = $request->input('rm_id');
            $exists = RelationshipManager::where('user_id', $user_id)->count();
            if ($exists > 0) {
                RelationshipManager::where('user_id', $user_id)->update(['rm_id' => $rm_id]);
            } else {
                RelationshipManager::create(['user_id' => $user_id, 'rm_id' => $rm_id, 'added_by' => Auth::id()]);
            }
            activity()
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'admin_email' => auth()->guard('admin')->user()->email,
                    'userRole' => auth()->guard('admin')->user()->userRole,
                    'username' => auth()->guard('admin')->user()->username,
                    'admin_id' => auth()->guard('admin')->user()->id,
                    'client_id' => $user_id,
                    'rm_id' => $rm_id,
                    'remark' => 'RM Request'
                ])
                ->event('update')
                ->log('RM Request');
            return redirect()->back()->with('success', 'RM Details Updated');
        }
        return redirect()->back()->with('success', 'Only Super Admin can update');
    }

    public function updateIB(Request $request)
    {
        if ($request->has('ibUpdate')) {
            try {
                $ibFields = [];
                $user_id = $request->input('client_id');

                for ($i = 1; $i <= 15; $i++) {
                    $value = $request->input("ib$i");
                    if (!empty($value)) {
                        $ibFields[] = $value;
                    }
                }

                if (count($ibFields) !== count(array_unique($ibFields))) {
                    return redirect()->back()->withErrors('Some IB fields contain duplicate values.');
                } else {
                    $currentValues = DB::table('aspnetusers')
                        ->whereRaw('id = ?', [$user_id])
                        ->select('ib1', 'ib2', 'ib3', 'ib4', 'ib5', 'ib6', 'ib7', 'ib8', 'ib9', 'ib10', 'ib11', 'ib12', 'ib13', 'ib14', 'ib15')
                        ->first();

                    $updateFields = [];
                    $logdata = [];

                    for ($i = 1; $i <= 15; $i++) {
                        $fieldName = "ib$i";
                        $newValue = $request->input($fieldName);
                        if ($newValue !== $currentValues->$fieldName) {
                            $updateFields[$fieldName] = $newValue;
                            $logdata[$fieldName] = $newValue;
                        }
                    }

                    if (!empty($updateFields)) {
                        DB::table('aspnetusers')
                            ->whereRaw('id = ?', [$user_id])
                            ->update($updateFields);

                        $user = DB::table('aspnetusers')->where('id', $user_id)->first();

                        $this->addToUserLog([
                            'user_id' => $user_id,
                            'email' => $user->email,
                            'type' => 'ib',
                            'value' => json_encode($logdata)
                        ]);

                        return redirect()->back()->with('success', 'Client IB Details Updated Successfully');
                    } else {
                        return redirect()->back()->with('success', 'No changes were made. Everything is up to date!');
                    }
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('success', $e->getMessage());
            }
        }
    }

    public function addUser(Request $request)
    {
        if ($request->has('addUser')) {
            $fullname = $request->input('fullname');
            $email = $request->input('email');
            $password = $request->input('password');
            $confirmPassword = $request->input('confirm_password');
            $country = $request->input('country');
            $country_code = $request->input('country_code');
            $number = $request->input('telephone');
            $referral = '';
            $code = md5(uniqid(rand()));

            // Check if passwords match
            if ($password !== $confirmPassword) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Passwords do not match'
                ], 400);
            }

            // Check if the user already exists
            $userExist = DB::table('aspnetusers')->where('email', $email)->exists();

            if ($userExist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email already exists'
                ], 400);
            } else {
                $status = 1;
                $emailConfirmed = 1;
                try {
                    // Insert new user into the database
                    // $lastInsertId = DB::table('aspnetusers')->insertGetId([
                    //     'email' => $email,
                    //     'fullname' => $fullname,
                    //     'password' => $password,
                    //     'country_code' => $country_code,
                    //     'number' => $number,
                    //     'username' => $email,
                    //     'referral' => $referral,
                    //     'emailToken' => $code,
                    //     'country' => $country,
                    //     'status' => $status,
                    //     'email_confirmed' => $emailConfirmed,
                    // ]);

                    $user = User::create([
                        'email' => $email,
                        'fullname' => $fullname,
                        'password' => $password,
                        'country_code' => $country_code,
                        'number' => $number,
                        'username' => $email,
                        'referral' => $referral,
                        'emailToken' => $code,
                        'country' => $country,
                        'status' => $status,
                        'email_confirmed' => $emailConfirmed,
                    ]);
                    activity()
                        ->causedBy(auth()->guard('admin')->user())
                        ->withProperties([
                            'ip' => request()->ip(),
                            'admin_email' => auth()->guard('admin')->user()->email,
                            'userRole' => auth()->guard('admin')->user()->userRole,
                            'username' => auth()->guard('admin')->user()->username,
                            'admin_id' => auth()->guard('admin')->user()->id,
                            'client_id' => $user->id,
                            'client_email' => $email,
                            'client_password' => $password,
                            'status' => $status,
                            'remark' => 'Create Client'
                        ])
                        ->event('create')
                        ->log('Create Client');
                    if ($user) {
                        // Log the user addition (you need to implement this function if not already available)
                        $logData = [
                            'user_id' => $user->id,
                            'email' => $email,
                            'type' => 'client_add',
                            'value' => json_encode($request->except(['addUser', 'password', 'confirm_password']))
                        ];
                        $this->addToUserLog($logData);
                        $from = settings()['email_from_address'];
                        $emailSubject = settings()['admin_title'] . ' - Welcome Email';
                        $templateVars = [
                            'name' => $fullname,
                            'site_link' => settings()['copyright_site_name_text'],
                            'email' => $from,
                            'content' => $this->buildWelcomeContent($fullname, $email, $password),
                            'title_right' => 'Welcome',
                            'subtitle_right' => 'Aboard!',
                            'btn_text' => 'Login'
                        ];
                        $this->mailService->sendEmail($email, $emailSubject, '', '', $templateVars);
                        return redirect()->back()->with('success', 'User created successfully');
                    } else {
                        return redirect()->back()->with('error', 'User creation failed');
                    }
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
                }
            }
        }
    }

    private function buildWelcomeContent($fullname, $email, $password)
    {
        return "
            <div>Welcome to " . htmlspecialchars(settings()['admin_title'], ENT_QUOTES, 'UTF-8') . "! We're excited to have you on board.</div>
            <div>Your account has been successfully created, and you're now part of our growing community.</div>
            <div><b>Here are your login credentials:</b></div>
            <div><b>Username: </b>{$email}</div>
            <div><b>Password: </b>{$password}</div>
            <div>If you have any queries, please contact our support team. We’re here to help!</div>
        ";
    }
    public function updateUser(Request $request)
    {
        $user_id = $request->input('id');

        $validatedData = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                Rule::unique('aspnetusers', 'email')->ignore($user_id),
            ],
            'password' => [
                'sometimes', // Apply validation only if password is provided
                'nullable',
                'string',
                'min:8', // At least 8 characters
                'regex:/[a-z]/', // At least one lowercase letter
                'regex:/[A-Z]/', // At least one uppercase letter
                'regex:/\d/', // At least one number
                'regex:/[\W_]/', // At least one special character
            ],
            'confirm_password' => 'required_with:password|same:password',
        ]);

        if ($validatedData->fails()) {
            $errors = $validatedData->errors();
            $filteredErrors = [];

            // Check which specific regex rule failed and return only unmet requirements
            if ($errors->has('password')) {
                $password = $request->password;

                if (!preg_match('/[a-z]/', $password)) {
                    $filteredErrors[] = 'The password must contain at least one lowercase letter.';
                }
                if (!preg_match('/[A-Z]/', $password)) {
                    $filteredErrors[] = 'The password must contain at least one uppercase letter.';
                }
                if (!preg_match('/\d/', $password)) {
                    $filteredErrors[] = 'The password must contain at least one number.';
                }
                if (!preg_match('/[\W_]/', $password)) {
                    $filteredErrors[] = 'The password must contain at least one special character.';
                }
                if (strlen($password) < 8) {
                    $filteredErrors[] = 'The password must be at least 8 characters long.';
                }
                if ($errors->has('password.confirmed')) {
                    $filteredErrors[] = 'Passwords do not match.';
                }
            }
            if ($errors->has('email')) {
                $filteredErrors[] = $errors->get('email')[0];
            }
            $errorString = '';
            foreach ($filteredErrors as $error) {
                $errorString .= '• ' . $error;
            }
            $errorString = html_entity_decode($errorString);
            // dd($errorString);
            // return redirect()->back()->with('error', 'The email you entered is already in use and exists in our system.');
            return redirect()->back()->with('error', $errorString);
        }
        if ($request->has('updateUser')) {
            // $email = $request->input('email');
            $email = $validatedData->validated()['email'];
            $fullname = $request->input('fullname');
            $password = $request->input('password');

            $confirmPassword = $request->input('confirm_password');
            $country = $request->input('country');
            $country_code = $request->input('country_code');
            // $number = $request->input('telephone');
            $number = $request->country_code . $request->telephone;

            $emailNotification = $request->input('email_notification');
            $affiliate_id = $request->input('affiliate_id');

            $countryCode = Country::where('country_name', $request->country)
                ->select('country_code')
                ->first();

            $code = $countryCode ? $countryCode->country_code : 'null';

            if ($country_code != $code) {
                return redirect()->back()->with('error', 'Update failed! No changes were made due to a mismatch between the country and its code');
            }

            if ($password !== $confirmPassword) {
                // return response()->json([
                //     'status' => 'error',
                //     'message' => 'Passwords do not match'
                // ], 400);
                return redirect()->back()->with('error', 'Passwords do not match');
            }
            $status = 1;
            $emailConfirmed = 1;
            try {

                $user = User::find($user_id);

                if ($user) {
                    if ($email) {
                        $accounts = Account::where('email', $user->email)->get();

                        if (!$this->ensureMT5Connection()) {
                            return redirect()->back()->with('error', 'Failed to connect to MT5 server');
                        }

                        // foreach ($accounts as $account) {
                        //     $trade_user = null;
                        //     if (($error_code = $this->mt5Service->userGet($account->code, $trade_user)) != MTRetCode::MT_RET_OK) {
                        //         Log::error('error' . ' Something went wrong on getting user  details' . MTRetCode::GetError($error_code));
                        //         // return redirect()->back()->with('error', 'Something went wrong on getting user  details' . MTRetCode::GetError($error_code));
                        //     }
                        //     if ($trade_user) {
                        //         $trade_user->Email = $email;
                        //         $updated_user = "";
                        //         $error_code = $this->mt5Service->userUpdate($trade_user, $updated_user);
                        //         if ($error_code != MTRetCode::MT_RET_OK) {
                        //             Log::error("error " . $account->code . " Something went wrong on Updating email" . MTRetCode::GetError($error_code));
                        //             return redirect()->back()->with("error", "Something went wrong on Updating email" . MTRetCode::GetError($error_code));
                        //         } else {
                        //             Account::where('code', $account->code)->update([
                        //                 'email' => $email
                        //             ]);
                        //         }
                        //     }
                        // }
                        foreach ($accounts as $account) {
                            $account->email = $email;
                            $account->name = $fullname;
                            $account->save();
                            $trade_user = null;
                            if (($error_code = $this->mt5Service->userGet((int)$account->code, $trade_user)) != MTRetCode::MT_RET_OK) {
                                Log::error("Account {$account->code}: Failed to fetch user. Error: " . MTRetCode::GetError($error_code));
                                continue; // Skip to next account
                            }
                            if ($trade_user) {
                                $trade_user->Email = $email;
                                $trade_user->Name = $fullname;
                                $trade_user->Phone = $number;
                                $trade_user->Country = $country;
                                $updated_user = "";
                                $error_code = $this->mt5Service->userUpdate($trade_user, $updated_user);
                                if ($error_code != MTRetCode::MT_RET_OK) {
                                    Log::error("Account {$account->code}: Failed to update email. Error: " . MTRetCode::GetError($error_code));
                                    continue; // Skip this account but keep looping
                                }
                                Account::where('code', $account->code)->update([
                                    'email' => $email
                                ]);
                            }
                        }
                    }

                    $user->fullname = $fullname;
                    if ($password) {
                        $user->password = $password;
                    }
                    $user->number = $number;
                    $user->country_code = $country_code;
                    $user->country = $country;
                    $user->email = $email;
                    $user->affiliate_id = $affiliate_id;

                    $user->save();  // This will trigger the 'updated' event and the logic in your booted() method
                }
                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'admin_email' => auth()->guard('admin')->user()->email,
                        'userRole' => auth()->guard('admin')->user()->userRole,
                        'username' => auth()->guard('admin')->user()->username,
                        'admin_id' => auth()->guard('admin')->user()->id,
                        'send_to' => $user->id,
                        'client_fullname' => $fullname ?? '',
                        'client_password' => $password ?? '',
                        'client_number' => $number ?? '',
                        'client_country_code' => $country_code ?? '',
                        'client_country' => $country ?? '',
                        'client_email' => $user->email,
                        'remark' => 'Update Client Details'
                    ])
                    ->event('update')
                    ->log('Update Client Details');
                // $affectedRows = DB::table('aspnetusers')
                //     ->where(DB::raw('id'), $user_id)
                //     ->update([
                //         'fullname' => $fullname,
                //         'password' => $password,
                //         'number' => $number,
                //         'country_code' => $country_code,
                //         'country' => $country,
                //         'email' => $email,
                //     ]);


                // If update is successful
                if ($user) {
                    $updateData = [
                        'user_id' => $user_id,
                        'email' => $email,
                        'type' => 'client_update',
                        'value' => json_encode($request->except(['updateUser', 'password', 'confirm_password']))
                    ];
                    // dd($updateData);
                    // Log the update (you need to implement this function if not already available)
                    $this->addToUserLog($updateData);

                    // Send email notification if required
                    if ($emailNotification) {
                        $from = settings()['email_from_address'];
                        $emailSubject = settings()['admin_title'] . ' - Your Account Details Have Been Updated';
                        $templateVars = [
                            'name' => $fullname,
                            'site_link' => settings()['copyright_site_name_text'],
                            'email' => $from,
                            'content' => $this->buildEmailContent($fullname, $password, $country_code . $number, $country),
                            'title_right' => 'Account',
                            'subtitle_right' => 'Updation',
                            'btn_text' => 'Dashboard'
                        ];
                        $this->mailService->sendEmail($email, $emailSubject, '', '', $templateVars);
                    }
                    return redirect()->back()->with('success', 'Details updated successfully');
                } else {
                    return redirect()->back()->with('error', 'Update failed! No changes were made.');
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
            }
        }
    }

    private function buildEmailContent($fullname, $password, $number, $country)
    {
        return "
        <div>We hope this message finds you well!</div>
        <div>We wanted to inform you that your account details have been successfully updated.</div>
        <div><b>Latest Details:</b></div>
        <div><b>Name: </b>{$fullname}</div>
        <div><b>Password: </b>{$password}</div>
        <div><b>Telephone: </b>{$number}</div>
        <div><b>Country: </b>{$country}</div>
        <div>If you have any queries, please contact our support team. We’re here to help!</div>
        <div>Thank you for being a valued member of our community!</div>
    ";
    }

    private function addToUserLog($data)
    {

        UserLog::create([
            'user_id' => $data['user_id'],
            'email' => $data['email'],
            'admin_email' => Session::get('alogin'),
            'type' => $data['type'],
            'value' => $data['value']
        ]);
    }
    function add_to_user_log($data)
    {
        User::create([
            'email' => $data['email'],
            'admin_email' => session('alogin'),
            'type' => $data['type'],
            'value' => json_encode($data['value'])
        ]);
    }
    public function clientDetails(Request $request)
    {
        $id = request('userId');
        $user = User::with('ib')->findOrFail($id);  // Eager load 'ib' if necessary
        $countries = Country::all();
        $acc_groups = IbPlanDetails::with('plan')
            ->where('status', 1)
            ->where('deleted_at', null)
            ->groupBy('ib_category_id')
            ->get();

        $acc_types = AccountType::with('mt5Group')
            ->whereHas('mt5Group', fn($query) => $query->where('mt5_group_type', 'live'))
            ->get();

        // Get all the required data directly from $user
        $total_wd = $user->total_wd;  // Accessor for total wallet deposit
        $total_ntd = $user->NewTotalDeposit;
        $total_ntw = $user->NewTotalWithdrawal;

        $total_ww = $user->total_ww;  // Accessor for total wallet withdrawal
        $pending_ww = $user->pending_ww;  // Accessor for pending wallet withdrawal
        $wallet_balance = $user->wallet_balance;  // Accessor for wallet balance
        $total_balance = $user->total_balance;  // Accessor for total balance
        $live_accounts = $user->liveAccounts()->withTrashed()->where('account_request_status', 1)->get();  // Relationship for live accounts
        $demo_accounts = $user->demoAccounts;
        $bank_details = $user->bank_details;  // Accessor for bank details
        $kyc_details = $user->kyc_details;  // Accessor for KYC details
        $ib_details = $user->ib_details;  // Accessor for IB details
        $rm_details = $user->rm_details;  // Accessor for RM details

        $superadmin_details = $user->superadmin_details;  // Accessor for super admin details

        $country_code = $user->country_code;  // Accessor for country code

        $clients = $user->clients;  // Accessor for clients grouped by referral code

        $ticket_status = $user->ticket_status;  // Cached ticket status
        $ticket_types = $user->ticket_types;  // Cached ticket types

        $userid = $id;

        $IbTotalDeposits = $user->IbTotalDeposits;

        foreach ($user->liveAccounts()->withTrashed()->where('account_request_status', 1) as $key => $liveAccount) {
            $login = $liveAccount->code;
            // dd($login);
            if ($user->ib1) {
                $ibdata = Ib1::where('referral_code', $user->ib1)->first();
                $trade_user = null;
                if (($error_code = $this->mt5Service->userGet($login, $trade_user)) != MTRetCode::MT_RET_OK) {
                    // return redirect()->back()->with('error', 'Something went wrong on Updating details' . MTRetCode::GetError($error_code));
                }
                if ($trade_user) {
                    $trade_user->Agent = $ibdata->indexId ?? '';
                    $updated_user = "";
                    $error_code = $this->mt5Service->userUpdate($trade_user, $updated_user);
                    // if ($error_code != MTRetCode::MT_RET_OK) {
                    //     return redirect()->back()->with("error", "Something went wrong on Updating details" . MTRetCode::GetError($error_code));
                    // }
                }
            }
        }

        $kyc_log = KycLog::where('user_id', $id)->where('callback_payload', 'like', '%GREEN%')->latest()->first();

        $client_notes = ClientNote::where('client_id', $id)
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.client_details', compact(
            'acc_groups',
            'acc_types',
            'ticket_status',
            'ticket_types',
            'user',
            'total_wd',
            'total_ww',
            'pending_ww',
            'wallet_balance',
            'total_balance',
            'live_accounts',
            'demo_accounts',
            'bank_details',
            'kyc_details',
            'ib_details',
            'rm_details',
            'superadmin_details',
            'country_code',
            'clients',
            'userid',
            'countries',
            'IbTotalDeposits',
            'kyc_log',
            'total_ntd',
            'total_ntw',
            'client_notes'
        ));
    }

    public function sendPasswordResetLink(Request $request)
    {
        $email = $request->txtemail;
        $user = User::where('email', $email)->first();
        if ($user) {
            $code = md5(uniqid(rand()));
            $user->update(['emailToken' => $code]);
            $user->update(['email_token_time' => now()]);
            $content =
                '<div>Welcome to ' . htmlspecialchars(settings()['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
                '<div>We have received a request to reset the password associated with your account. If you initiated this request, please click the link below to reset your password:
      </div>';
            $from = settings()['email_from_address'];
            $emailSubject = settings()['admin_title'] . ' - Password Reset';
            $templateVars = [
                'name' => $user->fullname,
                'site_link' => settings()['copyright_site_name_text'] . "/reset-password?id=$user->id&code=$code",
                'btn_text' => "Reset Password",
                'email' => $from,
                "content" => $content,
                "title_right" => "",
                "subtitle_right" => ""
            ];
            $this->mailService->sendEmail($email, $emailSubject, '', '', $templateVars);
            return redirect()->back()->with("success", "An email has been sent to $email with the password reset link.");
        } else {
            return redirect()->back()->with("error", "User not found.");
        }
    }

    public function storeNote(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:aspnetusers,Id',
            'note' => 'required|string|max:5000'
        ]);

        ClientNote::create([
            'client_id' => $request->client_id,
            'admin_id' => Auth::guard('admin')->id(),
            'note' => $request->note
        ]);

        return redirect()->back()->with('success', 'Note added successfully.');
    }

    public function removeTwoFactor(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:aspnetusers,id'
        ]);

        $user = User::findOrFail($request->user_id);

        // Check if 2FA is enabled
        if (!$user->two_factor_secret || !$user->two_factor_confirmed_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Two-factor authentication is not enabled for this client.'
            ], 400);
        }

        try {
            // Disable 2FA using Laravel Fortify's action
            app(DisableTwoFactorAuthentication::class)($user);

            // Log the activity
            activity()
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'admin_email' => auth()->guard('admin')->user()->email,
                    'userRole' => auth()->guard('admin')->user()->userRole,
                    'username' => auth()->guard('admin')->user()->username,
                    'admin_id' => auth()->guard('admin')->user()->id,
                    'client_id' => $user->id,
                    'client_email' => $user->email,
                    'remark' => 'Remove 2FA'
                ])
                ->event('update')
                ->log('Remove Client 2FA');

            return response()->json([
                'status' => 'success',
                'message' => 'Two-factor authentication has been successfully removed for this client.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while removing two-factor authentication: ' . $e->getMessage()
            ], 500);
        }
    }
}
