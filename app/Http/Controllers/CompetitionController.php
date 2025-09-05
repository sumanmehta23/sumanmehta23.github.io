<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
use App\Services\UniversalMT5Service;
use Illuminate\Http\Request;
use App\Models\Ib1Commission;
use App\Models\WalletDeposit;
use App\MT5\MTProtocolConsts;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\CompetitionService;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\MailService as MailService;


class CompetitionController extends Controller
{
    protected $api;
    protected $mailService;
    protected $mt5Service;
    protected $competitionService;
    public function __construct(UniversalMT5Service $mt5Service, MailService $mailService,CompetitionService $competitionService)
    {
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        // MT5 connection deferred - use ensureMT5Connection() in methods that need it
        $this->competitionService = $competitionService;
    }


    public function competition()
    {
        $email = auth()->user()->email;
        $results = Account::with('accountType')
            ->where('user_id', auth()->user()->id)
            ->whereNotNull('competition_start_date')
            ->whereNotNull('competition_end_date')
            ->where('demo', true)
            ->orderBy('id', 'desc')
            ->get();

        // Get all competition account IDs
        $accountIds = Account::with('accountType')
            ->whereNotNull('competition_start_date')
            ->whereNotNull('competition_end_date')
            ->where('demo', true)
            ->pluck('id')
            ->toArray();

        // Get ranks for all competition accounts
        $accounts = Account::whereIn('id', $accountIds)
            ->get()
            ->map(function ($account) {
                return [
                    'competition_start_date' => $account->competition_start_date,
                    'competition_end_date' => $account->competition_end_date,
                    'account_id' => $account->id,
                    'total_amount' => $account->balance ?? 0
                ];
            });

        // Group by competition date range
        $grouped = $accounts->groupBy(function ($item) {
            return $item['competition_start_date'] . '|' . $item['competition_end_date'];
        });

        // Assign ranks within each date range
        $ranks = [];
        foreach ($grouped as $dateRange => $accountsInRange) {
            $sortedAccounts = $accountsInRange->sortByDesc('total_amount')->values();
            $rank = 1;
            foreach ($sortedAccounts as $accountData) {
                $ranks[$dateRange][$accountData['account_id']] = [
                    'rank' => $rank++,
                    'total' => $accountData['total_amount']
                ];
            }
        }

        // Add rank information to each result
        $results = $results->map(function ($account) use ($ranks) {
            $dateRange = $account->competition_start_date . '|' . $account->competition_end_date;
            if (isset($ranks[$dateRange][$account->id])) {
                $account->rank = $ranks[$dateRange][$account->id]['rank'];
                $account->total_participants = count($ranks[$dateRange]);
            } else {
                $account->rank = null;
                $account->total_participants = 0;
            }
            return $account;
        });

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
            ->where('ac_name','like', '%Competition%')
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

        $group = AccountType::where('id', $validatedData['options'])->where('status',1)->first();
        if (!$group) {
            return redirect()->back()->with('error', 'Competition is not active.');
        }

        $account_type_id = $validatedData['options'];
        $start_date = $group->competition_start_date;
        $end_date = $group->competition_end_date;

        if($end_date < now()) {
            return redirect()->back()->with('error', 'Competition is over, try another.');
        }
        if($start_date < now()) {
            return redirect()->back()->with('error', 'Competition registration is over, try another competition.');
        }

        $existingCompetition = Account::with('accountType')
            ->where('user_id', $user->id)
            ->where('demo', true)
            ->whereHas('accountType', function ($query) use ($start_date, $end_date,$account_type_id) {
                $query->where('competition_start_date', '>=',$start_date)
                    ->where('competition_end_date','<=', $end_date)
                    ->where('id', $account_type_id)
                    ->where('ac_name', 'like', '%Competition%');
            })
            ->first();

        // dd($existingCompetition);

        if ($existingCompetition) {
            return redirect()->back()->with('error', 'Competition already purchased for time period ' . $existingCompetition->competition_start_date. ' to '.  $existingCompetition->competition_end_date. '.');
        }

        if (stripos($group->ac_name, 'competition') !== false) {
            activity()->causedBy($user->id)
                ->withProperties([
                    'ip' => $request->ip(),
                    'email' => $user->email,
                    'type' => 'Competition',
                    'code' => 'Pending',
                    'leverage' => $validatedData['leverage'],
                    'remark' => 'Competition purchase'
                ])
                ->event('create')
                ->log('Create Live Account');

            $useraccount = Account::create([
                'user_id' => $user->id,
                'name' => $user->fullname ?? $user->email,
                'demo' => true,
                'email' => $user->email,
                'account_nick_name' => $nick_name,
                'account_type_id' => $account_type_id,
                'leverage' => $validatedData['leverage'],
                'currency' => 'USD',
                'ib1' => $user->ib1 ?? "",
                'account_request_status' => '0',
                'competition_start_date' => $start_date,
                'competition_end_date' => $end_date,
                'balance' => $demo_deposit,
                'competition_product_id' => $group->id,
            ]);

            if ($useraccount) {
                $from = $settings['email_from_address'];
                $emailSubject = 'Competition Registration';
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $content = "
                                <div style='font-family: Montserrat, sans-serif; color: #000000;'>
                                    <p style='color: #000000;'>We’re pleased to confirm your successful registration for the upcoming LQH Markets {$group->ac_name}.</p>
                                    <hr style='border: none; border-top: 0.3px solid rgb(183, 182, 182); margin: 20px 0;'>
                                    <p style='color: #000000;'>Get ready to showcase your trading skills, test your strategies, and compete for top rewards in a dynamic market environment.</p>
                                    <p style='color: #000000;'>Stay tuned — details on the competition start will follow shortly.</p>
                                    <p style='color: #000000;'>If you have any questions or need support, our team is here to help.</p>
                                    <p style='color: #000000;'>Trade smart,</p>
                                    <p style='color: #000000;'>The LQH Markets Team</p>
                                </div>
                            ";
                $templateVars = [
                    'name' => $user->fullname,
                    'email' => $settings['email_from_address'],
                    'content' => $content
                ];

                $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);

                return redirect()->back()->with('success', 'You have successfully enrolled in this competition. An email with your trading account details will be sent once the competition begins.');
            } else {
                return redirect()->back()->with('error', 'Account not created');
            }
        }
    }

    public function getAccountRank(Request $request)
    {
        $ids = $request->input('ids', []);

        // Get accounts with their competition details
        $accounts = Account::whereIn('id', $ids)
            ->whereNotNull('competition_start_date')
            ->whereNotNull('competition_end_date')
            ->where('competition_status', 'active')
            ->where('demo', true)
            ->get()
            ->map(function ($account) {
                return [
                    'month' => $account->competition_year . '-' . str_pad(date('m', strtotime($account->competition_month)), 2, '0', STR_PAD_LEFT),
                    'account_id' => $account->id,
                    'total_amount' => $account->balance ?? 0
                ];
            });

        // Group by month
        $grouped = $accounts->groupBy('month');

        // Assign ranks within each month
        $ranks = [];
        foreach ($grouped as $month => $monthAccounts) {
            // Sort accounts by balance in descending order
            $sortedAccounts = $monthAccounts->sortByDesc('total_amount')->values();

            $rank = 1;
            foreach ($sortedAccounts as $accountData) {
                $ranks[$month][$accountData['account_id']] = [
                    'rank' => $rank++,
                    'total' => $accountData['total_amount']
                ];
            }
        }

        return response()->json($ranks);
    }

    public function leaderboard(Request $request)
    {

        // Get month and year from request or use current
        // $startDate = $request['start_date'] ?? now()->startOfMonth();
        // $endDate = $request['end_date'] ?? now()->endOfMonth();

        $competition_id = $request['competition_id'] ?? null;
        $competition = AccountType::where('id', $competition_id)->first();
        if (!$competition) {
            return redirect()->back()->with('error', 'Competition not found.');
        }

        try {
            // Get competition data from service
            $stats = $this->competitionService->getCurrentStats($competition);
            $rankings = $this->competitionService->getRankings($competition);
            $performers = $this->competitionService->getPerformers($competition);
            $competitionStatus = $this->competitionService->getCompetitionStatus($competition);
            // Get available competitions for filtering
             $availableCompetitions = Account::with('accountType')
                                    ->whereHas('accountType', function ($query) {
                                        $query->whereColumn('id', 'accounts.competition_product_id');
                                    })
                                    // ->select('competition_start_date', 'competition_end_date', 'competition_product_id')
                                    ->where('demo', true)
                                    ->whereNotNull('competition_start_date')
                                    ->whereNotNull('competition_end_date')
                                    ->whereNotNull('competition_product_id')
                                    ->orderBy('competition_start_date', 'desc')
                                    ->get()
                                    ->groupBy('competition_product_id');

            return view('competitions.leaderboard', [
                'stats' => $stats,
                'rankings' => $rankings,
                'performers' => $performers,
                'competition_start_date' => $competition->competition_start_date,
                'competition_end_date' => $competition->competition_end_date,
                // 'months' => [
                //     'January', 'February', 'March', 'April', 'May', 'June',
                //     'July', 'August', 'September', 'October', 'November', 'December'
                // ],
                // 'years' => range(now()->year - 1, now()->year + 1),
                'availableCompetitions' => $availableCompetitions,
                'competitionStatus' => $competitionStatus['status'],
                'targetDate' => $competitionStatus['targetDate'],
                'showTimer' => $competitionStatus['showTimer'],
                'competition' => $competition,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in competition leaderboard: ' . $e->getMessage());
            return back()->with('error', 'Unable to load competition data. Please try again.');
        }
    }

    public function getTraderData($accountNo, $start, $end)
    {
        // Ensure start and end are Carbon instances
        $startDate = \Carbon\Carbon::parse($start)->startOfDay();
        $endDate = \Carbon\Carbon::parse($end)->endOfDay();

        $account = Account::with([
            'trades',
            'dailyReports' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('report_date', [$startDate, $endDate])
                    ->orderBy('report_date');
            }
        ])->where('code', $accountNo)->firstOrFail();

        // Get daily reports data
        $labels = [];
        $equity = [];

        $dailyData = $account->dailyReports->keyBy(function ($item) {
            return $item->report_date->format('Y-m-d');
        });

        // Fill in any missing dates with the last known equity value
        $lastEquity = $account->equity ?? '0.00';
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayLabel = $currentDate->format('M d');
            $labels[] = $dayLabel;

            if ($dailyData->has($dateKey)) {
                $lastEquity = $dailyData[$dateKey]->equity;
            } else {
                $lastEquity = '0.00';
            }

            $equity[] = round($lastEquity, 2);
            $currentDate->addDay();
        }

        // Get trades data
        $trades = $account->trades->map(function($trade) {
            return [
                'position' => $trade->position_id,
                'open_time' => $trade->open_time,
                'close_time' => $trade->close_time ?? null,
                'symbol' => $trade->symbol,
                'volume' => $trade->volume,
                'profit' => $trade->profit
            ];
        })->sortByDesc('open_time')->values()->all();

        return response()->json([
            'chart_data' => [
                'labels' => $labels,
                'equity' => $equity
            ],
            'trades' => $trades
        ]);
    }

}
