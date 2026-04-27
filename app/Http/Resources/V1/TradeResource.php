<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradeResource extends JsonResource
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
            'account_id' => $this->account_id,
            'code' => $this->code,
            'order_id' => $this->order_id,
            'symbol' => $this->symbol,
            'position_id' => $this->position_id,
            'type' => $this->type,
            'volume' => $this->volume,
            'volume_ext' => $this->volume_ext,
            'open_price' => $this->open_price,
            'close_price' => $this->close_price,
            'profit' => $this->profit,
            'swap' => $this->swap,
            'commission' => $this->commission,
            'sl' => $this->sl,
            'tp' => $this->tp,
            'comment' => $this->comment,
            'status' => $this->status,
            'state' => $this->state,
            'synced' => $this->synced,
            'open_time' => $this->open_time,
            'close_time' => $this->close_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
