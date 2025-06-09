<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalResource extends JsonResource
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
            'transaction_type' => $this->withdraw_type,
            'transaction_amount' => $this->withdraw_amount,
            'transaction_date' => $this->withdraw_date,
            'transaction_base_currency' => $this->currency_type ?? null,
            'product_id' => $this->admin_remark,
        ];
    }
}
