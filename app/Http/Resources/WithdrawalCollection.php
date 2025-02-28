<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class WithdrawalCollection extends ResourceCollection
{
    public $preserveKeys = true;
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array

    {
        // return ['data' => $this->collection];
        return [
            'data' => $this->collection->map(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->withdraw_amount,
                    'email' => $withdrawal->email,
                    'transaction_id' => $withdrawal->transaction_id,
                    'status' => $withdrawal->status,
                    'created_at' => $withdrawal->created_at->toDateTimeString(),
                ];
            }),
        ];
    }
}
