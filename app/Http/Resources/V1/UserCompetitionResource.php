<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCompetitionResource extends JsonResource
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
            'account_id' => $this->id,
            'account_number' => $this->code ?? null,
            'user_email' => $this->user->email ?? null,
            'product_id' => $this->competition_product_id ?? null,
            'product' => [
                'id' => $this->product?->id ?? null,
                'name' => $this->product?->name ?? null,
            ],
            'status' => $this->competition_status ?? null,
            'start_date' => $this->formatDate($this->product?->competition_start_date),
            'end_date' => $this->formatDate($this->product?->competition_end_date),
            'email' => $this->competition_email ?? null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
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
