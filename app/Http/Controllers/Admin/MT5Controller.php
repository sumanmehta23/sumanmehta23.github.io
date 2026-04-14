<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Enums\PlatformEnum;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\Promocode;
use App\Models\AccountType;
use App\MT5\MTEnDealAction;
use App\Models\TotalBalance;
use App\Models\TradeDeposit;
use App\Services\UniversalMT5Service;
use App\Services\X9Service;

use Illuminate\Http\Request;
use App\MT5\MTProtocolConsts;
use App\Helpers\AccountHelper;
use Illuminate\Validation\Rule;
use App\Models\BonusTransaction;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Trade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\MailService as MailService;

class MT5Controller extends Controller
{
    protected $api;
    protected $mailService;
    protected $mt5Service;
    public function __construct(MailService $mailService, UniversalMT5Service $mt5Service, X9Service $x9Service)
    {
        $this->mt5Service = $mt5Service;
        // MT5 connection deferred - use ensureMT5Connection() in methods that need it
        $this->mailService = $mailService;
        $this->x9Service = $x9Service;
        // $this->api = $api;

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
        return view('admin.promocode', []);
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
                'min_deposit' => $promocode->min_deposit,
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
        $promocode->min_deposit = $request->min_deposit ?? '';
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
                'min_deposit'      => $request->min_deposit ?? '',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Promocode added successfully',
            ]);
        } catch (\Throwable $e) {
            // log the actual error for debugging
            Log::error('Promocode create error: ' . $e->getMessage());

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
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server');
        }

        if ($request->has(['code', 'account_type'])) {
            $code = $request->input('code');
            $account_type = $request->input('account_type');
            $leverage = $request->input('leverage');

            // Get the account to determine platform
            $account = Account::with('user')->where('code', $code)->first();

            if (!$account) {
                return redirect()->back()->with('error', 'Account not found');
            }

            // Fetch account type details
            $acc = DB::table('account_types')
                ->where('id', $account_type)
                ->first();

            if (!$acc) {
                return redirect()->back()->with('error', 'Account type not found');
            }

            // Handle based on platform
            if ($account->platform === PlatformEnum::X9->value) {
                // Handle X9 platform
                return $this->updateX9AccountDetails($account, $acc, $leverage, $code, $account_type);
            } else {
                // Handle MT5 platform (existing logic)
                return $this->updateMT5AccountDetails($account, $acc, $leverage, $code, $account_type);
            }
        }

        return redirect()->back()->with('error', 'Missing required parameters');
    }

    private function updateX9AccountDetails($account, $acc, $leverage, $code, $account_type)
    {
        try {
            // For X9, we need to get the client group ID from the account type
            // The form sends account_type_id, so we need to look up the x9_group_id from account_types table
            $accountType = \App\Models\AccountType::find($account_type);
            $x9GroupId = $accountType ? $accountType->x9_group_id : null;

            // Only update group if x9_group_id is mapped
            if ($x9GroupId) {
                $groupResponse = $this->x9Service->updateUserGroup(intval($code), $x9GroupId);
                if (!$groupResponse['status']) {
                    return redirect()->back()->with('error', 'Failed to update group in X9: ' . $groupResponse['message']);
                }
            } else {
                // Log that this account type is not mapped to X9
                Log::info('Account type not mapped to X9 group', ['account_type_id' => $account_type, 'code' => $code]);
            }

            // Update leverage in X9
            $leverageResponse = $this->x9Service->updateUserLeverage(intval($code), $leverage);
            if (!$leverageResponse['status']) {
                return redirect()->back()->with('error', 'Failed to update leverage in X9: ' . $leverageResponse['message']);
            }

            // Update in local database
            DB::table('accounts')
                ->where('code', $code)
                ->update([
                    'leverage' => $leverage,
                    'account_type_id' => $account_type
                ]);

            // Log activity
            activity()
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'admin_email' => auth()->guard('admin')->user()->email,
                    'userRole' => auth()->guard('admin')->user()->userRole,
                    'username' => auth()->guard('admin')->user()->username,
                    'admin_id' => auth()->guard('admin')->user()->id,
                    'code' => $code,
                    'leverage' => $leverage,
                    'account_type_id' => $account_type,
                    'platform' => PlatformEnum::X9->value,
                    'x9_group_id' => $x9GroupId,
                    'group_updated' => $x9GroupId ? true : false,
                    'remark' => 'CRM Update Group Leverage (X9)'
                ])
                ->event('update')
                ->log('CRM Update Group Leverage (X9)');

            $updateMessage = $x9GroupId ? "X9 Account Details Successfully Updated" : "X9 Leverage Updated (Group unchanged - not mapped)";
            return redirect()->back()->with("success", $updateMessage);
        } catch (\Exception $e) {
            Log::error('X9 Account Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating X9 account: ' . $e->getMessage());
        }
    }

    private function updateMT5AccountDetails($account, $acc, $leverage, $code, $account_type)
    {
        // Existing MT5 logic
        $trade_user = NULL;
        $this->api->UserGet($code, $trade_user);

        if (($error_code = $this->api->UserGet($code, $trade_user)) != MTRetCode::MT_RET_OK) {
            return redirect()->back()->with('error', 'Something went wrong on Updating details' . MTRetCode::GetError($error_code));
        }

        $referral = $account->user->ib1;
        $groupCode = $acc->ac_group;
        $account_type_id = $acc->id;

        $trade_user->Group = $groupCode;
        $trade_user->Leverage = $leverage;
        info("Updated User Details ", ['code' => $code, 'group' => $groupCode, 'leverage' => $leverage]);

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
                    'userRole' => auth()->guard('admin')->user()->userRole,
                    'username' => auth()->guard('admin')->user()->username,
                    'admin_id' => auth()->guard('admin')->user()->id,
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

    public function updatePassword(Request $request)
    {
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server');
        }

        if ($request->has(['code', 'password_type'])) {
            $login = $request->input('code');
            $pass_type = $request->input('password_type');
            $new_password = $request->input('password');
            $type = $request->input('type', 'live'); // default to 'live' if 'type' is not provided

            // Get account to check platform
            $account = Account::where('code', $login)->first();
            if (!$account) {
                return redirect()->back()->with('error', 'Account not found');
            }

            // Handle password update based on platform
            if ($account->platform === PlatformEnum::X9->value) {
                return $this->updateX9Password($account, $login, $pass_type, $new_password);
            } else {
                return $this->updateMT5Password($account, $login, $pass_type, $new_password);
            }
        }
    }

    private function updateX9Password($account, $login, $pass_type, $new_password)
    {
        try {
            // Map password types for X9 API
            $x9PasswordType = $pass_type === 'main' ? 'master' : $pass_type;

            // Update password in X9
            $response = $this->x9Service->resetUserPassword(intval($login), $x9PasswordType, $new_password);

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
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'admin_email' => auth()->guard('admin')->user()->email,
                    'userRole' => auth()->guard('admin')->user()->userRole,
                    'username' => auth()->guard('admin')->user()->username,
                    'admin_id' => auth()->guard('admin')->user()->id,
                    'code' => $login,
                    'new_password' => $new_password,
                    'platform' => PlatformEnum::X9->value,
                    'password_type' => $x9PasswordType,
                    'remark' => 'CRM Update ' . ucfirst($pass_type) . ' Password (X9)'
                ])
                ->event('update')
                ->log('CRM Update ' . ucfirst($pass_type) . ' Password (X9)');

            $passwordTypeName = $pass_type === 'main' ? 'Master' : ucfirst($pass_type);
            return redirect()->back()->with('success', "Your {$passwordTypeName} Password Successfully Updated");
        } catch (\Exception $e) {
            Log::error('X9 Password Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating X9 password: ' . $e->getMessage());
        }
    }

    private function updateMT5Password($account, $login, $pass_type, $new_password)
    {
        // Change main password
        if ($pass_type == 'main') {
            if (($error_code = $this->api->UserPasswordChange($login, $new_password, MTProtocolConsts::WEB_VAL_USER_PASS_MAIN)) != MTRetCode::MT_RET_OK) {
                return redirect()->back()->with("error", 'Something went wrong on fetching details' . MTRetCode::GetError($error_code));
            } else {
                $account->trader_password = $new_password;
                $account->save(); // Save will apply casting and encrypt the password

                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'admin_email' => auth()->guard('admin')->user()->email,
                        'userRole' => auth()->guard('admin')->user()->userRole,
                        'username' => auth()->guard('admin')->user()->username,
                        'admin_id' => auth()->guard('admin')->user()->id,
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
                $account->invester_password = $new_password;
                $account->save(); // Save will apply casting and encrypt the password

                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'admin_email' => auth()->guard('admin')->user()->email,
                        'userRole' => auth()->guard('admin')->user()->userRole,
                        'username' => auth()->guard('admin')->user()->username,
                        'admin_id' => auth()->guard('admin')->user()->id,
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

    public function depositToAccount(Request $request)
    {
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server');
        }

        $eid = $request->input('email');
        $user_id = $request->input('client_id');
        $user = User::find($user_id);
        $code = $request->input('code');
        $account = Account::where('code', $code)->where('user_id', $user_id)->first();
        if ($request->has('deposit_to_account')) {
            $amount = str_replace(',', '', $request->input('amount'));
            $description = $request->input('description');
            $deposit_type = 'CRM';
            $email = $eid;
            $deposit_currency = 'USD';
            $login = $code;
            $comment = 'CRM Deposited';

            // Handle based on platform
            if ($account->platform === PlatformEnum::X9->value) {
                // Handle X9 deposit
                $response = $this->x9Service->manageBalance(
                    intval($login),
                    'balance', // operation_type
                    'Deposit', // transaction_type
                    floatval($amount),
                    $comment
                );

                if (!$response['status']) {
                    return redirect()->back()->with('error', 'X9 Deposit Failed: ' . $response['message']);
                }
            } else {
                // Handle MT5 deposit (existing logic)
                $ticket = null;
                if (($error_code = $this->api->TradeBalance($login, MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
                    return redirect()->back()->with('error', MTRetCode::GetError($error_code));
                }
            }

            // Create deposit record in database (same for both platforms)
            $tradeDeposit = TradeDeposit::create([
                'user_id' => $user->id,
                'account_id' => $account->id,
                'transaction_id' => uniqid(),
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
                    'userRole' => auth()->guard('admin')->user()->userRole,
                    'username' => auth()->guard('admin')->user()->username,
                    'admin_id' => auth()->guard('admin')->user()->id,
                    'client_id' => $user->id,
                    'client_email' => $email,
                    'deposit_amount' => $amount,
                    'code' => $code,
                    'account_id' => $account->id,
                    'platform' => $account->platform,
                    'remark' => 'CRM Deposit'
                ])
                ->event('create')
                ->log('CRM Deposit');

            $settings = settings();
            $emailSubject = $settings['admin_title'] . ' - Fund Deposit';
            $content = '<div>We are pleased to inform you that funds have been successfully deposited into your account.</div>';
            $templateVars = [
                'name' => $user->fullname,
                'site_link' => settings()['copyright_site_name_text'],
                "btn_text" => "Go To Dashboard",
                'email' => settings()['email_from_address'],
                "content" => $content,
                'amount' => $amount,
                'code' => $code,
                'date' => now()->format('Y-m-d H:i:s'),
                'type' => $deposit_type,
                "title_right" => "Fund",
                "subtitle_right" => "Deposit"
            ];
            $this->mailService->sendEmail($email, $emailSubject, '', '', $templateVars);
            return redirect()->back()->with('success', 'Trade Deposit Successful');
        }
    }

    public function softDeleteAccount(Request $request)
    {

        $account = Account::where('id', $request->input('account_id'))->first();
        $login = $account->code;

        $this->ensureMT5Connection();

        $settings = settings();
        // Validate platform selection
        $request->validate([
            'platform' => 'required|in:mt5,x9',
        ]);

        $platform = $request->input('platform');

        if ($platform === PlatformEnum::X9->value) {
            $response = $this->x9Service->getUserDetails($login);
            if ($response['data']['trading_account']['client_group_type_id'] != 1) {
                if ($response['data']['balance']['balance'] > 0) {
                    return redirect()->back()->with('error', 'Account has balance, please transfer amount to another account.');
                }
            }else{
                return redirect()->back()->with('error', 'Demo accounts cannot be deleted.');
            }

            // X9 deletion logic disableAccount
            $response = $this->x9Service->disableAccount($account->code);
            if (!$response['status']) {
                return redirect()->back()->with('error', 'X9 Account Soft Deletion Failed: ' . $response['message']);
            }
            // Delete from local database
            $account->deletion_type = 'soft';
            $account->save();
            $account->delete();
            return redirect()->back()->with('success', 'X9 Account Soft Deleted Successfully');
        } elseif ($platform === PlatformEnum::MT5->value) {
            $trade_user = NULL;
            if (($error_code = $this->api->UserGet($login, $trade_user) != MTRetCode::MT_RET_OK)) {
                return redirect()->back()->with('error', 'MT5 Account Deletion Failed: ' . MTRetCode::GetError($error_code));
            }

            if ($trade_user->Balance > 0) {
                if ($account->demo != 1) {
                    return redirect()->back()->with('error', 'Account has balance, please transfer amount to another account.');
                }
            }
            // MT5 deletion logic
            $error_code = $this->api->DisableTrading($login);
            if (!$error_code['status']) {
                return redirect()->back()->with('error', 'MT5 Account Soft Deletion Failed during cleanup: ' . $error_code['message']);
            }
            // Delete from local database
            $account->deletion_type = 'soft';
            $account->save();
            $account->delete();
            return redirect()->back()->with('success', 'MT5 Account Soft Deleted Successfully');
        }
    }

    public function deleteAccount(Request $request)
    {

        $account = Account::withTrashed()->with('trades')->where('id', $request->input('account_id'))->first();
        $login = $account->code;

        $this->ensureMT5Connection();

        $settings = settings();
        // Validate platform selection
        $request->validate([
            'platform' => 'required|in:mt5,x9',
        ]);

        $platform = $request->input('platform');

        if ($platform === PlatformEnum::X9->value) {
            $response = $this->x9Service->getUserDetails($login);

            if ($response['data']['trading_account']['client_group_type_id'] != 1) {
                if ($response['data']['balance']['balance'] > 0) {
                    return redirect()->back()->with('error', 'Account has balance, please transfer amount to another account.');
                }
            }else{
                return redirect()->back()->with('error', 'Demo accounts cannot be deleted.');
            }

            // X9 deletion logic disableAccount
            $response = $this->x9Service->disableAccount($account->code);
            if (!$response['status']) {
                return redirect()->back()->with('error', 'X9 Account Deletion Failed: ' . $response['message']);
            }
            // Delete from local database
            $account->deletion_type = 'delete';
            $account->save();
            $account->delete();
            return redirect()->route('admin.dashboard')->with('success', 'X9 Account Deleted Successfully');
        } elseif ($platform === PlatformEnum::MT5->value) {
            $trade_user = NULL;
            if (($error_code = $this->api->UserGet($login, $trade_user) != MTRetCode::MT_RET_OK)) {
                return redirect()->back()->with('error', 'MT5 Account Deletion Failed: ' . MTRetCode::GetError($error_code));
            }

            if ($trade_user->Balance > 0) {
                if ($account->demo != 1) {
                    return redirect()->back()->with('error', 'Account has balance, please transfer amount to another account.');
                }
            }

            if ($account->tradeDeposits->count() > 0 || $account->tradeWithdrawals->count() > 0) {
                return redirect()->back()->with('error', 'Account has trade deposits or withdrawals, cannot delete.');
            }

            if($account->trades->count() > 0){
                return redirect()->back()->with('error', 'Account has trades associated, cannot delete.');
            }

            // MT5 deletion logic
            $error_code = $this->api->DisableTradingOrDeleteUser($login);
            if (!$error_code['status']) {
                return redirect()->back()->with('error', 'MT5 Account Deletion Failed during cleanup: ' . $error_code['message']);
            }
            // Delete from local database
            $account->deletion_type = 'delete';
            $account->save();
            $account->delete();
            return redirect()->route('admin.dashboard')->with('success', 'MT5 Account Deleted Successfully');
        }
    }

    public function restoreAccount(Request $request)
    {
        $account = Account::withTrashed()->with('trades')->where('id', $request->input('account_id'))->first();
        $login = $account->code;
        $this->ensureMT5Connection();

        $settings = settings();
        // Validate platform selection
        $request->validate([
            'platform' => 'required|in:mt5,x9',
        ]);

        $platform = $request->input('platform');

        if ($platform === PlatformEnum::X9->value) {
            $response = $this->x9Service->getUserDetails($login);

            if ($response['data']['trading_account']['client_group_type_id'] != 1) {
                if ($response['data']['balance']['balance'] > 0) {
                    return redirect()->back()->with('error', 'Account has balance, please transfer amount to another account.');
                }
            }else{
                return redirect()->back()->with('error', 'Demo accounts cannot be Restoration.');
            }

            // X9 deletion logic enableAccount
            $response = $this->x9Service->enableAccount($account->code);
            if (!$response['status']) {
                return redirect()->back()->with('error', 'X9 Account Restoration Failed: ' . $response['message']);
            }
            // Delete from local database
            $account->deletion_type = null;
            $account->save();
            $account->restore();
            return redirect()->back()->with('success', 'X9 Account Restored Successfully');
        } elseif ($platform === PlatformEnum::MT5->value) {
            // $trade_user = NULL;
            // if (($error_code = $this->api->UserGet($login, $trade_user) != MTRetCode::MT_RET_OK)) {
            //     return redirect()->back()->with('error', 'MT5 Account Restoration Failed. Account not present in mt5 server. '. MTRetCode::GetError($error_code));
            // }
            // if ($trade_user) {
                // MT5 deletion logic
                if($account->deletion_type === 'archive'){
                    $success = $this->api->restoreUser($login);
                    if (!$success) {
                        return redirect()->back()->with('error', 'MT5 Account Restoring Failed');
                    }
                }elseif($account->deletion_type === 'soft'){
                    $success = $this->api->EnableTrading($login);
                    if (!$success) {
                        return redirect()->back()->with('error', 'MT5 Account Restoring Failed');
                    }
                }
            // }
            // Delete from local database
            $account->deletion_type = null;
            $account->save();
            $account->restore();
            return redirect()->back()->with('success', 'MT5 Account Restored Successfully');
        }
    }

    public function archiveAccount(Request $request)
    {
        $account = Account::where('id', $request->input('account_id'))->first();
        $login = $account->code;

        $this->ensureMT5Connection();

        // Validate platform selection
        $request->validate([
            'platform' => 'required|in:' . implode(',', PlatformEnum::all()),
        ]);

        $platform = $request->input('platform');
        if ($platform === PlatformEnum::X9->value) {
            $response = $this->x9Service->getUserDetails($login);

            if ($response['data']['trading_account']['client_group_type_id'] != 1) {
                if ($response['data']['balance']['balance'] > 0) {
                    return redirect()->back()->with('error', 'Account has balance, please transfer amount to another account before archiving.');
                }
            } else {
                return redirect()->back()->with('error', 'Demo accounts cannot be archived.');
            }

            // X9 archive logic - disable the account
            $response = $this->x9Service->disableAccount($account->code);
            if (!$response['status']) {
                return redirect()->back()->with('error', 'X9 Account Archive Failed: ' . $response['message']);
            }

            // Archive in local database
            $account->deletion_type = 'archive';
            $account->save();
            $account->delete(); // Soft delete to hide it
            return redirect()->back()->with('success', 'X9 Account Archived Successfully');
        } elseif ($platform === PlatformEnum::MT5->value) {

            $trade_user = NULL;

            if (($error_code = $this->api->UserGet($login, $trade_user) != MTRetCode::MT_RET_OK)) {
                return redirect()->back()->with('error', 'MT5 Account Archive Failed: ' . MTRetCode::GetError($error_code));
            }

            if ($trade_user->Balance > 0) {
                if ($account->demo != 1) {
                    return redirect()->back()->with('error', 'Account has balance, please transfer amount to another account before archiving.');
                }
            }

            // MT5 archive logic - disable trading
            $success = $this->api->archiveUser($login);
            if ($success != true) {
                return redirect()->back()->with('error', 'MT5 Account Archive Failed during cleanup');
            }

            // Archive in local database
            $account->deletion_type = 'archive';
            $account->save();
            $account->delete(); // Soft delete to hide it
            return redirect()->back()->with('success', 'MT5 Account Archived Successfully');
        }
    }

    public function depositToCellexpertAccount(Request $request)
    {
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server');
        }

        $eid = $request->input('email');
        $user_id = $request->input('client_id');
        $user = User::find($user_id);
        $code = $request->input('code');
        $account = Account::where('code', $code)->where('user_id', $user_id)->first();

        if ($request->has('deposit_to_account')) {
            $amount = str_replace(',', '', $request->input('amount'));
            $description = $request->input('description');
            $deposit_type = 'CRM';
            $email = $eid;
            $deposit_currency = 'USD';
            $login = $code;
            $comment = 'CRM Deposited';

            // Handle based on platform
            if ($account->platform === PlatformEnum::X9->value) {
                // Handle X9 deposit
                $response = $this->x9Service->manageBalance(
                    intval($login),
                    'balance', // operation_type
                    'Deposit', // transaction_type
                    floatval($amount),
                    $comment
                );

                if (!$response['status']) {
                    return redirect()->back()->with('error', 'X9 Deposit Failed: ' . $response['message']);
                }
            } else {
                // Handle MT5 deposit (existing logic)
                $ticket = null;
                if (($error_code = $this->api->TradeBalance($login, MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
                    return redirect()->back()->with('error', MTRetCode::GetError($error_code));
                }
            }
            // Create deposit record in database (same for both platforms)
            $tradeDepositDate = [
                'user_id' => $user->id,
                'account_id' => $account->id,
                'transaction_id' => uniqid(),
                'email' => $email,
                'code' => $code,
                'deposit_amount' => $amount,
                'deposit_type' => $deposit_type,
                'status' => 1,
                'admin_remark' => $description,
                'deposit_currency' => $deposit_currency,
                'created_by' => session('alogin'),
            ];

            if ($user->cxd) {
                $tradeDepositDate['cell_tracking'] = 1;
            }

            $tradeDeposit = TradeDeposit::create($tradeDepositDate);

            $transid = "TDID" . str_pad($tradeDeposit->id, 4, '0', STR_PAD_LEFT);

            // Store in total_balance table
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
                    'userRole' => auth()->guard('admin')->user()->userRole,
                    'username' => auth()->guard('admin')->user()->username,
                    'admin_id' => auth()->guard('admin')->user()->id,
                    'client_id' => $user->id,
                    'client_email' => $email,
                    'deposit_amount' => $amount,
                    'code' => $code,
                    'account_id' => $account->id,
                    'platform' => $account->platform,
                    'remark' => 'CRM Deposit'
                ])
                ->event('create')
                ->log('CRM Deposit');

            $settings = settings();
            $emailSubject = $settings['admin_title'] . ' - Fund Deposit';
            $content = '<div>We are pleased to inform you that funds have been successfully deposited into your account.</div>';
            $templateVars = [
                'name' => $user->fullname,
                'site_link' => settings()['copyright_site_name_text'],
                "btn_text" => "Go To Dashboard",
                'email' => settings()['email_from_address'],
                "content" => $content,
                'amount' => $amount,
                'code' => $code,
                'date' => now()->format('Y-m-d H:i:s'),
                'type' => $deposit_type,
                "title_right" => "Fund",
                "subtitle_right" => "Deposit"
            ];
            $this->mailService->sendEmail($email, $emailSubject, '', '', $templateVars);
            return redirect()->back()->with('success', 'Trade Deposit Successful');
        }
    }

    public function bonusToAccount(Request $request)
    {
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server');
        }

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
        $account = Account::where('code', $code)->where('user_id', $user_id)->first();

        if (!$account) {
            return redirect()->back()->with('error', 'Account not found');
        }

        if ($request->has('bonus_to_account')) {
            $amount = $request->input('amount');
            $description = $request->input('description');
            $type = $request->input('type');
            $deposit_type = $type === 'in' ? 'Bonus In' : 'Bonus Out';
            $amount = $type === 'in' ? $amount : -1 * $amount;
            $email = $eid;
            $deposit_currency = 'USD';
            $login = $code;
            $comment = $type === 'in' ? 'Bonus Deposit' : 'Bonus Withdraw';

            // Handle based on platform
            if ($account->platform === PlatformEnum::X9->value) {
                // Handle X9 bonus using the correct bonus operation
                $response = $this->x9Service->manageBonus(
                    intval($login),
                    $type, // 'in' or 'out'
                    abs(floatval($amount)), // Always send positive amount
                    $comment
                );

                if (!$response['status']) {
                    return redirect()->back()->with('error', 'X9 Bonus Operation Failed: ' . $response['message']);
                }
            } else {
                // Handle MT5 bonus (existing logic)
                $ticket = null;
                $loginss = [];

                if (in_array($login, $loginss)) {
                    $operation = MTEnDealAction::DEAL_BONUS;
                } else {
                    $operation = MTEnDealAction::DEAL_BALANCE;
                }

                if (($error_code = $this->api->TradeBalance($login, $operation, $amount, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
                    return redirect()->back()->with('error', MTRetCode::GetError($error_code));
                }
            }

            // Create bonus record in database (same for both platforms)
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
                    'userRole' => auth()->guard('admin')->user()->userRole,
                    'username' => auth()->guard('admin')->user()->username,
                    'admin_id' => auth()->guard('admin')->user()->id,
                    'client_id' => $user->id,
                    'client_email' => $email,
                    'bonus_amount' => $amount,
                    'bonus_type' => $comment,
                    'code' => $code,
                    'account_id' => $account->id,
                    'platform' => $account->platform,
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
                            <p><b>Bonus Date: </b>' . date("Y-m-d H:i:s") . '</p>';

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
    public function creditBonusToAccount(Request $request)
    {
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server');
        }

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
        $account = Account::where('code', $code)->where('user_id', $user_id)->first();

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

            // Handle based on platform
            $success = false;
            if ($account->platform === PlatformEnum::X9->value) {
                // Handle X9 bonus credit using the correct bonus operation
                $response = $this->x9Service->manageBonus(
                    intval($login),
                    $type, // 'in' or 'out'
                    abs($amount), // amount (always positive for X9)
                    $comment
                );

                if ($response && $response['status']) {
                    $success = true;
                } else {
                    return redirect()->back()->with('error', $response['message'] ?? 'X9 bonus operation failed');
                }
            } else {
                // Handle MT5 bonus credit
                if (($error_code = $this->api->TradeBalance($login, MTEnDealAction::DEAL_BONUS, $amount, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
                    return redirect()->back()->with('error', MTRetCode::GetError($error_code));
                } else {
                    $success = true;
                }
            }

            if ($success) {
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
                        'userRole' => auth()->guard('admin')->user()->userRole,
                        'username' => auth()->guard('admin')->user()->username,
                        'admin_id' => auth()->guard('admin')->user()->id,
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
                                <p><b>Bonus Date: </b>' . date("Y-m-d H:i:s") . '</p>';

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
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server');
        }

        $eid = $request->input('email');
        $user_id = $request->input('client_id');
        $user = User::find($user_id);
        $code = $request->input('code');
        $account = Account::where('code', $code)->where('user_id', $user_id)->first();
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

            // Handle based on platform
            if ($account->platform === PlatformEnum::X9->value) {
                // Handle X9 withdrawal
                $response = $this->x9Service->manageBalance(
                    intval($login),
                    'balance', // operation_type
                    'Withdrawal', // transaction_type
                    floatval($amount), // Always send positive amount
                    $comment
                );

                if (!$response['status']) {
                    return redirect()->back()->with('error', 'X9 Withdrawal Failed: ' . $response['message']);
                }
            } else {
                // Handle MT5 withdrawal (existing logic)
                $ticket = null;
                if (($error_code = $this->api->TradeBalance($login, MTEnDealAction::DEAL_BALANCE, $tw_amount, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
                    return redirect()->back()->with("error", MTRetCode::GetError($error_code));
                }
            }

            // Create withdrawal record in database (same for both platforms)
            $deposit_details = TradeWithdrawals::create([
                'email' => $email,
                'user_id' => $user->id,
                'account_id' => $account->id,
                'code' => $account->code,
                'withdraw_to' => null,
                'withdrawal_amount' => $amount,
                'withdraw_type' => $withdraw_type,
                'admin_remark' => $description,
                'Status' => 1,
                'created_by' => session('alogin')
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
                    'withdrawal_amount' => $amount,
                    'code' => $code,
                    'account_id' => $account->id,
                    'platform' => $account->platform,
                    'remark' => 'CRM Withdraw'
                ])
                ->event('create')
                ->log('CRM Withdraw');

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

    public function withdrawFromCellexpertAccount(Request $request)
    {
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server');
        }

        $eid = $request->input('email');
        $user_id = $request->input('client_id');
        $user = User::find($user_id);
        $code = $request->input('code');
        $account = Account::where('code', $code)->where('user_id', $user_id)->first();

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

            // Handle based on platform
            if ($account->platform === PlatformEnum::X9->value) {
                // Handle X9 withdrawal
                $response = $this->x9Service->manageBalance(
                    intval($login),
                    'balance', // operation_type
                    'Withdrawal', // transaction_type
                    floatval($amount), // Always send positive amount
                    $comment
                );

                if (!$response['status']) {
                    return redirect()->back()->with('error', 'X9 Withdrawal Failed: ' . $response['message']);
                }
            } else {
                // Handle MT5 withdrawal (existing logic)
                $ticket = null;
                if (($error_code = $this->api->TradeBalance($login, MTEnDealAction::DEAL_BALANCE, $tw_amount, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
                    return redirect()->back()->with("error", MTRetCode::GetError($error_code));
                }
            }

            // Common data for TradeWithdrawals
            $withdrawData = [
                'email' => $email,
                'user_id' => $user->id,
                'account_id' => $account->id,
                'code' => $account->code,
                'withdraw_to' => null,
                'withdrawal_amount' => $amount,
                'withdraw_type' => $withdraw_type,
                'admin_remark' => $description,
                'Status' => 1,
                'created_by' => session('alogin'),
                'code' => $code,
            ];

            // Add cell_tracking only for Cellexpert accounts (cxd)
            if ($user->cxd) {
                $withdrawData['cell_tracking'] = 1;
            }

            $deposit_details = TradeWithdrawals::create($withdrawData);

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
                    'withdrawal_amount' => $amount,
                    'code' => $code,
                    'account_id' => $account->id,
                    'platform' => $account->platform,
                    'remark' => 'CRM Withdraw'
                ])
                ->event('create')
                ->log('CRM Withdraw');

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

        $account = Account::withTrashed()->where('id', $id)->with(['accountType', 'user', 'BonusTransaction'])->first();

        $trade = Trade::where('code', $account->code)->get();
        $total_profit = $trade->sum('profit');
        $total_comission = $trade->sum('commission');
        $total_swap = $trade->sum('swap');
        $total_trades = count($trade);

        if ($account) {
            $code = $account->code;
        } else {
            $code = '';
        }

        if ($account->demo == false) {
            AccountHelper::updateLiveAndDemoAccounts($account->user_id);
            $type = "live";
        } else {
            $type = "demo";
        }
        $account = Account::withTrashed()->where('id', $id)->with(['accountType', 'user', 'BonusTransaction'])->first();

        if (!$account) {
            alert()->error("The MT5 account does not exist or has been deleted. Please try again.");
            return redirect("/admin/dashboard");
        }

        // Total approved deposits
        // $total_deposit = DB::table('trade_deposit')
        //     ->where(DB::raw('code'), $account->code)
        //     ->where('status', 1)
        //     ->sum('deposit_amount');
        $total_deposit = TradeDeposit::withTrashed()->where('account_id', $account->id)
            ->where('status', 1)
            ->sum('deposit_amount');

        // Total unapproved deposits
        $unapproved_deposit = TradeDeposit::withTrashed()->where('account_id', $account->id)
            ->where('status', '!=', 1)
            ->sum('deposit_amount');

        // Total approved withdrawals
        $total_withdrawal = TradeWithdrawals::withTrashed()->where('account_id', $account->id)
            ->where('status', 1)
            ->sum('withdrawal_amount');

        // Total unapproved withdrawals
        $unapproved_withdrawal = TradeWithdrawals::withTrashed()->where('account_id', $account->id)
            ->where('status', '!=', 1)
            ->sum('withdrawal_amount');

        $bonus_trans = BonusTransaction::withTrashed()->where('status', 1)
            ->where("account_id", $account->id)
            ->get();
        $account_types = AccountType::where('status', 1)->get();

        // Handle platform-specific account data retrieval
        $accountHelper = null;
        if ($account->platform === PlatformEnum::X9->value) {
            // For X9 accounts, use X9Service to get account details
            $x9Service = app(\App\Services\X9Service::class);
            $response = $x9Service->getUserDetails($account->code);
            if ($response['status']) {
                $x9AccountData = $response['data'];
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

                $openTrades = $x9Service->getOpenTrades($account->code);
                $openTradesCount = $openTrades['data']['total_positions'] ?? 0;
                $closedTrades = $x9Service->getClosedTradesByAccount($account->code);
                $closedTradesCount = $closedTrades['data']['summary']['total_trades'] ?? 0;
                $profit = $closedTrades['data']['summary']['net_profit_loss'] ?? 0;
                $commission = collect($closedTrades['data']['trades'])->sum('commission') ?? 0;

                $totalTrades = $openTradesCount + $closedTradesCount;
                // Update account with fresh data from X9
                try {
                    // Extract balance data from the correct nested structure


                    $balance = floatval($balanceData['balance'] ?? $account->balance);
                    $credit = floatval($balanceData['credit'] ?? 0);
                    $bonus = floatval($balanceData['bonus'] ?? 0);
                    $equity = floatval($balanceData['equity'] ?? ($balance + $credit + $bonus));
                    $marginFree = floatval($balanceData['free_margin'] ?? 0);
                    $margin = floatval($balanceData['margin'] ?? 0);
                    $marginLevel = $margin > 0 ? round(($equity / $margin) * 100, 2) : 0;

                    $account->update([
                        'balance' => $balance,
                        'credit' => $credit,
                        'equity' => $equity,
                        'margin_free' => $marginFree,
                        'margin_level' => $marginLevel,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to update X9 account in admin panel: ' . $e->getMessage());
                }
                $accountHelper = (object) [
                    'Balance' => floatval($balanceData['balance'] ?? $account->balance),
                    'Credit' => floatval($balanceData['credit'] ?? 0),
                    'Bonus' => floatval($balanceData['bonus'] ?? 0),
                    'Equity' => floatval($balanceData['equity'] ?? $account->balance),
                    'Margin' => floatval($balanceData['margin'] ?? 0),
                    'MarginFree' => floatval($balanceData['free_margin'] ?? 0),
                    'MarginLevel' => floatval($balanceData['margin_level'] ?? 0),
                    'Profit' => $profit,
                    'Commission' => $commission,
                    'TotalTrades' => $totalTrades,
                ];

                // Get X9 group name for display
                $x9GroupName = $x9AccountData['trading_account']['client_group_name'] ?? 'Standard';
                $x9Leverage = $x9AccountData['trading_account']['leverage'] ?? $account->leverage ?? '1:100';
            } else {
                $x9GroupName = null;
                $x9Leverage = null;
            }
        } else {
            // For MT5 accounts, use the existing AccountHelper
            $accountHelper = AccountHelper::getAccount($account->code);
            $x9GroupName = null;
            $x9Leverage = null;
        }

        $title = $account->platform === PlatformEnum::X9->value ? 'X9 Account Details' : 'MT5 Account Details';

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
            'title' => $title,
            'x9_group_name' => $x9GroupName,
            'x9_leverage' => $x9Leverage,
            'total_profit' => $total_profit,
            'total_comission' => $total_comission,
            'total_swap' => $total_swap,
            'total_trades' => $total_trades,
        ]);
    }
}
