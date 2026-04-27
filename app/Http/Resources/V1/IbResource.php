<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IbResource extends JsonResource
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
            'email' => $this->email ?? null,
            'first_name' => $this->firstname ?? null,
            'last_name' => $this->lastname ?? null,
            'full_name' => ($this->firstname ?? '').' '.($this->lastname ?? ''),
            'referral_code' => $this->referral_code ?? null,
            'country' => $this->country ?? null,
            'ib_status' => $this->ib_status ?? null,
            'parent_id' => $this->parent_id ?? null,
            'parent' => $this->getParentInfo(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * Get parent IB information if loaded
     */
    private function getParentInfo(): ?array
    {
        if (! $this->relationLoaded('parent')) {
            return null;
        }

        if (! $this->parent) {
            return null;
        }

        return [
            'id' => $this->parent->id,
            'email' => $this->parent->email,
            'first_name' => $this->parent->firstname,
            'last_name' => $this->parent->lastname,
            'full_name' => ($this->parent->firstname ?? '').' '.($this->parent->lastname ?? ''),
        ];
    }
}
