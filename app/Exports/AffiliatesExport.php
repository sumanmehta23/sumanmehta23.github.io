<?php

namespace App\Exports;

use App\Models\Affiliate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AffiliatesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Affiliate::orderBy('created_at', 'desc')->get();
    }

    /**
     * Define the headings for the Excel file
     */
    public function headings(): array
    {
        return [
            'Aff #ID',
            'Custom ID',
            'Country',
            'First Name',
            'Last Name',
            'Email',
            'Single Campaign Mode',
            'Status',
            'Email Verified',
            'Available Balance',
            'Promotional Materials',
            'Terms and Conditions',
            'Privacy Policy',
            'Blocked',
            '2FA Active',
            'Deleted',
            'Manager',
            'Referrer',
            'Company Name',
            'Payout Groups',
            'Payouts',
            'Affiliate Group',
            'Creation Date',
            'Last Login',
            'Website',
            'Phone',
            'Additional Info',
            'Created At',
        ];
    }

    /**
     * Map data to columns
     */
    public function map($affiliate): array
    {
        return [
            $affiliate->affiliate_code,
            $affiliate->custom_id,
            $affiliate->country,
            $affiliate->first_name,
            $affiliate->last_name,
            $affiliate->email,
            $affiliate->single_campaign_mode,
            strtoupper($affiliate->status),
            $affiliate->email_verified ? 'TRUE' : 'FALSE',
            $affiliate->available_balance,
            $affiliate->promotional_materials,
            $affiliate->terms_and_conditions,
            $affiliate->privacy_policy,
            $affiliate->blocked ? 'TRUE' : 'FALSE',
            $affiliate['2fa_active'] ? 'TRUE' : 'FALSE',
            $affiliate->deleted ? 'TRUE' : 'FALSE',
            $affiliate->manager,
            $affiliate->referrer,
            $affiliate->company_name,
            $affiliate->payout_groups,
            $affiliate->payouts,
            $affiliate->affiliate_group,
            $affiliate->creation_date ? $affiliate->creation_date->format('d-m-Y H:i') : '',
            $affiliate->last_login ? $affiliate->last_login->format('d-m-Y H:i') : '',
            $affiliate->website,
            $affiliate->phone,
            $affiliate->additional_info,
            $affiliate->created_at->format('Y-m-d H:i:s'),
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
