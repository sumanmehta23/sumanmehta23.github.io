<?php

namespace App\Imports;

use App\Models\Affiliate;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AffiliatesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithBatchInserts, WithChunkReading
{
    use SkipsFailures;

    protected $importedCount = 0;
    protected $updatedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];

    /**
     * Normalize column names - convert to lowercase and replace spaces/special chars
     */
    private function normalizeKey(string $key): string
    {
        return Str::slug(strtolower($key), '_');
    }

    /**
     * Get value from row with multiple possible keys
     */
    private function getRowValue(array $row, array $possibleKeys, $default = null)
    {
        foreach ($possibleKeys as $key) {
            $normalizedKey = $this->normalizeKey($key);
            if (isset($row[$normalizedKey]) && !empty($row[$normalizedKey])) {
                return $row[$normalizedKey];
            }
        }
        return $default;
    }

    /**
     * Map column headers to match the Excel file
     */
    private function mapRow(array $row): array
    {
        // Normalize all keys in the row
        $normalizedRow = [];
        foreach ($row as $key => $value) {
            $normalizedRow[$this->normalizeKey($key)] = $value;
        }
        
        // Map various column name formats to our expected format
        $mapped = [];
        
        // Map affiliate code (Aff #ID)
        $mapped['affiliate_code'] = $this->getRowValue($normalizedRow, [
            'aff_id', 'aff_number_id', 'affiliate_code', 'affiliate_id'
        ]);
        
        // Map custom ID
        $mapped['custom_id'] = $this->getRowValue($normalizedRow, [
            'custom_id', 'id'
        ]);
        
        // Map first name
        $mapped['first_name'] = $this->getRowValue($normalizedRow, [
            'first_name', 'firstname', 'fname'
        ]);
        
        // Map last name
        $mapped['last_name'] = $this->getRowValue($normalizedRow, [
            'last_name', 'lastname', 'lname'
        ]);
        
        // Map email (no validation, just required)
        $mapped['email'] = $this->getRowValue($normalizedRow, ['email']);
        
        // Map phone
        $mapped['phone'] = $this->getRowValue($normalizedRow, ['phone', 'telephone', 'mobile']);
        
        // Map country
        $mapped['country'] = $this->getRowValue($normalizedRow, ['country']);
        
        // Map company name
        $mapped['company_name'] = $this->getRowValue($normalizedRow, [
            'company_name', 'company', 'business_name'
        ]);
        
        // Map website
        $website = $this->getRowValue($normalizedRow, ['website', 'url', 'site']);
        if ($website === '-' || empty($website)) {
            $website = null;
        }
        $mapped['website'] = $website;
        
        // Map status (convert APPROVED to active, etc.)
        $status = $this->getRowValue($normalizedRow, ['status'], 'active');
        $statusUpper = strtoupper($status);
        if ($statusUpper === 'APPROVED' || $statusUpper === 'ACTIVE') {
            $status = 'active';
        } elseif ($statusUpper === 'REJECTED' || $statusUpper === 'BLOCKED' || $statusUpper === 'INACTIVE') {
            $status = 'inactive';
        } elseif ($statusUpper === 'PENDING') {
            $status = 'pending';
        } else {
            $status = 'active'; // Default
        }
        $mapped['status'] = $status;
        
        // Map Single Campaign Mode
        $mapped['single_campaign_mode'] = $this->getRowValue($normalizedRow, [
            'single_campaign_mode'
        ]);
        
        // Map Email Verified (TRUE/FALSE)
        $emailVerified = $this->getRowValue($normalizedRow, ['email_verified', 'email_verifie']);
        $mapped['email_verified'] = ($emailVerified === 'TRUE' || $emailVerified === '1' || $emailVerified === true);
        
        // Map Available Balance - clean up currency symbols and commas
        $balance = $this->getRowValue($normalizedRow, [
            'available_balance', 'available_balan', 'balance'
        ], '0');
        // Remove currency symbols, commas, and spaces
        $balance = preg_replace('/[^0-9.\-]/', '', (string)$balance);
        $mapped['available_balance'] = $balance !== '' ? (float)$balance : 0.00;
        
        // Map Promotional Materials (TRUE/FALSE)
        $promoMaterials = $this->getRowValue($normalizedRow, ['promotional_materials', 'promotional_ma']);
        $mapped['promotional_materials'] = ($promoMaterials === 'TRUE' || $promoMaterials === 'FALSE') ? $promoMaterials : null;
        
        // Map Terms and Conditions (TRUE/FALSE)
        $terms = $this->getRowValue($normalizedRow, ['terms_and_conditions', 'terms_and_con']);
        $mapped['terms_and_conditions'] = ($terms === 'TRUE' || $terms === 'FALSE') ? $terms : null;
        
        // Map Privacy Policy (TRUE/FALSE)
        $privacy = $this->getRowValue($normalizedRow, ['privacy_policy']);
        $mapped['privacy_policy'] = ($privacy === 'TRUE' || $privacy === 'FALSE') ? $privacy : null;
        
        // Map Blocked (TRUE/FALSE or 0)
        $blocked = $this->getRowValue($normalizedRow, ['blocked']);
        $mapped['blocked'] = ($blocked === 'TRUE' || $blocked === '1' || $blocked === true);
        
        // Map 2FA Active (TRUE/FALSE or 0)
        $twoFa = $this->getRowValue($normalizedRow, ['2fa_active', 'twofa_active']);
        $mapped['2fa_active'] = ($twoFa === 'TRUE' || $twoFa === '1' || $twoFa === true);
        
        // Map Deleted (TRUE/FALSE or 0)
        $deleted = $this->getRowValue($normalizedRow, ['deleted']);
        $mapped['deleted'] = ($deleted === 'TRUE' || $deleted === '1' || $deleted === true);
        
        // Map Manager
        $mapped['manager'] = $this->getRowValue($normalizedRow, ['manager']);
        
        // Map Referrer
        $mapped['referrer'] = $this->getRowValue($normalizedRow, ['referrer', 'company_name']);
        
        // Map Payout Groups
        $mapped['payout_groups'] = $this->getRowValue($normalizedRow, ['payout_groups']);
        
        // Map Payouts
        $mapped['payouts'] = $this->getRowValue($normalizedRow, ['payouts']);
        
        // Map Affiliate Group
        $mapped['affiliate_group'] = $this->getRowValue($normalizedRow, ['affiliate_group']);
        
        // Map Creation Date
        $creationDate = $this->getRowValue($normalizedRow, ['creation_date']);
        if ($creationDate && $creationDate !== '-') {
            try {
                $mapped['creation_date'] = \Carbon\Carbon::parse($creationDate);
            } catch (\Exception $e) {
                $mapped['creation_date'] = null;
            }
        } else {
            $mapped['creation_date'] = null;
        }
        
        // Map Last Login
        $lastLogin = $this->getRowValue($normalizedRow, ['last_login']);
        if ($lastLogin && $lastLogin !== '-') {
            try {
                $mapped['last_login'] = \Carbon\Carbon::parse($lastLogin);
            } catch (\Exception $e) {
                $mapped['last_login'] = null;
            }
        } else {
            $mapped['last_login'] = null;
        }
        
        // Map Additional Info
        $mapped['additional_info'] = $this->getRowValue($normalizedRow, [
            'additional_info', 'notes', 'comments', 'remarks'
        ]);
        
        // Commission rate - clean up currency symbols and commas
        $commission = $this->getRowValue($normalizedRow, [
            'commission_rate', 'commission', 'rate'
        ], '0');
        // Remove currency symbols, commas, and spaces
        $commission = preg_replace('/[^0-9.\-]/', '', (string)$commission);
        $mapped['commission_rate'] = $commission !== '' ? (float)$commission : 0.00;
        
        return $mapped;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Map the row to our expected format
            $mapped = $this->mapRow($row);
            
            // Skip if no affiliate code or email
            if (empty($mapped['affiliate_code']) || empty($mapped['email'])) {
                return null;
            }
            
            // Check if affiliate exists by email, affiliate_code, or custom_id
            $query = Affiliate::where('email', $mapped['email'])
                ->orWhere('affiliate_code', $mapped['affiliate_code']);
            
            // Also check custom_id if present
            if (!empty($mapped['custom_id'])) {
                $query->orWhere('custom_id', $mapped['custom_id']);
            }
            
            $affiliate = $query->first();

            if ($affiliate) {
                // Skip this row - affiliate already exists (duplicate)
                // Don't update, just skip
                $this->skippedCount++;
                return null;
            }

            // Create new affiliate with all fields - ensure all fields are present
            $this->importedCount++;
            
            // Prepare complete data with all fields (don't filter nulls for batch insert)
            $affiliateData = [
                'affiliate_code' => $mapped['affiliate_code'],
                'custom_id' => $mapped['custom_id'] ?? null,
                'first_name' => $mapped['first_name'] ?? 'N/A',
                'last_name' => $mapped['last_name'] ?? 'N/A',
                'email' => $mapped['email'],
                'phone' => $mapped['phone'] ?? null,
                'country' => $mapped['country'] ?? null,
                'company_name' => $mapped['company_name'] ?? null,
                'website' => $mapped['website'] ?? null,
                'commission_rate' => $mapped['commission_rate'] ?? 0.00,
                'status' => $mapped['status'] ?? 'active',
                'notes' => null,
                'single_campaign_mode' => $mapped['single_campaign_mode'] ?? null,
                'email_verified' => $mapped['email_verified'] ?? false,
                'available_balance' => $mapped['available_balance'] ?? 0.00,
                'promotional_materials' => $mapped['promotional_materials'] ?? null,
                'terms_and_conditions' => $mapped['terms_and_conditions'] ?? null,
                'privacy_policy' => $mapped['privacy_policy'] ?? null,
                'blocked' => $mapped['blocked'] ?? false,
                '2fa_active' => $mapped['2fa_active'] ?? false,
                'deleted' => $mapped['deleted'] ?? false,
                'manager' => $mapped['manager'] ?? null,
                'referrer' => $mapped['referrer'] ?? null,
                'payout_groups' => $mapped['payout_groups'] ?? null,
                'payouts' => $mapped['payouts'] ?? null,
                'affiliate_group' => $mapped['affiliate_group'] ?? null,
                'creation_date' => $mapped['creation_date'] ?? null,
                'last_login' => $mapped['last_login'] ?? null,
                'additional_info' => $mapped['additional_info'] ?? null,
            ];
            
            return new Affiliate($affiliateData);
        } catch (\Exception $e) {
            Log::error('Affiliate import error: ' . $e->getMessage(), ['row' => $row]);
            $this->errors[] = "Row error: {$e->getMessage()}";
            return null;
        }
    }

    /**
     * Validation rules for each row
     */
    public function rules(): array
    {
        return [
            // No validation - just import everything
        ];
    }
    
    /**
     * Prepare rows before validation
     */
    public function prepareForValidation($data, $index)
    {
        // Clean up the data
        $data = array_map(function($value) {
            return is_string($value) ? trim($value) : $value;
        }, $data);
        
        return $data;
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'affiliate_code.required' => 'Affiliate code is required.',
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'website.url' => 'Website must be a valid URL.',
            'commission_rate.numeric' => 'Commission rate must be a number.',
            'status.in' => 'Status must be active, inactive, or pending.',
        ];
    }

    /**
     * Get imported count
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * Get updated count
     */
    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    /**
     * Get skipped count
     */
    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Batch size for bulk insert
     */
    public function batchSize(): int
    {
        return 500;
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 500;
    }
}
