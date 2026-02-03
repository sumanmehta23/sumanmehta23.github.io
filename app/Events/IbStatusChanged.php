<?php

namespace App\Events;

use App\Models\Ib1;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IbStatusChanged
{
    use Dispatchable, SerializesModels;

    public Ib1 $ib;
    public int $oldStatus;
    public int $newStatus;

    public function __construct(Ib1 $ib, int $oldStatus, int $newStatus)
    {
        $this->ib = $ib;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}
