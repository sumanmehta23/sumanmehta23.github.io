<?php

namespace App\MT5;

class MTEnOrderType
{
    const ORDER_BUY                = 0; // Market Buy
    const ORDER_SELL               = 1; // Market Sell
    const ORDER_BUY_LIMIT          = 2; // Pending Buy Limit
    const ORDER_SELL_LIMIT         = 3; // Pending Sell Limit
    const ORDER_BUY_STOP           = 4; // Pending Buy Stop
    const ORDER_SELL_STOP          = 5; // Pending Sell Stop
    const ORDER_BUY_STOP_LIMIT     = 6; // Pending Buy Stop Limit
    const ORDER_SELL_STOP_LIMIT    = 7; // Pending Sell Stop Limit
}
