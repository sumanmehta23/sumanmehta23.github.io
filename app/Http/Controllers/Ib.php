<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Ib1;
use App\Models\User;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\Country;
use App\Models\Setting;
use App\Models\IbWallet;
use App\MT5\MTEnDealAction;
use Illuminate\Support\Str;
use App\Models\TradeDeposit;
use Illuminate\Http\Request;
use App\Helpers\AccountHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Services\UniversalMT5Service;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class Ib extends Controller
{
    protected $mt5Service;
    protected $api;
    public function __construct(UniversalMT5Service $mt5Service)
    {
        $this->mt5Service = $mt5Service;
        // MT5 connection deferred - use ensureMT5Connection() in methods that need it
    }

    private function ensureMT5Connection()
    {
        if (!$this->mt5Service) {
            $this->mt5Service = new UniversalMT5Service();
        }

        if (!$this->mt5Service->connect()) {
            Log::error('Failed to establish MT5 connection in Ib');
            return false;
        }

        $this->api = $this->mt5Service->getApi();
        return true;
    }

    public function index()
    {

        $email = auth()->user()->email;
        $user = auth()->user();
        $cacheKey = 'ib1_' . $user->id;

        if (!Cache::has($cacheKey)) {
            $ib_result = Ib1::where('user_id', $user->id)->first();
            Cache::put($cacheKey, $ib_result, 60);
        } else {
            $ib_result = Cache::get($cacheKey);
        }
        // $ib_result = Ib1::where('user_id', $user->id)->first();
        if ($ib_result && $ib_result->status == 1) {
            return redirect("/ib-profile");
        }
        return view('ib', compact('ib_result'));
    }
    public function ibEnroll(Request $request)
    {
        // Validate that request is POST
        if (!$request->isMethod('post')) {
            return response()->json(['status' => 'false', 'message' => 'Invalid request method']);
        }

        try {
            $uid = uniqid();
            $code = Str::random(32);

            // Generate unique referral code
            do {
                $referral_code = Str::random(6);
            } while (Ib1::where('referral_code', $referral_code)->exists());

            $user = auth()->user();
            $ibStatus = 1;
            $ibGroup = '';

            // Get IB activation setting
            $settingsdata = Setting::where('name', 'ib_toggle_activation')->first();
            if (!$settingsdata) {
                throw new \Exception('IB activation settings not found');
            }

            if ($settingsdata->value == 'automatic') {
                // Get default IB group
                $ibGroup = DB::table('ib_plan_details')
                    ->leftJoin('ib_categories', 'ib_categories.id', '=', 'ib_plan_details.ib_category_id')
                    ->where('ib_categories.ib_cat_name', 'default')
                    ->where('ib_plan_details.status', $ibStatus)
                    ->whereNull('ib_plan_details.deleted_at')
                    ->value('ib_plan_details.id');

                // Create IB with active status
                $ib = Ib1::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'referral_code' => $referral_code,
                    'ib_plan_details_id' => $ibGroup,
                    'name' => $user->fullname,
                    'password' => Hash::make($user->password),
                    'number' => $user->number,
                    'username' => $user->email,
                    'emailToken' => $code,
                    'status' => $ibStatus,
                ]);
            } else if ($settingsdata->value == 'manually') {
                // Create IB with pending status
                $ib = Ib1::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'referral_code' => $referral_code,
                    'ib_plan_details_id' => $request->ib_plan_details_id,
                    'name' => $user->fullname,
                    'password' => $user->password,
                    'number' => $user->number,
                    'username' => $user->email,
                    'emailToken' => $code,
                    'status' => 0,
                ]);

                // Log activity
                $adminUser = auth()->guard('admin')->user();
                activity()
                    ->causedBy($adminUser)
                    ->withProperties([
                        'ip' => request()->ip(),
                        'user_email' => $adminUser ? $adminUser->email : null,
                        'userRole' => $adminUser ? $adminUser->userRole : null,
                        'username' => $adminUser ? $adminUser->username : null,
                        'user_id' => $adminUser ? $adminUser->id : null,
                        'client_id' => $user->id,
                        'ib_status' => $ib->status,
                        'ib_group' => $ibGroup,
                        'remark' => 'Ib Request'
                    ])
                    ->event('update')
                    ->log('Ib Request');
            } else {
                throw new \Exception('Invalid IB activation type');
            }

            // Log activity


            // Clear cache
            $cacheKey = 'ib1_' . $user->id;
            Cache::forget($cacheKey);
            // Return response based on activation type
            if ($settingsdata->value == 'automatic') {
                return response()->json([
                    'status' => 'true',
                    'activationType' => 'automatic',
                    'message' => 'IB request submitted successfully and is pending approval.'
                ]);
            } else {
                return response()->json([
                    'status' => 'true',
                    'activationType' => 'manually',
                    'message' => 'IB request submitted successfully and is pending approval.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'false', 'message' => $e->getMessage()]);
        }
    }

    public function ibResend(Request $request)
    {
        $userId = auth()->user()->id;

        try {
            $ib = Ib1::where('user_id', $userId)->where('status', 2)->first();

            if (!$ib) {
                return redirect()->route('ib')->with('error', 'IB not found or invalid status');
            }

            // Update the IB status
            $ib->status = 0;
            $ib->save();

            return response()->json(['status' => 'true', 'message' => 'IB status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'false', 'message' => 'Something went wrong', 'error' => $e->getMessage()]);
        }
    }


    public function ibUpdateReferral(Request $request)
    {
        $ib1_id = $request->ib1_id;
        $referral_code = $request->referral_code;

        $ib1 = Ib1::find($ib1_id);
        activity()->causedBy(auth()->user()->id)
            ->withProperties(
                [
                    'ip' => $request->ip(),
                    'email' => auth()->user()->email,
                    'old' => $ib1->referral_code,
                    'new' => $referral_code,
                    'remark' => 'Update Referral'
                ]
            )
            ->event('update')
            ->log('Update Referral');
        if ($ib1) {

            try {
                if ($ib1->referral_code == $referral_code) {
                    session()->flash('error', 'Referral code already saved.');
                    return back();
                }

                $existingReferralCode = Ib1::where('referral_code', $referral_code)->first();
                if ($existingReferralCode) {
                    session()->flash('error', 'Referral code already registered.');
                    return back();
                }


                $ib1_referral = $ib1->referral_code;
                if (!$ib1_referral) {
                    $ib1_referral = $ib1->email;
                }
                User::where(function ($query) use ($ib1_referral) {
                    for ($i = 1; $i <= 15; $i++) {
                        $query->orWhere("ib$i", $ib1_referral);
                    }
                })
                    ->get()
                    ->each(function ($user) use ($ib1_referral, $referral_code) {
                        for ($i = 1; $i <= 15; $i++) {
                            $column = "ib$i";
                            if ($user->$column == $ib1_referral) {
                                $user->$column = $referral_code;
                            }
                        }
                        $user->save();
                    });
                $ib1->referral_code = $referral_code;
                $ib1->save();

                session()->flash('success', 'Referral code updated successfully.');
                return back();
            } catch (\Exception $e) {
                session()->flash('error', $e->getMessage());
                return back();
            }
        }
        session()->flash('error', 'Ib1 record not found.');
        return back();
    }

    public function ib_profile()
    {

        $user = auth()->user();

        $userId = $user->id;
        $ib_wallet = 0.00;
        AccountHelper::updateLiveAndDemoAccounts($userId, $this->api);
        $ib = Ib1::with('planDetails')
            ->where('user_id', $userId)
            ->where('status', 1)
            ->whereNotNull('ib_plan_details_id')
            ->first();
        if (!$ib) {
            return redirect()->route('ib');
        }
        $ib_email = $user->email;
        //  dd($plan_id);

        $refercode = $user->ib->referral_code;
        $ib_clients_total = User::where(function ($query) use ($refercode) {
            for ($i = 1; $i <= 15; $i++) {
                $query->orWhere("ib{$i}", $refercode);
            }
        })->distinct('email')->count('email');

        $ib_wallet_raw = IbWallet::where('user_id', $userId)
            ->selectRaw('SUM(ib_wallet) as wallet, SUM(ib_withdraw) as withdraw')
            ->first();

        if ($ib_wallet_raw) {
            $ib_wallet = number_format($ib_wallet_raw->wallet - $ib_wallet_raw->withdraw, 2);
        }

        $live_accs = Account::where('user_id', $userId)
            ->select('id', 'balance', 'code')
            ->where('demo', false)
            ->where('account_request_status', 1)
            ->orderBy('id', 'desc')
            ->get();

        $user = User::with('ib')->findOrFail($userId);

        $IbTotalDeposits = $user->IbTotalDeposits;
        $IbTotalWithdrawal = $user->IbTotalWithdrawal;
        return view('ib-profile', compact('ib_wallet_raw', 'ib', 'ib_clients_total', 'ib_wallet', 'live_accs', 'userId', 'IbTotalDeposits', 'IbTotalWithdrawal'));
    }
    public function ibReference(Request $request)
    {
        if ($request->has('refercode')) {
            $refercode = $request->query('refercode');
            $result = DB::table('ib1')->where('referral_code', $refercode)->first();

            if ($result) {
                $referral_code = $result->referral_code;
                $countries = Country::all();
                $turnstileEnabled = (bool) config('services.turnstile.enabled', false);
                $turnstileSiteKey = (string) config('services.turnstile.site_key', '');
                return view('auth.register', compact('countries', 'referral_code', 'turnstileEnabled', 'turnstileSiteKey'));
            } else {
                return redirect()->route('register')->with('error', 'Invalid Refer Code');
            }
        } else {
            return redirect()->route('register')->with('error', 'Invalid Refer Code');
        }
    }
    public function processTransfer(Request $request)
    {
        // Ensure MT5 connection is established
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server. Please try again.');
        }

        // Generate a unique rate-limiting key based on user or IP
        $key = 'processTransfer:' . (auth()->id() ?: $request->ip());

        // Check if the user has exceeded the rate limit
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retryAfter = RateLimiter::availableIn($key);
            return redirect()->back()->with('error', "Too many requests. Please wait {$retryAfter} seconds before trying again.");
        }


        // Increment the rate limiter
        RateLimiter::hit($key, 10); // Lock for 10 seconds

        if ($request->has('transfer')) {
            // Validate the request input
            $request->validate([
                'amount' => 'required|numeric|min:.01', // Ensure amount is a positive number
                'account' => 'required|exists:accounts,id', // Ensure account ID is valid
            ]);
            $amount = $request->input('amount');
            $accountId = $request->input('account');
            $userId = auth()->user()->id;
            $account = Account::where(['id' => $accountId, 'user_id' => $userId])->firstOrFail();
            $email = auth()->user()->email;

            $balance = IbWallet::where('user_id', $userId)->selectRaw('SUM(ib_wallet) as wallet, SUM(ib_withdraw) as withdraw')->first();

            $availableBalance = $balance->wallet - $balance->withdraw;
            if ($availableBalance >= $amount) {

                if (!$availableBalance || !$amount || !$account) {
                    alert()->warning("Invalid Request", "Please Select / Enter valid values");
                    return redirect()->back();
                }

                $comment = 'IB Comm. - Dep';
                $ticket = null;
                $errorCode = $this->api->TradeBalance($account->code, MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket, true);
                activity()->causedBy(auth()->user()->id)
                    ->withProperties(
                        [
                            'ip' => $request->ip(),
                            'email' => auth()->user()->email,
                            'deposit_amount' => $amount,
                            'code' => $account->code,
                            'remark' => 'Commission Transfer'
                        ]
                    )
                    ->event('create')
                    ->log('Commission Transfer');
                if ($errorCode != MTRetCode::MT_RET_OK) {
                    $error = MTRetCode::GetError($errorCode);
                    return redirect()->back()->with('error', 'Something went wrong on Deposit. ' . $error);
                } else {
                    // Insert into trade_deposit.
                    TradeDeposit::create([
                        'user_id' => $userId,
                        'email' => $email,
                        'code' => $account->code,
                        'account_id' => $account->id,
                        'deposit_amount' => $amount,
                        'deposit_type' => 'IB Withdraw',
                        'deposit_from' => 'IB Commission',
                        'status' => 1
                    ]);
                    // Insert into ib_wallet for withdrawal.
                    IbWallet::create([
                        'email' => $email,
                        'user_id' => $userId,
                        'order_id' => rand(10000, 9999999),
                        'account_id' => $account->id,
                        'ib_withdraw' => $amount,
                        'remark' => 'IB Comm. Withdrawl'
                    ]);
                    return redirect()->back()->with('success', 'IB Balance is Transferred to ' . $account->code);
                }
            } else {

                return redirect()->back()->with('error', 'Insufficient Transferable Balance');
            }
        }
    }

    /**
     * Get IB Commission data for DataTables
     */
    public function getCommissionData(Request $request)
    {
        try {
            $userId = auth()->user()->id;

            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Fetch histories
            $query = IbWallet::with('account')->where('user_id', $userId)->orderBy('created_at', 'desc');

            if ($request->ajax()) {
                return datatables()->of($query)
                    ->editColumn('amount', function ($row) {
                        return $row->code ? @money($row->ib_wallet) : ($row->ib_withdraw);
                    })
                    ->addColumn('type', function ($row) {
                        return $row->ib_wallet ? 'Commission' : 'Transfer';
                    })
                    ->addColumn('account', function ($row) {
                        $code = $row->account->code ?? '';
                        $email = $row->account->email ?? '';
                        return "
                            <div class='row align-items-center'>
                                 <div class='col-auto pe-0'>
                                     <img src='/assets/images/mt5.png' alt='user-image'
                                         class='rounded wid-50 hei-50'>
                                 </div>
                                 <div class='col'>
                                     <h4 class='mb-2 ms-2'>
                                         <span class='text-truncate w-100'>{$code}</span>
                                     </h4>
                                     <p class='mb-0 text-muted ms-2 f-12'>
                                         <span class='text-truncate w-100'>{$email}</span>
                                     </p>
                                 </div>
                             </div>";
                    })
                    ->addColumn('date', function ($row) {
                        $date = date('Y-m-d', strtotime($row->created_at));
                        $time = date('H:i:s', strtotime($row->created_at));
                        return "<div class='lh-1'>
                            {$date}
                        </div>
                        <small class='lh-2 text-muted'>
                            {$time}
                        </small>";
                    })
                    ->addColumn('email', function ($row) {
                        return $row->account->email ?? '';
                    })
                    ->addColumn('exp_date', function ($row) {
                        return date('Y-m-d', strtotime($row->created_at));
                    })
                    ->addColumn('time', function ($row) {
                        return date('H:i:s', strtotime($row->created_at));
                    })
                    ->addColumn('exp_account', function ($row) {
                        return $row->account->code ?? '';
                    })
                    ->addColumn('exp_amount', function ($row) {
                        return ($row->ib_wallet) ?? ($row->ib_withdraw);
                    })
                    ->rawColumns(['date', 'account', 'type', 'amount', 'email'])
                    ->make(true);
            }

            return response()->json(['error' => 'Invalid request'], 400);
        } catch (\Exception $e) {
            Log::error('Error in getCommissionData: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching data'], 500);
        }
    }

    /**
     * Get IB Client Profile data for DataTables
     */
    public function getClientIbProfile(Request $request)
    {
        try {
            $userId = auth()->user()->id;
            $level = $request->input('level');

            if (!$userId || !$level) {
                return response()->json(['error' => 'Invalid parameters'], 400);
            }

            $user = User::with('ib')->find($userId);

            if (!$user || !$user->ib) {
                return response()->json(['error' => 'IB profile not found'], 404);
            }

            $query = DB::table('aspnetusers as au')
                ->leftJoin('accounts as acc', function ($join) {
                    $join->on('acc.user_id', '=', 'au.id')
                        ->where('acc.demo', '=', 0);
                })
                ->leftJoin('trade_deposits as td', function ($join) {
                    $join->on('td.user_id', '=', 'au.id')
                        ->where('td.status', '=', 1);
                })
                ->where("au.ib{$level}", $user->ib->referral_code)
                ->select(
                    DB::raw('COUNT(DISTINCT acc.id) AS total_accounts'),
                    DB::raw('SUM(DISTINCT td.deposit_amount) AS total_deposit'),
                    'au.*'
                )
                ->groupBy('au.id');

            if ($request->ajax()) {
                return datatables()->of($query)
                    ->editColumn('email', function ($row) {
                        return " <div class='row align-items-center'>
                            <div class='col-auto pe-0'>
                                <img src='/assets/images/ib_avatar.png' alt='user-image' class='rounded wid-55 hei-55' style='height:50px'>
                            </div>
                            <div class='col'>
                                <h6 class='mb-2'>
                                    <span class='text-truncate w-100'>{$row->fullname}</span>
                                </h6>
                                <p class='mb-0 text-muted f-12'>
                                    <span class='text-truncate w-100'>{$row->email}</span>
                                </p>
                            </div>
                        </div>";
                    })
                    ->editColumn('total_accounts', function ($row) {
                        return $row->total_accounts;
                    })
                    ->editColumn('total_deposit', function ($row) {
                        return $row->total_deposit ? $row->total_deposit : "$0.00";
                    })
                    ->editColumn('profile_status', function ($row) {
                        if ($row->email_confirmed == 1) {
                            return " <span  class='badge btn bg-success'>Active</span>";
                        } else {
                            return "<span class='badge btn bg-info'>Not Verified</span>";
                        }
                    })
                    ->editColumn('client_name', function ($row) {
                        return $row->fullname;
                    })
                    ->editColumn('client_email', function ($row) {
                        return $row->email;
                    })
                    ->rawColumns(['email', 'profile_status', 'client_name', 'client_email'])
                    ->make(true);
            }

            return response()->json(['error' => 'Invalid request'], 400);
        } catch (\Exception $e) {
            Log::error('Error in getClientIbProfile: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching data'], 500);
        }
    }
}
