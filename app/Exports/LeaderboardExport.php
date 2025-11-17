<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaderboardExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $rankings;
    protected $limit;

    public function __construct($rankings, $limit = null)
    {
        $this->rankings = $rankings;
        $this->limit = $limit;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = collect($this->rankings);

        if ($this->limit) {
            $data = $data->take($this->limit);
        }

        return $data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Rank',
            'Account Number',
            'Name',
            'Email',
            'Balance',
            'Equity',
            'Profit',
            'Total Trades',
        ];
    }

    /**
     * @var mixed $ranking
     */
    public function map($ranking): array
    {
        return [
            $ranking['rank'],
            $ranking['account_code'],
            $ranking['name'],
            $ranking['email'],
            number_format($ranking['balance'], 2),
            number_format($ranking['equity'], 2),
            number_format($ranking['total_profit'], 2),
            $ranking['total_trades'],
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold
            1 => ['font' => ['bold' => true]],
        ];
    }
}
