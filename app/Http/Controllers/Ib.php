<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Ib1;
use App\Models\User;
use App\MT5\MTWebAPI;
use App\Models\Symbol;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\Country;
use App\Models\IbWallet;
use Carbon\Carbon;
use App\Models\LiveAccount;
use App\MT5\MTEnDealAction;
use App\Models\IbClientList;
use App\Models\TradeDeposit;
use App\Services\MT5Service;
use Illuminate\Http\Request;
use App\Models\Ib1Commission;
use App\Models\IbPlanDetails;
use App\Helpers\AccountHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

class Ib extends Controller
{
    protected $mt5Service;
    protected $api;
    public function __construct(MT5Service $mt5Service)
    {
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
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
        if ($request->isMethod('post')) {
            $uid = uniqid();
            $code = Str::random(32);
            do {
                $referral_code = Str::random(6);
            } while (Ib1::where('referral_code', $referral_code)->exists());
            $user = auth()->user();
            try {
                Ib1::create([
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
                return response()->json(['status' => 'true']);
            } catch (\Exception $e) {
                return response()->json(['status' => 'false', 'message' => $e->getMessage()]);
            }
        }
        return response()->json(['status' => 'false', 'message' => 'Invalid request method']);
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
            $ib_wallet = $ib_wallet_raw->wallet - $ib_wallet_raw->withdraw;
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
                return view('auth.register', compact('countries', 'referral_code'));
            } else {
                return redirect()->route('register')->with('error', 'Invalid Refer Code');
            }
        } else {
            return redirect()->route('register')->with('error', 'Invalid Refer Code');
        }
    }
    public function processTransfer(Request $request)
    {
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
                $errorCode = $this->api->TradeBalance($account->code, $type = MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket, true);
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
                        'account_id' => $account->id,
                        'ib_withdraw' => $amount,
                        'remark' => 'IB Comm. Withdrawl'
                    ]);
                    return redirect()->back()->with('success', 'IB Balance is Transferred to ' . $account->code);
                }
            } else {

                return redirect()->back()->with('error', 'Insufficient IB Transferrable Balance');
            }
        }
    }
}
