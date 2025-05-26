<?php

namespace App\Http\Controllers;

use App\Models\Ib1;
use App\Models\User;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\Leverage;
use App\Models\AccountType;
use App\Models\DemoDeposit;
use App\MT5\MTEnDealAction;
use App\Models\TotalBalance;
use App\Services\MT5Service;
use Illuminate\Http\Request;
use App\Models\Ib1Commission;
use App\Models\WalletDeposit;
use App\MT5\MTProtocolConsts;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\MailService as MailService;


class CompetitionController extends Controller
{
    protected $api;
    protected $mailService;
    protected $mt5Service;
    public function __construct(MT5Service $mt5Service, MailService $mailService)
    {
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
    }

    public function competition()
    {

        $email = auth()->user()->email;
        $results = Account::with('accountType')
            ->where('user_id', auth()->user()->id)
            ->whereNotNull('competition_month')
            ->where('demo', true)
            ->orderBy('id', 'desc')
            ->get();

        return view('competition', compact('results'));
    }

    public function showCompetitionForm()
    {
        $user=auth()->user();
        $email  = $user->email;

        $results = AccountType::with('mt5Group')
            ->whereHas('mt5Group', function ($query) {
                $query->where('mt5_group_type', 'demo');
            })
            ->where('is_client_group', 1)
            ->where('ac_name', 'Competition')
            ->orderBy('display_priority', 'desc')
            ->get();

        return view('createCompetition', compact('user', 'results'));
    }

    public function createCompetition(Request $request)
    {
        $key = 'deposit:' . (auth()->id() ?: $request->ip());

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retryAfter = RateLimiter::availableIn($key);
            return redirect()->back()
            ->with('error', "Please wait {$retryAfter} seconds before trying again.");
        }
        RateLimiter::hit($key, 10);

        $settings = settings();
        $validatedData = $request->validate([
            'options' => 'required|string',
            'leverage' => 'required|string',
            'demo_deposit' => 'required',
        ]);

        $demo_deposit = $request->demo_deposit;
        $user = auth()->user();
        $nick_name = $request->nick_name;

        $email = $user->email;
        $group = AccountType::where('id', $validatedData['options'])->firstOrFail();
        $referral=$user->referral;
        $ib=$user->ib1;
        $account_type_id = $validatedData['options'];
        //wealthytrades
        if(($referral=="wealthytrades" || $ib=="wealthytrades") && $group->ac_group != 'LM\B-Book\10x\DF-B'){
            $groupCode = str_replace("DF","SNSI",$group->ac_group);
            $group = AccountType::where('ac_group', $groupCode)->first();

            if($group){
                $_POST["options"] =$group->id;
                $account_type_id = $group->id;
            }
        }elseif((strtolower($referral)=="swingtradinglab" || strtolower($ib)=="swingtradinglab") && $group->ac_group != 'LM\B-Book\10x\DF-B') {
            $groupCode = str_replace("DF","ALEX",$group->ac_group);
            $group = AccountType::where('ac_group', $groupCode)->first();
            if($group){
                $_POST["options"] =$group->id;
                $account_type_id = $group->id;
            }

        }else{
            $groupCode = $group->ac_group;
        }

        $userAcc = Account::where('user_id', $user->id)->where('demo',0)->get();

        $nextMonth = date('F', strtotime('+1 month'));
        $currentYear = date('Y');
        $nextYear = date('Y') + 1;

        $existingCompetition = Account::where('user_id', $user->id)
            ->where('competition_month', $nextMonth)
            ->where('competition_year',$currentYear)
            ->where('demo', true)
            ->first();

        if ($existingCompetition) {
            return redirect()->back()->with('error', 'Competition already purchased for next month.');
        }

        if($group->ac_name == 'Competition'){
            $settings = settings();
            activity()->causedBy($user->id)
                ->withProperties(
                    [
                        'ip' => $request->ip(),
                        'email' => $user->email,
                        'type' => 'Live',
                        'code' => 'Pending',
                        'leverage' => $validatedData['leverage'],
                        'ib' => $ib,
                        'remark' => 'Competition purchase'
                    ])
            ->event('create')
            ->log('Create Live Account');
            if($nextMonth == 'January'){
                $year = $nextYear;
            }else{
                $year = $currentYear;
            }
            $useraccount = Account::create([
                'user_id' => $user->id,
                'name' => $user->fullname??$user->email,
                'demo'=> true,
                'email' => $user->email,
                'account_nick_name' =>  $nick_name,
                'account_type_id' => $account_type_id,
                'leverage' => $validatedData['leverage'],
                'currency' => 'USD',
                'ib1' => $user->ib1?? "",
                'account_request_status' => '0',
                'competition_month' => $nextMonth,
                'competition_year' => $year,
                'balance' => $demo_deposit,

            ]);
            if($useraccount){
                $from = $settings['email_from_address'];
                $emailSubject = 'Competition Requested';
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $content =
                    '<div>Thank you for choosing LQH Markets. Your request for a '.date('F', strtotime('+1 month')).' month will be approved  on 1st of next month.</div>

                    <p>If you need any assistance, our support team is available 24/7 at support@lqhmarkets.com</p>
                    <p>Best Regards.</p>
                <p>LQH Markets Team</p>';
                $templateVars = [
                    'name' => $user->fullname,
                    'email' => $settings['email_from_address'],
                    "content" => $content,
                    "title_right" => "Competition Request Pending",
                    "subtitle_right" => "",
                ];
                $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
                return redirect()->back()->with('success', 'Competition Request Received Your request has been submitted.');
            } else {
                return redirect()->back()->with('error', 'Account not created');
            }
        }
    }

    public function getAccountRank(Request $request)
    {

        $ids = $request->input('ids', []);

        // Example: fetch ranks from the Account model, adjust as needed
        // $accounts = Account::whereIn('id', $ids)->get();


         // Group transactions by month and account, and sum the amounts
        $monthlyData = DB::table('accounts')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                'account_id',
                DB::raw('SUM(amount) as total_amount')
            )
            ->whereIn('account_id', $ids)
            ->groupBy('month', 'account_id')
            ->orderBy('month')
            ->orderByDesc('total_amount')
            ->get();


        // Organize data by month
        $grouped = [];
        foreach ($monthlyData as $data) {
            $grouped[$data->month][] = $data;
        }

        // Assign ranks within each month
        $ranks = [];
        foreach ($grouped as $month => $accounts) {
            $rank = 1;
            foreach ($accounts as $accountData) {
                $ranks[$month][$accountData->account_id] = [
                    'rank' => $rank++,
                    'total' => $accountData->total_amount
                ];
            }
        }

        return response()->json($ranks);

        // $ranks = [];
        // foreach ($accounts as $account) {
        //     $ranks[$account->id] = $account->rank ?? '-';
        // }
        // return response()->json($ranks);
    }

}
