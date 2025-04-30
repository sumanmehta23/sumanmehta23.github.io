<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;
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
                    'amount' => $withdrawal->withdraw_amount ?? $withdrawal->withdrawal_amount,
                    'withdraw_transaction_fee' => $withdrawal->withdraw_transaction_fee ?? $withdrawal->transaction_fee,
                    'type' => $withdrawal->withdraw_type,
                    'email' => $withdrawal->email,
                    'transaction_id' => $withdrawal->transaction_id,
                    'status' => $withdrawal->status,
                    'created_at' => Carbon::parse($withdrawal->created_at)->addHours(3)->toDateTimeString(),
                ];
            }),
        ];
    }
}
