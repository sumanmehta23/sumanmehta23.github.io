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
use Str;use Carbon\Carbon;
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
        $user=auth()->user();
        $cacheKey = 'ib1_' . $user->id;

        if (!Cache::has($cacheKey)) {
            $ib_result =Ib1::where('user_id', $user->id)->first();
            Cache::put($cacheKey, $ib_result, 60);
            
        } else {
            $ib_result=Cache::get($cacheKey);
        }
        // $ib_result = Ib1::where('user_id', $user->id)->first();
        if($ib_result && $ib_result->status == 1) {
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
                    'referral_code' =>$referral_code,
                    'ib_category_id' => $request->ib_category_id,
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

    public function ibUpdateReferral(Request $request)
    {
        $ib1_id = $request->ib1_id;
        $referral_code = $request->referral_code;

        $ib1 = Ib1::find($ib1_id);
        if ($ib1) {

            try {
                if ($ib1->referral_code == $referral_code) {
                    session()->flash('error', 'Referral code already saved.');
                    return back();             }

                $existingReferralCode = Ib1::where('referral_code', $referral_code)->first();
                if ($existingReferralCode) {
                    session()->flash('error', 'Referral code already registered.');
                    return back();               }


                $ib1_referral = $ib1->referral_code;
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
                return back();            }
        }
        session()->flash('error', 'Ib1 record not found.');
        return back();
    }

    public function ib_profile()
    {
        $userId = auth()->user()->id;
        $ib_wallet = 0.00;
        AccountHelper::updateLiveAndDemoAccounts($userId, $this->api);
        $ib = Ib1::where('user_id', $userId)
            ->whereNotNull('ib_category_id')
            ->first();


        if (!$ib) {
            return redirect()->route('ib');
        }
        $plan_id = $ib->ib_category_id;


        $ib_email = auth()->user()->email;

        if ($plan_id) {
            $ibPlans = IbPlanDetails::where('ib_category_id', $plan_id)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->get()
                ->toArray();
            // Prepare the commission structure
            $ib_acc_plans = [];
            foreach ($ibPlans as $plan) {
                $ib_acc_plans[$plan['account_type_id']][$plan['level_id']] = [];
                for ($i = 1; $i <= $plan['level_id']; $i++) {
                    $ib_acc_plans[$plan['account_type_id']][$plan['level_id']]["d$i"] = $plan["d$i"];
                }
            }

            $referral_code= auth()->user()->ib->referral_code;
            // Loop through levels and fetch associated client accounts
            for ($i = 1; $i <= 15; $i++) {
                $clientLiveAccs = Account::select('id', 'code', 'user_id', 'account_type_id')
                    ->where('demo',false)
                    ->whereHas('user', function ($query) use ($referral_code, $i) {
                        $query->where("ib$i", $referral_code)->where('status', 1);
                    })
                    ->get();

                foreach ($clientLiveAccs as $client) {
                    $login = $client->code;
                    $from = 'September 01,2024';
                    $to = 'March 31,2080';
                    $total = 0;
                    if (($error_code = $this->api->HistoryGetTotal($login, $from, $to, $total)) != MTRetCode::MT_RET_OK) {
                        session()->flash('error', 'MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
                    }

                    $closedOrderHistory = $total;

                    if ($closedOrderHistory == 0) {
                        continue;
                    }
                    $offset = Ib1Commission::where('code', $login)->count();

                    $total = $closedOrderHistory;
                    while ($offset < $total) {
                        if (($error_code = $this->api->HistoryGetPage($login, $from, $to, $offset, $total, $orders)) != MTRetCode::MT_RET_OK) {
                            session()->flash('error', 'MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
                        }
                        $result2 = $orders;

                        if ($result2) {
                            foreach ($result2 as $item) {

                                $symbolWithoutP = rtrim($item->Symbol, '.p');
                                
                                if (!isset($symbolmap[$symbolWithoutP])) {
                                    try {
                                        $symbol = Symbol::where('symbol', $symbolWithoutP)->first();

                                        if ($symbol) {
                                            $symbolmap[$symbolWithoutP] = $symbol->path;
                                        } else {
                                            $symbolmap[$symbolWithoutP] = 'default/path';
                                        }
                                    } catch (\Exception $e) {
                                        logger()->error('Error fetching symbol: ' . $e->getMessage());
                                        $symbolmap[$symbolWithoutP] = 'error/path';
                                    }
                                }
                                
                                $symbolpath = $symbolmap[$symbolWithoutP];
                                
                                if (strpos($symbolpath, 'Energy') !== false || strpos($symbolpath, 'Indices') !== false || strpos($symbolpath, 'Cryptocurrencies') !== false) {
                                    $b = 0.00001;
                                } else {
                                    $b = 0.0001;
                                }

                                $order = $item->Order;
                                $login = $item->Login;
                                $init_volume = $item->VolumeInitial;
                                $volume = $init_volume * $b;
                                $time_closed = Carbon::createFromTimestamp($item->TimeDone);

                                try {
                                    Ib1Commission::create([
                                        'user_id' => $client->user_id,
                                        'account_id' => $client->id,
                                        'order_id' => $order,
                                        'code' => $login,
                                        // 'init_volume' => $init_volume,
                                        'volume' => $volume,
                                        'time_closed' => $time_closed
                                    ]);
                                } catch (Exception $e) {
                                    dd($e);
                                    logger()->error('Error inserting commission: ' . $e->getMessage());
                                }
                            }
                        }
                        $offset = Ib1Commission::where('code', $login)->count();
                    }
                }
            }

            //Calculate IB Wallet
            for ($i = 1; $i <= 15; $i++) {
                DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");
                $client_live_accs=Ib1Commission::with(['user:id,email,ib1,ib2,ib3,ib4,ib5,ib6,ib7,ib8,ib9,ib10,ib11,ib12,ib13,ib14,ib15','account:id,account_type_id','ibWallet'])
                    ->whereHas('user', function ($query) use ($referral_code, $i) {
                        $query->where("ib$i", $referral_code)->where('status', 1);
                    })
                    ->whereDoesntHave('ibWallet', function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    })
                    ->where('status', 0)

                    ->groupBy('order_id')
                    ->orderByDesc('id')->get();

                foreach ($client_live_accs as $ca) {
                    $ib_level = collect(range(1, 15))->takeWhile(fn($iter) => $ca->user->{'ib' . $iter} !== null)->count();
                    $commission = $ib_acc_plans[$ca->account->account_type_id][$ib_level]["d$i"] ?? null;

                    if ($commission) {
                        $ib_level_name = "IB Level $ib_level - D$i";
                        $ib_wallet = ((float) $commission / 2) * $ca->volume;

                        IbWallet::create([
                            'ib_wallet' => $ib_wallet,
                            'email' => $referral_code,
                            'code' => $ca->code,
                            'user_id' => $userId,
                            'account_id' => $ca->account->id,
                            'order_id' => $ca->order_id,
                            // 'remark' => $ca->client_email,
                            'ib_level' => $ib_level_name,
                        ]);

                        // $ca->status= 1;
                        // $ca->save();
                    }
                }
                // $client_live_accs=Ib1Commission::whereHas('user', function ($query) use ($referral_code, $i) {
                //     $query->where("ib$i", $referral_code)->where('status', 1);
                // })
                // ->whereDoesntHave('ibWallet', function ($query) use ($userId) {
                //     $query->where('user_id', $userId)->whereNull('order_id');
                // })
                // ->where('status', 0)
                // ->update(['status'=>1]);
            }


        }
        $refercode =auth()->user()->ib->referral_code;
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
            ->where('demo', false)
            ->orderBy('id', 'desc')
            ->get();

        for ($i = 1; $i <= 7; $i++) {
            $ib_clients[$i] = IbClientList::where("ib$i", auth()->user()->ib->referral_code)->get();
        }
        $histories = IbWallet::where('user_id', $userId)->get();
        // dd($ib_wallet);
        return view('ib-profile', compact('ib_wallet_raw', 'ib', 'ib_clients_total', 'ib_wallet', 'live_accs', 'ib_clients', 'histories'));
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
        if ($request->has('transfer')) {
            $amount = $request->input('amount');
            $accountId = $request->input('account');
            $userId = auth()->user()->id;
            $account=Account::where(['id'=>$accountId,'user_id'=>$userId])->firstOrFail();
            $email = auth()->user()->email;

            $balance=IbWallet::where('user_id', $userId)->selectRaw('SUM(ib_wallet) as wallet, SUM(ib_withdraw) as withdraw')->first();

            $availableBalance = $balance->wallet - $balance->withdraw;
            if ($availableBalance >= $amount) {

                if(!$availableBalance || !$amount || !$account){
                    alert()->warning("Invalid Request","Please Select / Enter valid values");
                    return redirect()->back();
                }

                $comment = 'IB Comm. - Dep';
                $ticket = null;
                $errorCode = $this->api->TradeBalance($account->code, $type = MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket, true);

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
                        'account_id'=>$account->id,
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
