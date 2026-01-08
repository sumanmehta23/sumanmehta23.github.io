<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Maps trade data to Cellexpert API fields
     */
    public function toArray(Request $request): array
    {
        // check if $this->account->accountType->ac_group contains 'B-Book'

        $isBBook = (isset($this->account->accountType)) && str_contains($this->account->accountType->ac_group, 'B-Book');
        // if(!$this->corrections){
            return [
                'id' => $this->position_id,
                'user_id' => $this->account->user_id ?? null,
                'account_id' => $this->account_id,
                'code' => $this->code,
                // Cellexpert required fields
                'symbol' => $this->symbol, // Optional: The symbol for the traded asset
                'position_volume' => $this->volume, // Mandatory: The Monetary position amount (Volume)
                'position_lot_volume' => $this->volume_ext, // Optional: The LOT Volume for the position
                'position_spread' => $this->spread ?? null, // Optional: The position monetary spread
                'position_close_date' => $this->close_time, // Mandatory: The Position finalization date
                'position_open_date' => $this->open_time, // Optional: The position open time
                'position_base_currency' => $this->account->currency ?? null, // Optional: The transaction currency in 3 letter ISO format
                //flip profit from nevgative to positive and positive to negative  if B-Book
                'position_pl' => $isBBook ? $this->profit : 0, // Optional: The Profit or Loss derived from the position
                'position_trading_group' => $this->trading_group ?? null, // Optional: The associated trading group
                'position_status' => $this->status, // Optional: Indicating the outcome (Won, Lost, Cancelled)
                'position_type' => $this->type, // Optional: A description of the Position
            ];
    }
}
