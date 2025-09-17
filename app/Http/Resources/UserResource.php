<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $accountType = null;
        if ($this->accounts->isNotEmpty() && $this->accounts->first()->accountType) {
            $accountType = $this->accounts->first()->accountType->ac_name;
        }

        // Handle created_at date with type checking
        $registrationDate = null;
        if ($this->created_at) {
            if ($this->created_at instanceof \Carbon\Carbon) {
                $registrationDate = $this->created_at->toIso8601String();
            } else {
                // If it's a string, try to parse it or just use it directly
                try {
                    $registrationDate = \Carbon\Carbon::parse($this->created_at)->toIso8601String();
                } catch (\Exception $e) {
                    $registrationDate = $this->created_at;
                }
            }
        }

        // Handle updated_at date with type checking
        $lastModifiedDate = null;
        if ($this->updated_at) {
            if ($this->updated_at instanceof \Carbon\Carbon) {
                $lastModifiedDate = $this->updated_at->toIso8601String();
            } else {
                // If it's a string, try to parse it or just use it directly
                try {
                    $lastModifiedDate = \Carbon\Carbon::parse($this->updated_at)->toIso8601String();
                } catch (\Exception $e) {
                    $lastModifiedDate = $this->updated_at;
                }
            }
        }

        $isoCountry = $this->countryDetail ? $this->countryDetail->country_alpha : null;

        return [
            'user_id' => $this->id, // Mandatory
            'full_name' => $this->fullname,
            'user_additional_id' => $this->email, // Optional, using email as additional ID
            'cxd' => $this->cxd, // Mandatory, getting from the query string
            'aff_id' => $this->ib1, // Optional
            'status' => $this->kyc_verify ? "active" : "inactive", // Optional
            'account_type' => $accountType, // Optional
            'registration_date' => $registrationDate, // Mandatory
            'last_modified_date' => $lastModifiedDate, // Mandatory
            'iso_country' => $isoCountry, // Mandatory, taking first 2 letters of country
            'user_ip_address' => $this->client_ip, // Optional, using current request IP as fallback
            'live_accounts' => $this->liveAccounts
        ];
    }
}
