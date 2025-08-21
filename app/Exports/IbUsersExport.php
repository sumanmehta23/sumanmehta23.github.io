<?php

namespace App\Exports;

use App\Models\Ib1;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Illuminate\Database\Eloquent\Builder;

class IbUsersExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldAutoSize, WithStrictNullComparison
{
    protected $filters;

    /**
     * Create a new export instance
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Build the query for export
     */
    public function query(): Builder
    {
        $query = Ib1::query()
            ->select([
                'ib1.id',
                'ib1.user_id',
                'ib1.status',
                'ib1.created_at'
            ])
            ->with([
                'user:id,fullname,email,number',
                'ibWallet:id,user_id,ib_wallet,ib_withdraw' // Fixed: specify exact columns for ibWallet
            ]);

        // Apply default filter for active IBs
        if (empty($this->filters['status'])) {
            $query->where('ib1.status', 1);
        } else {
            $query->where('ib1.status', $this->filters['status']);
        }

        // Apply date filters if provided
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('ib1.created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('ib1.created_at', '<=', $this->filters['date_to']);
        }

        // Apply search filter if provided
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('number', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('ib1.created_at', 'desc');
    }

    /**
     * Headings for Excel with professional formatting
     */
    public function headings(): array
    {
        return [
            'IB ID',
            'Full Name',
            'Email Address',
            'Phone Number',
            'Total Commission Earned',
            'Total Withdrawals',
            'Current Balance',
            'Account Status',
            'Registration Date',
            'Registration Time'
        ];
    }

    /**
     * Map each row of data with optimized processing
     */
    public function map($ib): array
    {
        // Handle ibWallet relation safely with null checks
        $wallets = collect();
        if ($ib->ibWallet && count($ib->ibWallet) > 0) {
            $wallets = $ib->ibWallet instanceof \Illuminate\Support\Collection
                ? $ib->ibWallet
                : collect($ib->ibWallet);
        }

        // Calculate totals with proper null handling
        $totalCommission = $wallets->sum(function($wallet) {
            return floatval($wallet->ib_wallet ?? 0);
        });
        
        $totalWithdrawal = $wallets->sum(function($wallet) {
            return floatval($wallet->ib_withdraw ?? 0);
        });
        
        $balance = $totalCommission - $totalWithdrawal;

        // Format currency safely
        $formattedCommission = '$' . number_format($totalCommission, 2);
        $formattedWithdrawal = '$' . number_format($totalWithdrawal, 2);
        $formattedBalance = '$' . number_format($balance, 2);

        // Status mapping with better descriptions
        $status = match (intval($ib->status)) {
            1 => 'Active IB',
            2 => 'Rejected',
            0 => 'Pending Request',
            default => 'Unknown Status',
        };

        // Handle timezone adjustment safely with null checks
        $createdAt = null;
        if ($ib->created_at) {
            try {
                $createdAt = Carbon::parse($ib->created_at)->addHours(3);
            } catch (\Exception $e) {
                $createdAt = null;
            }
        }

        // Safe user data extraction
        $userName = 'N/A';
        $userEmail = 'N/A';
        $userPhone = 'N/A';
        
        if ($ib->user) {
            $userName = $ib->user->fullname ?? 'N/A';
            $userEmail = $ib->user->email ?? 'N/A';
            $userPhone = $ib->user->number ?? 'N/A';
        }

        return [
            $ib->id ?? '',
            $userName,
            $userEmail,
            $userPhone,
            $formattedCommission,
            $formattedWithdrawal,
            $formattedBalance,
            $status,
            $createdAt ? $createdAt->format('Y-m-d') : 'N/A',
            $createdAt ? $createdAt->format('H:i:s') : 'N/A'
        ];
    }

    /**
     * Chunk size for processing large datasets
     */
    public function chunkSize(): int
    {
        return 500; // Reduced chunk size for better memory management
    }
}
