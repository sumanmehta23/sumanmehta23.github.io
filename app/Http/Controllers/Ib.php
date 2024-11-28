<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Ib1;
use App\Models\User;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\Country;
use App\Models\IbWallet;
use App\Models\LiveAccount;
use App\MT5\MTEnDealAction;
use App\Models\IbClientList;
use App\Services\MT5Service;
use Illuminate\Http\Request;
use App\Models\Ib1Commission;
use App\Models\IbPlanDetails;
use App\Models\TradeDeposit;
use App\Helpers\AccountHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

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
        $ib_result = Ib1::where('email', $email)->first();
        if($ib_result && $ib_result->status == 1) {
            return redirect("/ib-profile");
        }
        return view('ib', compact('ib_result'));
    }
    public function ibEnroll(Request $request)
    {
        if ($request->isMethod('post')) {
            $uid = uniqid();
            $code = md5(uniqid(rand()));
            $user = session('user');
            try {
                Ib1::create([
                    'uid' => $uid,
                    'email' => $user['email'],
                    'name' => $user['fullname'],
                    'password' => $user['password'],
                    'number' => $user['number'],
                    'username' => $user['email'],
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
    public function ib_profile()
    {
        $userId = auth()->user()->id;
        AccountHelper::updateLiveAndDemoAccounts($userId, $this->api);
        $ib = Ib1::where('user_id', $userId)
            ->whereNotNull('acc_type')
            ->first();

        // dd($ib);
        if (!$ib) {
            return redirect()->route('ib');
        }
        $plan_id = $ib->acc_type;
        $ib_email = auth()->user()->email;
        if ($plan_id) {
            $ibPlans = IbPlanDetails::where('ib_plan_id', $plan_id)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->get()
                ->toArray();
            // Prepare the commission structure
            $ib_acc_plans = [];
            foreach ($ibPlans as $plan) {
                $ib_acc_plans[$plan['acc_type']][$plan['level_id']] = [];
                for ($i = 1; $i <= $plan['level_id']; $i++) {
                    $ib_acc_plans[$plan['acc_type']][$plan['level_id']]["d$i"] = $plan["d$i"];
                }
            }
            // Loop through levels and fetch associated client accounts
            for ($i = 1; $i <= 15; $i++) {
                $clientLiveAccs = Account::select('code', 'user_id', 'account_type')
                    ->where('demo',false)
                    ->whereHas('user', function ($query) use ($userId, $i) {
                        $query->where("ib$i", $userId)->where('status', 1);
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
                    $offset = Ib1Commission::where('login', $login)->count();
                    $total = $closedOrderHistory;
                    while ($offset < $total) {
                        if (($error_code = $this->api->HistoryGetPage($login, $from, $to, $offset, $total, $orders)) != MTRetCode::MT_RET_OK) {
                            session()->flash('error', 'MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
                        }
                        $result2 = $orders;
                        if ($result2) {
                            foreach ($result2 as $item) {
                                $order = $item->Order;
                                $login = $item->Login;
                                $init_volume = $item->VolumeInitial;
                                $volume = $init_volume * 0.0001;
                                $time_closed = gmdate("Y-m-d H:i:s", $item->TimeDone);
                                try {
                                    Ib1Commission::create([
                                        'user_id' => $client->user_id,
                                        'order_id' => $order,
                                        'login' => $login,
                                        // 'init_volume' => $init_volume,
                                        'volume' => $volume,
                                        'time_closed' => $time_closed
                                    ]);
                                } catch (Exception $e) {
                                    logger()->error('Error inserting commission: ' . $e->getMessage());
                                }
                            }
                        }
                        $offset = Ib1Commission::where('login', $login)->count();
                    }
                }
            }

            //Calculate IB Wallet
            for ($i = 1; $i <= 15; $i++) {
                DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");
                $client_live_accs=Ib1Commission::where('status', 0)
                ->with(['user:id,email,ib1,ib2,ib3,ib4,ib5,ib6,ib7,ib8,ib9,ib10,ib11,ib12,ib13,ib14,ib15','account:id,account_type','ibWallet'])
                    ->whereHas('user', function ($query) use ($userId, $i) {
                        $query->where("ib$i", $userId)->where('status', 1);
                    })
                    ->whereDoesntHave('ibWallet', function ($query) use ($userId) {
                        $query->where('user_id', $userId)->whereNull('order_id');
                    })
                    ->where('status', 0)
                    
                    ->groupBy('order_id')
                    ->orderByDesc('id')->get();
                
                    // $client_live_accs = DB::table('ib1_commission')
                    // ->join('liveaccount', 'liveaccount.trade_id', '=', 'ib1_commission.login')
                    // ->join('aspnetusers', 'aspnetusers.email', '=', 'ib1_commission.user_id')
                    // ->leftJoin('ib_wallet', function ($join) use ($ib_email) {
                    //     $join->on('ib_wallet.order_id', '=', 'ib1_commission.order_id')
                    //         ->where('ib_wallet.email', '=', $ib_email);
                    // })
                    // ->select(
                    //     'aspnetusers.email as client_email',
                    //     'aspnetusers.ib1',
                    //     'aspnetusers.ib2',
                    //     'aspnetusers.ib3',
                    //     'aspnetusers.ib4',
                    //     'aspnetusers.ib5',
                    //     'aspnetusers.ib6',
                    //     'aspnetusers.ib7',
                    //     'aspnetusers.ib8',
                    //     'aspnetusers.ib9',
                    //     'aspnetusers.ib10',
                    //     'aspnetusers.ib11',
                    //     'aspnetusers.ib12',
                    //     'aspnetusers.ib13',
                    //     'aspnetusers.ib14',
                    //     'aspnetusers.ib15',
                    //     'ib1_commission.*',
                    //     'liveaccount.account_type'
                    // )
                    // ->where('ib1_commission.status', 0)
                    // ->whereNull('ib_wallet.order_id')
                    // ->where('aspnetusers.status', 1)
                    // ->where('aspnetusers.ib' . $i, '=', $ib_email)
                    // ->groupBy(
                    //     'ib1_commission.order_id'
                    // )
                    // ->orderByDesc('ib1_commission.id')
                    // ->get();
                foreach ($clientLiveAccs as $ca) {
                    $ib_level = collect(range(1, 15))->takeWhile(fn($iter) => $ca->{'ib' . $iter} !== null)->count();
                    $commission = $ib_acc_plans[$ca->account_type][$ib_level]["d$i"] ?? null;

                    if ($commission) {
                        $ib_level_name = "IB Level $ib_level - D$i";
                        $ib_wallet = ((float) $commission / 2) * $ca->volume;

                        IbWallet::create([
                            'ib_wallet' => $ib_wallet,
                            'email' => $ib_email,
                            'user_id' => $userId,
                            'account_id' => $ca->account->id,
                            'order_id' => $ca->order_id,
                            'remark' => $ca->client_email,
                            'ib_level' => $ib_level_name,
                        ]);
                    }
                }
            }
        }
        $ib_clients_total = User::where(function ($query) use ($userId) {
            for ($i = 1; $i <= 15; $i++) {
                $query->orWhere("ib{$i}", $userId);
            }
        })->distinct('email')->count('email');
        $ib_wallet_raw = IbWallet::where('user_id', $userId)
            ->selectRaw('SUM(ib_wallet) as wallet, SUM(ib_withdraw) as withdraw')
            ->first();
        $ib_wallet = 0.00;
        if ($ib_wallet_raw) {
            $ib_wallet = $ib_wallet_raw->wallet - $ib_wallet_raw->withdraw;
        }
        $live_accs = Account::where('user_id', $userId)
            ->where('demo', false)
            ->orderBy('id', 'desc')
            ->get();
        for ($i = 1; $i <= 7; $i++) {
            $ib_clients[$i] = IbClientList::where("ib$i", $userId)->get();
        }
        $histories = IbWallet::where('user_id', $userId)->get();
        return view('ib-profile', compact('ib_clients_total', 'ib_wallet', 'live_accs', 'ib_clients', 'histories'));
    }
    public function ibReference(Request $request)
    {
        if ($request->has('refercode')) {
            $refercode = $request->query('refercode');
            $decodedEmail = base64_decode($refercode);

            // Fetch the IB record using Eloquent or DB facade
            $result = DB::table('ib1')->where('email', $decodedEmail)->first();

            if ($result) {
                // Encode the IB details as required
                $ib1 = base64_encode($result->email);
                $ib2 = base64_encode($result->ib1);
                $ib3 = base64_encode($result->ib2);
                $ib4 = base64_encode($result->ib3);
                $ib5 = base64_encode($result->ib4);
                $ib6 = base64_encode($result->ib5);
                $ib7 = base64_encode($result->ib6);
                $ib8 = base64_encode($result->ib7);
                $ib9 = base64_encode($result->ib8);
                $ib10 = base64_encode($result->ib9);
                $ib11 = base64_encode($result->ib10);
                $ib12 = base64_encode($result->ib11);
                $ib13 = base64_encode($result->ib12);
                $ib14 = base64_encode($result->ib13);
                $ib15 = base64_encode($result->ib14);
                $countries = Country::all();
                return view('auth.ib_ref', compact('countries'));
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
                        'email' => $email,
                        'trade_id' => $account->code,
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
