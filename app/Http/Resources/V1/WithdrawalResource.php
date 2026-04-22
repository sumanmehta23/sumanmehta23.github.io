<?php

namespace App\Http\Resources\V1;

use App\Enums\WithdrawalStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalResource extends JsonResource
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
            'email' => $this->email ?? null,
            'amount' => $this->withdraw_amount,
            'withdraw_transaction_fee' => $this->withdraw_transaction_fee ?? null,
            'withdraw_date' => $this->withdraw_date ?? $this->created_at,
            'withdraw_completed_date' => $this->withdraw_completed_date ?? null,
            'transaction_id' => $this->transaction_id ?? null,
            'status' => $this->status ?? null,
            'status_label' => WithdrawalStatusEnum::from($this->status)->label(),
            'type' => $this->withdraw_type ?? null,
            'source' => $this->source ?? null,
            'approved_at' => $this->approved_date ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
