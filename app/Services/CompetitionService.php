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
            'month' => $month,
            'year' => $year
        ];
        // });
    }

    /**
     * Get trader performance data
     */
    public function getTraderData($accountId)
    {
        // Get account with ordered trade deposits
        $account = Account::with(['tradeDeposits' => function($query) {
            $query->orderBy('created_at', 'asc'); // Changed to ASC to calculate equity properly
        }])->findOrFail($accountId);

        $trades = $account->tradeDeposits;
        $monthStart = Carbon::now()->startOfMonth();

        // Calculate running equity - start with initial balance
        $currentEquity = $account->initial_balance ?? 10000;
        $chartData = ['labels' => [], 'equity' => []];

        // Initialize with starting point
        $chartData['labels'][] = $monthStart->format('M d');
        $chartData['equity'][] = $currentEquity;

        foreach ($trades as $trade) {
            if ($trade->created_at >= $monthStart) {
                $currentEquity += $trade->profit;
                $chartData['labels'][] = $trade->created_at->format('M d');
                $chartData['equity'][] = round($currentEquity, 2);
            }
        }

        // Map trades to the required format
        $formattedTrades = $trades->where('created_at', '>=', $monthStart)
            ->map(function($trade) {
                return [
                    'time' => $trade->created_at->toDateTimeString(),
                    'type' => $trade->action ?? ($trade->profit > 0 ? 'Buy' : 'Sell'),
                    'symbol' => $trade->symbol,
                    'volume' => $trade->volume,
                    'price' => $trade->price,
                    'profit' => round($trade->profit, 2)
                ];
            })
            ->values() // Reset array keys
            ->sortByDesc('time') // Most recent first
            ->values(); // Reset array keys again

        return [
            'chart_data' => $chartData,
            'trades' => $formattedTrades
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
