<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BonusTransactionResource extends JsonResource
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
            'bonus_amount' => $this->bonus_amount ?? null,
            'bonus_currency' => $this->bonus_currency ?? null,
            'bonus_type' => $this->bonus_type ?? null,
            'bonus_code' => $this->bonus_code_id ?? null,
            'bonus_code_description' => $this->bonus_code_desc ?? null,
            'bonus_date' => $this->bonus_date?->toIso8601String(),
            'status' => $this->status ?? null,
            'admin_remark' => $this->admin_remark ?? null,
            'created_by' => $this->created_by ?? null,
        ];
    }
}
