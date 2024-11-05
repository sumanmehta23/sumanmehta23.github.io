<?php

namespace App\Services;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\MT5\MTEnDealAction;

class MT5Service
{
    protected $api;

    public function __construct(MTWebAPI $api)
    {
        $this->api = $api;
    }

    public function connect()
    {
        $settings = settings();
        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));

        return $this->api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );
    }
    public function getApi()
    {
        return $this->api;
    }
}
