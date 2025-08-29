<?php

namespace App\Services;

use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\MT5\MTEnDealAction;

class MT5Service
{
    protected $api;
    protected $dealerApi;

    public function __construct(MTWebAPI $api)
    {
        $this->api = $api;
    }

    public function connect()
    {
        // In testing environment, return success without actual connection
        if (app()->environment('testing')) {
            return MTRetCode::MT_RET_OK; // Return success code
        }

        // Production environment - use settings()
        $settings = settings();
        $serverIp = $settings['mt5_server_ip'];
        $serverPort = $settings['mt5_server_port'];
        $webLogin = $settings['mt5_server_web_login'];
        $webPassword = $settings['mt5_server_web_password'];

        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));

        return $this->api->Connect(
            $serverIp,
            $serverPort,
            300,
            $webLogin,
            $webPassword
        );
    }
    public function dealerConnect()
    {
        // In testing environment, return success without actual connection
        if (app()->environment('testing')) {
            return MTRetCode::MT_RET_OK; // Return success code
        }

        // Production environment - use settings()
        $settings = settings();
        $serverIp = $settings['mt5_server_ip'];
        $serverPort = $settings['mt5_server_port'];
        $webLogin = $settings['mt5_server_web_login'];
        $webPassword = $settings['mt5_server_web_password'];

        // $this->dealerApi->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));

        return $this->api->DealerConnect(
            $serverIp,
            $serverPort,
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );
    }
    public function getApi()
    {
        return $this->api;
    }
    public function getDealerApi()
    {
        return $this->dealerApi;
    }
}
