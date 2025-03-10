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
        $userId = auth()->user()->id;
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

        $plan_id = $ib->planDetails ? $ib->planDetails->ib_category_id : '';


        $ib_email = auth()->user()->email;
        //  dd($plan_id);
        if ($plan_id) {
            ini_set('max_execution_time', 600);
            ini_set("memory_limit", "1024M");
            $ibPlans = Cache::remember('ibPlans:' . $userId, 60 * 60, function () use ($plan_id) {
                return IbPlanDetails::where('ib_category_id', $plan_id)->where('status', 1)
                    ->whereNull('deleted_at')
                    ->get()
                    ->toArray();
            });
            // IbPlanDetails::where('ib_category_id', $plan_id)
            //     ->where('status', 1)
            //     ->whereNull('deleted_at')
            //     ->get()
            //     ->toArray();
            // Prepare the commission structure
            // dd($ibPlans);
            $ib_acc_plans = [];
            foreach ($ibPlans as $plan) {
                $ib_acc_plans[$plan['account_type_id']][$plan['level_id']] = [];

                for ($i = 1; $i <= $plan['level_id']; $i++) {
                    $ib_acc_plans[$plan['account_type_id']][$plan['level_id']]["d$i"] = $plan["d$i"];
                }
            }
            // dump($ib_acc_plans);
            $referral_code = auth()->user()->ib->referral_code;

            if (!$referral_code) {
                $referral_code = auth()->user()->ib->email;
            }

            // info('Getting accounts for ref code '.$referral_code." for user ".$userId);
            // dd($referral_code);
            // Loop through levels and fetch associated client accounts
            // for ($i = 1; $i <= 15; $i++) {
            //     Account::select('id', 'code', 'user_id', 'account_type_id')
            //         ->where('demo', false)
            //         ->where('account_request_status', 1)
            //         ->whereHas('user', function ($query) use ($referral_code, $i) {
            //             $query->where("ib$i", $referral_code)->where('status', 1);
            //         })
            //         ->chunk(100, function ($clientLiveAccs) use ($referral_code, $i) {
            //             foreach ($clientLiveAccs as $client) {
            //                 $login = $client->code;
            //                 $from = 'September 01,2024';
            //                 $to = 'March 31,2080';
            //                 $total = 0;

            //                 $error_code = $this->api->HistoryGetTotal($login, $from, $to, $total);
            //                 if ($error_code != MTRetCode::MT_RET_OK) {
            //                     session()->flash('error', 'MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
            //                     continue;
            //                 }

            //                 $closedOrderHistory = $total;
            //                 if ($closedOrderHistory == 0) {
            //                     continue;
            //                 }

            //                 $offset = Ib1Commission::where('code', $login)->count();
            //                 $total = $closedOrderHistory;

            //                 $maxTries = 10;
            //                 $attempts = 0;
            //                 $processedOrders = [];

            //                 while ($offset < $total && $attempts < $maxTries) {
            //                     $error_code = $this->api->HistoryGetPage($login, $from, $to, $offset, $total, $orders);
            //                     if ($error_code != MTRetCode::MT_RET_OK) {
            //                         session()->flash('error', 'MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
            //                         break;
            //                     }

            //                     if ($orders) {
            //                         $ibcommissions = [];
            //                         $orderIdsAndCodes = [];

            //                         foreach ($orders as $item) {
            //                             $symbolWithoutP = $item->Symbol;
            //                             if (!isset($symbolmap[$symbolWithoutP])) {
            //                                 try {
            //                                     $symbol = Symbol::where('symbol', $symbolWithoutP)->first();
            //                                     $symbolmap[$symbolWithoutP] = $symbol ? $symbol->path : 'default/path';
            //                                 } catch (Exception $e) {
            //                                     logger()->error('Error fetching symbol: ' . $e->getMessage());
            //                                     $symbolmap[$symbolWithoutP] = 'error/path';
            //                                 }
            //                             }

            //                             $symbolpath = $symbolmap[$symbolWithoutP];
            //                             $b = (strpos($symbolpath, 'Energy') !== false || strpos($symbolpath, 'Indices') !== false || strpos($symbolpath, 'Cryptocurrencies') !== false) ? 0.00001 : 0.0001;

            //                             if (in_array($item->Order . '-' . $item->Login, $processedOrders)) {
            //                                 continue;
            //                             }

            //                             $existingCommission = Ib1Commission::where('order_id', $item->Order)
            //                                 ->where('code', $item->Login)
            //                                 ->exists();

            //                             if ($existingCommission) {
            //                                 continue;
            //                             }

            //                             $processedOrders[] = $item->Order . '-' . $item->Login;

            //                             $ibcommissions[] = [
            //                                 'id' => (string)Str::orderedUuid(),
            //                                 'user_id' => $client->user_id,
            //                                 'account_id' => $client->id,
            //                                 'order_id' => $item->Order,
            //                                 'code' => $item->Login,
            //                                 'init_volume' => $item->VolumeInitial,
            //                                 'symbol' => $symbolWithoutP,
            //                                 'volume' => $item->VolumeInitial * $b,
            //                                 'time_closed' => Carbon::createFromTimestamp($item->TimeDone),
            //                                 'created_at' => now(),
            //                                 'updated_at' => now(),
            //                             ];

            //                             if (count($ibcommissions) >= 50) {
            //                                 try {
            //                                     Ib1Commission::insert($ibcommissions);
            //                                 } catch (Exception $e) {
            //                                     logger()->error('Error inserting commission: ' . $e->getMessage());
            //                                 }
            //                                 $ibcommissions = [];
            //                             }
            //                         }

            //                         if (count($ibcommissions) > 0) {
            //                             try {
            //                                 Ib1Commission::insert($ibcommissions);
            //                             } catch (Exception $e) {
            //                                 logger()->error('Error inserting commission: ' . $e->getMessage());
            //                             }
            //                         }
            //                     }

            //                     $offset += count($orders);

            //                     $attempts++;
            //                     if ($attempts >= $maxTries) {
            //                         logger()->warning("Reached max tries for account: $login after $attempts attempts.");
            //                     }
            //                 }

            //                 if ($attempts >= $maxTries) {
            //                     session()->flash('error', "Reached maximum attempts for account: $login. Skipping.");
            //                 }
            //             }
            //         });
            // }

            //Calculate IB Wallet
            // for ($i = 1; $i <= 15; $i++) {
            //     DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");

            //     Ib1Commission::with(['user:id,email,ib1,ib2,ib3,ib4,ib5,ib6,ib7,ib8,ib9,ib10,ib11,ib12,ib13,ib14,ib15', 'account:id,account_type_id', 'ibWallet'])
            //         ->whereHas('user', function ($query) use ($referral_code, $i) {
            //             $query->where("ib$i", $referral_code)->where('status', 1);
            //         })
            //         ->whereDoesntHave('ibWallet', function ($query) use ($userId) {
            //             $query->where('user_id', $userId);
            //         })
            //         ->where('status', 0)
            //         ->chunk(100, function ($client_live_accs) use ($referral_code, $userId, $ib_acc_plans, $i) {
            //             $walletsToCreate = [];

            //             foreach ($client_live_accs as $ca) {
            //                 $ib_level = collect(range(1, 15))->takeWhile(fn($iter) => $ca->user->{'ib' . $iter} !== null)->count();
            //                 $commission = $ib_acc_plans[$ca->account->account_type_id][$ib_level]["d$i"] ?? null;

            //                 if ($commission) {
            //                     $ib_level_name = "IB Level $ib_level - D$i";
            //                     $ib_wallet = ((float)$commission / 2) * $ca->volume;

            //                     $formatted_ib_wallet = number_format($ib_wallet, 10, '.', '');

            //                     if ($formatted_ib_wallet < 0.0000001) {
            //                         $formatted_ib_wallet = '0.0000000000'; // Handle small values
            //                     }

            //                     $existingWallet = IbWallet::where('user_id', $userId)
            //                         ->where('order_id', $ca->order_id)
            //                         ->exists();

            //                     if (!$existingWallet) {
            //                         $walletsToCreate[] = [
            //                             'id' => (string)Str::orderedUuid(),
            //                             'ib_wallet' => $formatted_ib_wallet,
            //                             'email' => $referral_code,
            //                             'code' => $ca->code,
            //                             'user_id' => $userId,
            //                             'account_id' => $ca->account->id,
            //                             'order_id' => $ca->order_id,
            //                             'ib1_commission_id' => $ca->id,
            //                             'ib_level' => $ib_level_name,
            //                             'created_at' => now(),
            //                             'updated_at' => now(),
            //                         ];
            //                     }
            //                 }
            //             }

            //             if (count($walletsToCreate) > 0) {
            //                 try {
            //                     IbWallet::insert($walletsToCreate);
            //                 } catch (Exception $e) {
            //                     logger()->error('Error inserting IB wallet records: ' . $e->getMessage());
            //                 }
            //             }
            //         });
            // }
        }
        $refercode = auth()->user()->ib->referral_code;
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
            ->where('account_request_status', 1)
            ->orderBy('id', 'desc')
            ->get();
        for ($i = 1; $i <= 7; $i++) {
            $ib_clients[$i] = IbClientList::where("ib$i", $refercode)->get();
        }
        $histories = IbWallet::where('user_id', $userId)->get();
        // info("IB Profile for user ".$userId." with wallet ".json_encode($ib_wallet));
        // dd($ib_wallet);
        $user = User::with('ib')->findOrFail($userId);

        $IbTotalDeposits = $user->IbTotalDeposits;
        return view('ib-profile', compact('ib_wallet_raw', 'ib', 'ib_clients_total', 'ib_wallet', 'live_accs', 'ib_clients', 'histories', 'userId','IbTotalDeposits'));
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
            // Validate the request input
            $request->validate([
                'amount' => 'required|numeric|min:1', // Ensure amount is a positive number
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
