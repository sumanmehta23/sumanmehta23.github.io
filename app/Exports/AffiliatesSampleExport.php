<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AffiliatesSampleExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * Sample data for the template
     */
    public function array(): array
    {
        return [
            [
                'mybrokerbuddy',                    // Aff #ID (affiliate_code)
                '1',                                 // Custom ID
                'John',                              // First Name
                'Doe',                               // Last Name
                'john.doe@example.com',              // Email
                'US',                                // Country
                'APPROVED',                          // Status (APPROVED/active/inactive/pending)
                'FALSE',                             // Email Verified (TRUE/FALSE)
                '$1,250.50',                         // Available Balance
                'Jay Ab',                            // Manager
                'referrer123',                       // Referrer
                'https://example.com',               // Website
                '+1234567890',                       // Phone
                'Acme Corp',                         // Company Name
                '10.00',                             // Commission Rate
                'Group A',                           // Affiliate Group
                'Payout Group 1',                    // Payout Groups
                'FALSE',                             // Blocked (TRUE/FALSE)
                'FALSE',                             // 2FA Active (TRUE/FALSE)
                '2024-01-15 10:30:00',               // Creation Date
                '2024-11-14 15:45:00',               // Last Login
                'Sample affiliate notes',            // Additional Info
            ],
            [
                'alitrades',
                '26',
                'Jane',
                'Smith',
                'jane.smith@example.com',
                'UK',
                'active',
                'TRUE',
                '$5,000.00',
                'Jay Ab',
                'mybrokerbuddy',
                'https://techltd.com',
                '+0987654321',
                'Tech Ltd',
                '15.50',
                'Group B',
                '50% PNL Deal',
                'FALSE',
                'TRUE',
                '2024-02-20 08:00:00',
                '2024-11-15 09:20:00',
                'Another sample user',
            ],
            [
                'testaffiliate',
                '100',
                'Test',
                'User',
                'test@example.com',
                'CA',
                'pending',
                'FALSE',
                '$0.00',
                'Jay Ab',
                '-',
                '-',
                '-',
                'Test Company',
                '0.00',
                'Default',
                'Standard',
                'FALSE',
                'FALSE',
                '2024-11-15 12:00:00',
                '-',
                'Test affiliate for import',
            ],
        ];
    }

    /**
     * Define the headings for the template
     * Using exact column names from user's Excel file
     */
    public function headings(): array
    {
        return [
            'Aff #ID',                  // affiliate_code (REQUIRED)
            'Custom ID',                // custom_id
            'First Name',               // first_name (REQUIRED)
            'Last Name',                // last_name (REQUIRED)
            'Email',                    // email (REQUIRED)
            'Country',                  // country
            'Status',                   // status (APPROVED/active/inactive/pending)
            'Email Verified',           // email_verified (TRUE/FALSE)
            'Available Balance',        // available_balance ($1,000.00 format supported)
            'Manager',                  // manager
            'Referrer',                 // referrer
            'Website',                  // website
            'Phone',                    // phone
            'Company Name',             // company_name
            'Commission Rate',          // commission_rate
            'Affiliate Group',          // affiliate_group
            'Payout Groups',            // payout_groups
            'Blocked',                  // blocked (TRUE/FALSE)
            '2FA Active',               // 2fa_active (TRUE/FALSE)
            'Creation Date',            // creation_date (YYYY-MM-DD HH:MM:SS)
            'Last Login',               // last_login (YYYY-MM-DD HH:MM:SS)
            'Additional Info',          // additional_info / notes
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
