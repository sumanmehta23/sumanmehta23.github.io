<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UniversalWithdrawalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Determine if this is a wallet withdrawal or trade withdrawal
        $isWalletWithdrawal = isset($this->withdraw_amount);

        return [
            'user_id' => $this->user_id,
            'transaction_id' => $this->transaction_id,
            'transaction_type' => $this->withdraw_type,
            'transaction_amount' => $isWalletWithdrawal ? $this->withdraw_amount : $this->withdrawal_amount,
            'transaction_date' => $this->withdraw_date,
            'transaction_base_currency' => $this->currency_type ?? null,
            'product_id' => $this->admin_remark,
            'withdrawal_source' => $isWalletWithdrawal ? 'wallet' : 'trade',
            'status' => $this->status,
            'code' => $this->code ?? null,
            'account_id' => $isWalletWithdrawal ? null : $this->account_id,
        ];
    }
}
