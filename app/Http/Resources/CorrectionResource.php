<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CorrectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->deal_id,
            'user_id' => $this->account->user_id ?? null,
            'account_id' => $this->account_id,
            'code' => $this->account->code ?? null,
            'symbol' => 'correction',
            'position_volume' => null,
            'position_lot_volume' => null,
            'position_spread' => null,
            'position_open_date' => $this->created_at,
            'position_close_date' => $this->time_done,
            'position_base_currency' => null,
            'position_pl' => $this->profit,
            'position_status' => $this->comment,
            'position_type' => 'correction',
        ];
    }
}
