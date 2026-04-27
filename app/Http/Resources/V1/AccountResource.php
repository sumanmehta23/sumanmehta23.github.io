<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
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
            'user' => [
                'id' => $this->user->id ?? null,
                'email' => $this->user->email ?? null,
                'name' => ($this->user->first_name ?? '') . ' ' . ($this->user->last_name ?? ''),
            ],
            'account_number' => $this->code ?? null,
            'platform' => $this->trade_platform ?? null,
            'balance' => $this->balance ?? 0,
            'equity' => $this->equity ?? 0,
            'leverage' => $this->leverage ?? null,
            'status' => $this->status ?? null,
            'demo' => $this->demo ?? false,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
