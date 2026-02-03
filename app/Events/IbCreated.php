<?php

namespace App\Events;

use App\Models\Ib1;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IbCreated
{
    use Dispatchable, SerializesModels;

    public Ib1 $ib;

    public function __construct(Ib1 $ib)
    {
        $this->ib = $ib;
    }
}
