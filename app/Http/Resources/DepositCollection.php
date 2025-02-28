<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DepositCollection extends ResourceCollection
{
    public $preserveKeys = true;
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array

    {

        return [
            'data' => $this->collection->map(function ($deposit) {
                return [
                    'id' => $deposit->id,
                    'amount' => $deposit->deposit_amount,
                    'email' => $deposit->email,
                    'status' => $deposit->status,
                    'created_at' => $deposit->created_at->toDateTimeString(),
                ];
            }),
        ];
    }
}
