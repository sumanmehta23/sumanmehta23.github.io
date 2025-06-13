<?php

namespace App\MT5;

class MTEnOrderAction
{
    const ORDER_EXECUTE = 0;   // Open or close position (market execution)
    const ORDER_PENDING = 1;   // Place a pending order (limit/stop)
    const ORDER_MODIFY  = 2;   // Modify an existing pending order
    const ORDER_DELETE  = 3;   // Delete a pending order
}
