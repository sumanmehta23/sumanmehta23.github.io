<?php

namespace App\Services;

use App\Models\Account;
use App\Models\TradeDeposit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CompetitionService
{
    const CACHE_PREFIX = 'competition_';
    const CACHE_TTL = 300; // 5 minutes

    /**
     * Get competition statistics for a specific month/year
     *
     * @param string|null $month The month (defaults to current month)
     * @param int|null $year The year (defaults to current year)
     * @return array
     */
    public function getCurrentStats($month = null, $year = null)
    {
        $month = $month ?? now()->format('F');
        $year = $year ?? now()->year;

        $accounts = Account::with(['user', 'accountType'])
            ->where('competition_month', $month)
            ->where('competition_year', $year)
            ->where('code', '!=', null)
            ->where('demo', true)
            ->whereHas('accountType', function($q) {
                $q->where('ac_name', 'Competition');
            })
            ->get();
        return [
            'participants' => $accounts->count(),
            'total_volume' => $accounts->sum('lots_completed'),
            'avg_equity' => $accounts->avg('equity'),
            'top_performers' => $accounts->sortByDesc('equity')->take(10),
            'top_performer' => $accounts->sortByDesc('equity')->first(),
            'month' => $month,
            'year' => $year
        ];
        // });
    }

    /**
     * Get trader performance data
     */
    public function getTraderData($accountId, $page = 1, $perPage = 10)
    {
        // Get account with ordered trade deposits
        $account = Account::with(['tradeDeposits' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($accountId);

        // Calculate pagination
        $trades = $account->tradeDeposits;
        $total = $trades->count();
        $trades = $trades->forPage($page, $perPage);

        // Calculate chart data (leave this as is for the full dataset)
        $monthStart = Carbon::now()->startOfMonth();
        $currentEquity = $account->initial_balance ?? 10000;
        $chartData = ['labels' => [], 'equity' => []];

        foreach ($account->tradeDeposits as $trade) {
            $currentEquity += $trade->profit;
            $chartData['labels'][] = $trade->created_at->format('Y-m-d H:i');
            $chartData['equity'][] = $currentEquity;
        }

        return [
            'chart_data' => $chartData,
            'trades' => $trades->values(),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Get competition rankings for a specific month/year
     */
    public function getRankings($month, $year)
    {
        // dd($year);
        // dd($month);
        return Account::with('user', 'accountType', 'trades')
            ->where('competition_month', $month)
            ->where('competition_year', $year)
            ->where('code', '!=', null)
            ->where('demo', true)
            ->orderByDesc('equity')
            ->get()
            ->map(function($account, $index) {
                return [
                    'rank' => $index + 1,
                    'name' => $account->user->fullname ?? $account->user->email,
                    'email' => $account->user->email,
                    'account_code' => $account->code,
                    'equity' => $account->equity,
                    'balance' => $account->balance,
                    'volume' => $account->lots_completed,
                    'total_trades' => $account->trades->count(),
                    'total_profit' => $account->balance - $account->initial_balance,
                ];
            });
    }

    /**
     * Get competition status and timing information
     */
    public function getCompetitionStatus($month, $year)
    {
        $requestedDate = Carbon::createFromDate($year, date('m', strtotime($month)), 1);
        $now = Carbon::now();

        // Competition starts on 1st of the month
        $competitionStart = $requestedDate->copy()->startOfMonth();
        // Registration ends on last day of previous month
        $registrationEnd = $competitionStart->copy()->subDay();
        // Competition ends on last day of the month
        $competitionEnd = $requestedDate->copy()->endOfMonth();

        if ($now->lt($competitionStart)) {
            // Upcoming competition
            return [
                'status' => 'Competition Starts In',
                'targetDate' => $competitionStart->format('Y-m-d H:i:s'),
                'showTimer' => true
            ];
        } elseif ($now->lte($competitionEnd)) {
            // Current month competition
            return [
                'status' => 'Competition Ends In',
                'targetDate' => $competitionEnd->format('Y-m-d H:i:s'),
                'showTimer' => true
            ];
        } else {
            // Past competition
            return [
                'status' => 'Competition Ended',
                'targetDate' => null,
                'showTimer' => false
            ];
        }
    }
}
