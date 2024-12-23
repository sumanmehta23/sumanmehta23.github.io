<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\Models\Ib1;
use App\Models\Role;
use App\Models\User;
use App\Models\IbPlan;
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
use App\Models\IbClientList;
use App\Models\TicketStatus;
use App\Models\TotalBalance;
use App\Models\TradeDeposit;
use Illuminate\Http\Request;
use App\Models\WalletDeposit;
use App\Services\MailService;
use App\Models\WalletWithdraw;
use App\Models\ClientBankDetail;
use App\Models\RelationshipManager;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class ClientController extends Controller
{
    protected $mailService;
    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }
    public function index()
    {

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
        $total_clients = DB::table("aspnetusers")->count();

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

        if ($request->has('rmUpdate') && $role && $role->name ="Super Admin") {
            $user_id = $request->input('user_id');
            // $result = DB::table('aspnetusers')
            //     ->select('id')
            //     ->where('email', '=', $email)
            //     ->first();
            // $user_id = $result->id;
            $rm_id = $request->input('rm_id');
            $exists = RelationshipManager::where('user_id', $user_id)->count();
            if ($exists > 0) {
                RelationshipManager::where('user_id', $user_id  )->update(['rm_id' => $rm_id]);
            } else {
                RelationshipManager::create(['user_id' => $user_id, 'rm_id' => $rm_id, 'added_by' => Auth::id() ]);
            }
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
                            'user_id'=> $user_id,
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
                    $lastInsertId = DB::table('aspnetusers')->insertGetId([
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

                    if ($lastInsertId) {
                        // Log the user addition (you need to implement this function if not already available)
                        $logData = [
                            'user_id' => $lastInsertId,
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
        if ($request->has('updateUser')) {
            $email = $request->input('email');
            $fullname = $request->input('fullname');
            $password = $request->input('password');
            $confirmPassword = $request->input('confirm_password');
            $country = $request->input('country');
            $country_code = $request->input('country_code');
            // $number = $request->input('telephone');
            $number = $request->country_code.$request->telephone;
            $user_id = $request->input('id');
            $emailNotification = $request->input('email_notification');

            $countryCode = Country::where('country_name', $request->country)
            ->select('country_code')
            ->first();

            $code = $countryCode ? $countryCode->country_code : 'null';

            if($country_code != $code){
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
                // Update user in the database
                $affectedRows = DB::table('aspnetusers')
                    ->where(DB::raw('id'), $user_id)
                    ->update([
                        'fullname' => $fullname,
                        'password' => $password,
                        'number' => $number,
                        'country_code' => $country_code,
                        'country' => $country
                    ]);

                // If update is successful
                if ($affectedRows > 0) {
                    $updateData = [
                        'user_id' => $user_id,
                        'email' => $email,
                        'type' => 'client_update',
                        'value' => json_encode($request->except(['updateUser', 'password', 'confirm_password']))
                    ];
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
    // Fetch user and related data with eager loading
    $id = request('userId');
    $user = User::with(['ib']) // Eager load ib only
        ->where('id', $id)
        ->firstOrFail();

    // Retrieve all related data in a more efficient manner
    $acc_groups = IBPlan::with('category')
        ->where('status', 1)
        ->get()
        ->groupBy('ib_plan_cat_id'); // Grouping after fetching

    $acc_types = AccountType::with('mt5Group')
        ->whereHas('mt5Group', fn($query) => $query->where('mt5_group_type', 'live'))
        ->get();

    // Efficiently fetch client data without the loop
    $clients = $user->ib ? IbClientList::where('ib1', $user->ib->referral_code)->get()->groupBy('ib1') : [];

    // Calculate wallet-related balances
    $eid = $user->id;
    $total_wd = WalletDeposit::where('user_id', $eid)
        ->whereIn('deposit_type', ['Internal Transfer', 'Crypto Chill'])
        ->where('status', 1)
        ->sum('deposit_amount');

    $total_ww = WalletWithdraw::where('user_id', $eid)
        ->where('withdraw_type', 'Internal Transfer')
        ->where('status', 1)
        ->selectRaw('SUM(withdraw_amount + COALESCE(withdraw_transaction_fee, 0)) as total')
        ->value('total') ?? 0;

    $pending_ww = WalletWithdraw::where('user_id', $eid)
        ->where('status', 0)
        ->selectRaw('SUM(withdraw_amount + COALESCE(withdraw_transaction_fee, 0)) as total')
        ->value('total') ?? 0;

    $wallet_balance = (float)$total_wd - (float)$total_ww - (float)$pending_ww;

    // Calculate total balance
    $total_balance = TotalBalance::where('user_id', $eid)
        ->selectRaw('SUM(deposit_amount) as deposit_amount, SUM(trading_deposited) as trading_deposited, SUM(trading_withdrawal) as trading_withdrawal, SUM(withdraw_amount) as withdraw_amount')
        ->first();

    // Fetch live accounts, bank details, KYC details, IB details, RM details, and country info
    $live_accounts = Account::where('user_id', $eid)
        ->where('demo', false)
        ->orderBy('id', 'desc')
        ->get();

    $bank_details = DB::table('clientbankdetails')->where('userId', $eid)->first();
    $kyc_details = DB::table('kyc_update')->where('email', $eid)->get();
    $ib_details = DB::table('ib1')
        ->leftJoin('ib_wallet', 'ib1.user_id', '=', 'ib_wallet.user_id')
        ->leftJoin('account_types as ac', 'ac.ac_index', '=', 'ib1.acc_type')
        ->select('ib1.*', DB::raw('SUM(ib_wallet.ib_wallet) as deposit'), DB::raw('SUM(ib_wallet.ib_withdraw) as withdraw'), 'ac.ac_name')
        ->where('ib1.status', 1)
        ->where('ib1.email', $user->email)
        ->groupBy('ib1.email')
        ->havingRaw('COUNT(ib1.email) > 0')
        ->first();

    $rm_details = DB::table('relationship_manager as rm')
        ->leftJoin('emplist as emp', 'rm.rm_id', '=', 'emp.email')
        ->select('emp.client_index', 'emp.username', 'rm.*')
        ->where('rm.user_id', $eid)
        ->first();

    $superadmin_details = DB::table('emplist')->where('role_id', 1)->first();
    $country_code = DB::table('countries')->where('country_name', $user->country)->first();

    // Fetch ticket status and types with caching
    $ticket_status = Cache::remember('ticket_status', now()->addMinutes(10), fn() => DB::table('ticket_status')->get()->toArray());
    $ticket_types = Cache::remember('ticket_types', now()->addMinutes(10), fn() => DB::table('ticket_types')->get()->toArray());

    return view('admin.client_details', compact(
        'ticket_status',
        'ticket_types',
        'user',
        'acc_groups',
        'acc_types',
        'wallet_balance',
        'total_balance',
        'live_accounts',
        'bank_details',
        'kyc_details',
        'ib_details',
        'rm_details',
        'superadmin_details',
        'country_code',
        'total_wd',
        'total_ww',
        'clients'
    ));
}

    public function sendPasswordResetLink(Request $request)
    {
        $email = $request->txtemail;
        $user = User::where('email', $email)->first();
        if ($user) {
            $code = md5(uniqid(rand()));
            $user->update(['emailToken' => $code]);
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
}
