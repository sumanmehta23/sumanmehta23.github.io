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
use App\Services\MT5Service;
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
    public function __construct(MT5Service $mt5Service, MailService $mailService,CompetitionService $competitionService)
    {
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
        $this->competitionService = $competitionService;
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

        // Get all competition account IDs
        $accountIds = Account::whereNotNull('competition_month')
            ->whereNotNull('competition_year')
            ->where('demo', true)
            ->pluck('id')
            ->toArray();

        // Get ranks for all competition accounts
        $accounts = Account::whereIn('id', $accountIds)
            ->get()
            ->map(function ($account) {
                return [
                    'month' => $account->competition_year . '-' . str_pad(date('m', strtotime($account->competition_month)), 2, '0', STR_PAD_LEFT),
                    'account_id' => $account->id,
                    'total_amount' => $account->balance ?? 0
                ];
            });
            // dd($accounts);
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

        // Add rank information to each result
        $results = $results->map(function ($account) use ($ranks) {
            $month = $account->competition_year . '-' . str_pad(date('m', strtotime($account->competition_month)), 2, '0', STR_PAD_LEFT);
            if (isset($ranks[$month][$account->id])) {
                $account->rank = $ranks[$month][$account->id]['rank'];
                $account->total_participants = count($ranks[$month]);
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

        // if (RateLimiter::tooManyAttempts($key, 1)) {
        //     $retryAfter = RateLimiter::availableIn($key);
        //     return redirect()->back()
        //         ->with('error', "Please wait {$retryAfter} seconds before trying again.");
        // }
        // RateLimiter::hit($key, 10);

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


        $existingCompetition = Account::with('accountType')
            ->where('user_id', $user->id)
            ->where('demo', true)
            ->whereHas('accountType', function ($query) use ($start_date, $end_date) {
                $query->where('competition_start_date', '>=',$start_date)
                    ->where('competition_end_date','<=', $end_date)
                    ->where('ac_name', 'like', '%Competition%');
            })
            ->first();

        // dd($existingCompetition);

        if ($existingCompetition) {
            return redirect()->back()->with('error', 'Competition already purchased for ' . $competitionMonth . '.');
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
            ]);

            if ($useraccount) {
                $from = $settings['email_from_address'];
                $emailSubject = 'Competition Requested';
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";

                $content = "<div>Thank you for choosing LQH Markets. Your competition will starts from {$start_date} and end on {$end_date}.</div>
                            <p>If you need any assistance, our support team is available 24/7 at support@lqhmarkets.com</p>
                            <p>Best Regards.</p><p>LQH Markets Team</p>";

                $templateVars = [
                    'name' => $user->fullname,
                    'email' => $settings['email_from_address'],
                    'content' => $content,
                    'title_right' => "Competition Request Pending",
                    'subtitle_right' => "",
                ];

                $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);

                return redirect()->back()->with('success', 'Competition Request Received. Your request has been submitted.');
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
            ->whereNotNull('competition_month')
            ->whereNotNull('competition_year')
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
        $month = $request->query('month', now()->format('F'));
        $year = $request->query('year', now()->year);

        try {
            // Get competition data from service
            $stats = $this->competitionService->getCurrentStats($month, $year);
            $rankings = $this->competitionService->getRankings($month, $year);
            $competitionStatus = $this->competitionService->getCompetitionStatus($month, $year);
            // Get available competitions for filtering
            $availableCompetitions = Account::select('competition_month', 'competition_year')
                ->where('demo', true)
                ->whereNotNull('competition_month')
                ->whereNotNull('competition_year')
                ->distinct()
                ->orderBy('competition_year', 'desc')
                ->orderBy('competition_month', 'desc')
                ->get()
                ->groupBy('competition_year');
            // dump($stats);
            // dump($rankings);
            // dump($competitionStatus);
            // dd($availableCompetitions);
            return view('competitions.leaderboard', [
                'stats' => $stats,
                'rankings' => $rankings,
                'currentMonth' => $month,
                'currentYear' => $year,
                'months' => [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ],
                'years' => range(now()->year - 1, now()->year + 1),
                'availableCompetitions' => $availableCompetitions,
                'competitionStatus' => $competitionStatus['status'],
                'targetDate' => $competitionStatus['targetDate'],
                'showTimer' => $competitionStatus['showTimer']
            ]);
        } catch (\Exception $e) {
            Log::error('Error in competition leaderboard: ' . $e->getMessage());
            return back()->with('error', 'Unable to load competition data. Please try again.');
        }
    }

    public function getTraderData($accountNo, $month, $year)
    {
        $startDate = Carbon::createFromFormat('F Y', "$month $year")->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

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


        // Get the last 31 days of data (30 days + current day)

        $dailyData = $account->dailyReports->keyBy(function ($item) {
                        return $item->report_date->format('Y-m-d');
                    });


        // Fill in any missing dates with the last known equity value
        $lastEquity = $account->equity ?? '0.00';
        $daysInCurrentMonth = now()->daysInMonth;

        if($month == now()->format('F') && $year == now()->year){
            $today = now();
            $monthEnd = $today;
        }else{
            $today = $startDate;
            $monthEnd = $endDate;
        }

        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $monthEnd; // Up to today
        $currentDate = $startOfMonth->copy();

        while ($currentDate <= $endOfMonth) {
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
