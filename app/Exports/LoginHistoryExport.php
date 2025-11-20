<?php

namespace App\Exports;

use App\Models\LoginHistory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class LoginHistoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = LoginHistory::with('user:id,email,fullname');

        // Apply filters if provided
        if (isset($this->filters['action']) && !empty($this->filters['action'])) {
            $query->where('action', $this->filters['action']);
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '' && $this->filters['status'] !== null) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['date_from']) && !empty($this->filters['date_from'])) {
            $query->whereDate('created_date_js', '>=', $this->filters['date_from']);
        }

        if (isset($this->filters['date_to']) && !empty($this->filters['date_to'])) {
            $query->whereDate('created_date_js', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('created_date_js', 'desc')->get();
    }

    /**
     * Define the headings for the Excel file
     */
    public function headings(): array
    {
        return [
            'User Email',
            'User Name',
            'IP Address',
            'Country',
            'Action',
            'Date',
            'Time',
            'Status',
        ];
    }

    /**
     * Map data to columns
     */
    public function map($loginHistory): array
    {
        $date = $loginHistory->created_date_js ? Carbon::parse($loginHistory->created_date_js) : null;
        
        return [
            $loginHistory->user->email ?? $loginHistory->email ?? 'N/A',
            $loginHistory->user->fullname ?? 'N/A',
            $loginHistory->ip ?? 'N/A',
            $loginHistory->country ?? 'Unknown',
            strtoupper($loginHistory->action ?? 'N/A'),
            $date ? $date->format('Y-m-d') : 'N/A',
            $date ? $date->format('H:i:s') : 'N/A',
            $loginHistory->status == 1 ? 'Success' : 'Failed',
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }
}

