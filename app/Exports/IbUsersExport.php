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
    /**
     * Build the query for export
     */
    public function query()
    {
        // only select necessary fields for performance
        return Ib1::query()
            ->with(['user', 'ibWallet'])
            ->where('status', 1);
    }

    /**
     * Headings for Excel
     */
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

    /**
     * Map each row of data
     */
    public function map($ib): array
    {
        // Handle ibWallet relation (hasOne or hasMany)
        $wallets = $ib->ibWallet instanceof \Illuminate\Support\Collection
            ? $ib->ibWallet
            : collect([$ib->ibWallet])->filter();

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

    /**
     * Chunk size for processing
     */
    public function chunkSize(): int
    {
        return 1000; // process 1000 rows at a time
    }
}
