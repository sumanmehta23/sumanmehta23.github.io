<?php

namespace App\MT5;

class MTEnOrderAction
{
    const ORDER_EXECUTE = 0; // Market order execution
    const ORDER_PENDING = 1; // Pending order creation
    const ORDER_MODIFY = 2;  // Pending order modification
    const ORDER_DELETE = 3;  // Pending order deletion
}
