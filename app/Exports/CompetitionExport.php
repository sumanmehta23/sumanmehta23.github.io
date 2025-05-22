<?php

namespace App\Exports;

use App\Models\Account;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class CompetitionExport implements FromQuery, WithMapping, WithHeadings, WithColumnWidths
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Account::query()
            ->whereNotNull('competition_month')
            ->whereNotNull('competition_year')
            ->where('demo', 1)
            ->whereHas('accountType', function ($query) {
                $query->where('ac_name', 'Competition');
            })
            ->with(['user', 'accountType']);

        // Apply filters if provided
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($subq) use ($search) {
                    $subq->where('email', 'like', "%{$search}%")
                        ->orWhere('fullname', 'like', "%{$search}%");
                })
                ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['month'])) {
            $query->where('competition_month', $this->filters['month']);
        }

        if (!empty($this->filters['year'])) {
            $query->where('competition_year', $this->filters['year']);
        }

        if (isset($this->filters['status'])) {
            $query->where('account_request_status', $this->filters['status']);
        }

        return $query;
    }

    public function map($account): array
    {
        try {
            $profit = $account->balance ? ($account->balance - 100000) : null;

            return [
                $account->code ?? 'Pending',
                $this->formatStatus($account->account_request_status),
                $account->user->fullname ?? 'N/A',
                $account->user->email ?? 'N/A',
                $this->formatMonthYear($account->competition_month, (int)$account->competition_year),
                $this->formatCurrency($account->initial_balance ?? 100000),
                $this->formatCurrency($account->balance),
                $this->formatCurrency($account->equity),
                $profit !== null ? $this->formatCurrency($profit) : 'N/A'
            ];
        } catch (\Exception $e) {
            // Log error if needed and return a row with N/A values
            \Log::error('Export error for account: ' . ($account->code ?? 'unknown'), ['error' => $e->getMessage()]);
            return array_fill(0, 9, 'N/A');
        }
    }

    public function headings(): array
    {
        return [
            'Account',
            'Status',
            'Name',
            'Email',
            'Month/Year',
            'Initial Balance',
            'Balance',
            'Equity',
            'Profit'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Account
            'B' => 10,  // Status
            'C' => 30,  // Name
            'D' => 35,  // Email
            'E' => 12,  // Month/Year
            'F' => 15,  // Initial Balance
            'G' => 15,  // Balance
            'H' => 15,  // Equity
            'I' => 15,  // Profit
        ];
    }

    private function formatStatus($status): string
    {
        return $status == 1 ? 'Approved' : 'Pending';
    }

    private function formatMonthYear(?string $month, ?int $year): string
    {   
        if (!$month || !$year) {
            return 'N/A';
        }
        return $month . '/' . $year;
    }

    private function formatCurrency(?float $value): string
    {
        if ($value === null) {
            return 'N/A';
        }
        return number_format($value, 2);
    }
}
