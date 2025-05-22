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

        $registrationDate = $this->created_at ? $this->created_at->toIso8601String() : null;
        $lastModifiedDate = $this->updated_at ? $this->updated_at->toIso8601String() : null;

        $isoCountry = $this->country ? substr($this->country, 0, 2) : null;

        return [
            'user_id' => $this->id, // Mandatory
            'user_additional_id' => $this->email, // Optional, using email as additional ID
            'cxd' => $request->query('cxd'), // Mandatory, getting from the query string
            'aff_id' => $this->ib1, // Optional
            'status' => $this->status, // Optional
            'account_type' => $accountType, // Optional
            'registration_date' => $registrationDate, // Mandatory
            'last_modified_date' => $lastModifiedDate, // Mandatory
            'iso_country' => $isoCountry, // Mandatory, taking first 2 letters of country
            'user_ip_address' => $request->ip(), // Optional, using current request IP as fallback
        ];
    }
}
