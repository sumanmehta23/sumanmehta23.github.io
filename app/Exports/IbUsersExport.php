<?php

namespace App\Exports;

use App\Models\Ib1;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class IbUsersExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    public function query()
    {
        return Ib1::with(['user', 'ibWallet'])
            ->where('status', 1);
    }

    public function headings(): array
    {
        return [
            'Full Name',
            'Email',
            'Phone Number',
            'Tot. Comm.',
            'Tot. Withdrawal',
            'Status / Action',
            'Date',
            'Time'
        ];
    }

    public function map($ib): array
    {
        $wallets = $ib->ibWallet ?? collect();
        $total_deposit = '$' . number_format($wallets->sum('ib_wallet'), 2);
        $total_withdrawal = '$' . number_format($wallets->sum('ib_withdraw'), 2);

        $status = match ($ib->status) {
            1 => 'Active IB',
            2 => 'Rejected',
            0 => 'IB Requested',
            default => 'Not Requested',
        };

        $createdAt = Carbon::parse($ib->created_at)->addHours(3);

        return [
            $ib->user->fullname ?? 'N/A',
            $ib->user->email ?? 'N/A',
            $ib->user->number ?? 'N/A',
            $total_deposit,
            $total_withdrawal,
            $status,
            $createdAt->format('Y-m-d'),
            $createdAt->format('H:i:s')
        ];
    }

    public function chunkSize(): int
    {
        return 500;  // Process 100 records at a time
    }
}
