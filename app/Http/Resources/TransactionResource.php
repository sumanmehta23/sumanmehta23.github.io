<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'user_id' => $this->user_id,
            'transaction_id' => $this->transaction_id,
            'transaction_type' => $this->deposit_type,
            'transaction_amount' => $this->deposit_amount,
            'transaction_date' => $this->deposted_date,
            'transaction_base_currency' => $this->currency_type ?? 'USD',
            'product_id' => $this->admin_remark,
        ];
    }
}
