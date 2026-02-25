<?php

namespace App\Events;

use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TradeOpenedEvent
{
    use Dispatchable, SerializesModels;

    /** @var User */
    public $user;

    /** @var Trade */
    public $trade;

    public function __construct(User $user, Trade $trade)
    {
        $this->user = $user;
        $this->trade = $trade;
    }
}
