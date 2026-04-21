<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepositResource extends JsonResource
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
            'user_id' => $this->user_id,
            'amount' => $this->deposit_amount,
            'deposit_date' => $this->deposted_date ?? $this->created_at,
            'status' => $this->status ?? null,
            'deposit_type' => $this->deposit_type ?? null,
            'email' => $this->email ?? null,
            'source' => $this->source ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
