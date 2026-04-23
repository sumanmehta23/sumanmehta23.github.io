<?php

namespace App\Http\Resources\V1;

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
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->firstname ?? null,
            'last_name' => $this->lastname ?? null,
            'full_name' => $this->fullname ?? '',
            'phone' => $this->number ?? null,
            'country' => $this->country ?? null,
            'status' => $this->status ?? null,
            'registration_date' => $this->created_at?->toIso8601String(),
            'last_modified' => $this->updated_at?->toIso8601String(),
            'kyc_status' => $this->kyc_status ?? 0,
            'kyc_verified_date' => $this->kyc_synced_at?->toIso8601String(),
            'kyc_rejection_reason' => $this->kyc_rejection_reason ?? null,
            'source' => $this->source ?? null,
            'referral_code' => $this->referral_code ?? null,
            'ib_id' => $this->parent_id ?? null,
            'ib_info' => $this->getIbInfo(),
            'rm_id' => $this->getRelationshipManagerId(),
            'relationship_manager' => $this->getRelationshipManagerInfo(),
            'email_verified' => $this->email_confirmed ? true : false,
        ];
    }

    /**
     * Get IB information from parent_id in nested set
     */
    private function getIbInfo(): ?array
    {
        if (! $this->parent_id) {
            return null;
        }

        $ib = \App\Models\Ib::where('id', $this->parent_id)
            ->select('id', 'email', 'referral_code', 'firstname', 'lastname', 'country', 'ib_status')
            ->first();

        if (! $ib) {
            return null;
        }

        return [
            'id' => $ib->id,
            'email' => $ib->email,
            'first_name' => $ib->firstname,
            'last_name' => $ib->lastname,
            'full_name' => ($ib->firstname ?? '').' '.($ib->lastname ?? ''),
            'referral_code' => $ib->referral_code,
            'country' => $ib->country,
            'ib_status' => $ib->ib_status,
        ];
    }

    /**
     * Get relationship manager ID from pre-loaded relationship
     */
    private function getRelationshipManagerId(): ?string
    {
        // Check if relationshipManager is loaded to avoid N+1 queries
        if (! $this->relationLoaded('relationshipManager')) {
            return null;
        }

        $rm = $this->relationshipManager;
        if (! $rm) {
            return null;
        }

        // Check if employee relationship is loaded
        if (! $rm->relationLoaded('employee')) {
            $employee = $rm->employee()->first();
        } else {
            $employee = $rm->employee;
        }

        return $employee?->id;
    }

    /**
     * Get relationship manager information from pre-loaded relationship
     */
    private function getRelationshipManagerInfo(): ?array
    {
        // Check if relationshipManager is loaded to avoid N+1 queries
        if (! $this->relationLoaded('relationshipManager')) {
            return null;
        }

        $rm = $this->relationshipManager;
        if (! $rm) {
            return null;
        }

        // Check if employee relationship is loaded
        if (! $rm->relationLoaded('employee')) {
            $employee = $rm->employee()->first();
        } else {
            $employee = $rm->employee;
        }

        if (! $employee) {
            return null;
        }

        return [
            'id' => $employee->id,
            'name' => $employee->username ?? null,
            'email' => $employee->email ?? null,
            'assigned_at' => $this->formatDate($rm->created_at),
        ];
    }

    /**
     * Format date to ISO8601 string, handling both Carbon and string values.
     */
    private function formatDate($date): ?string
    {
        if ($date === null) {
            return null;
        }

        if (is_string($date)) {
            return \Carbon\Carbon::parse($date)->toIso8601String();
        }

        return $date->toIso8601String();
    }
}
