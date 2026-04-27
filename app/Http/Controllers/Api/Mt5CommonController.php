<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UniversalMT5Service;
use Illuminate\Http\JsonResponse;

class Mt5CommonController extends Controller
{
    protected $mt5Service;

    public function __construct(UniversalMT5Service $mt5Service)
    {
        $this->mt5Service = $mt5Service;
    }

    /**
     * Get MT5 server common information.
     */
    public function get(): JsonResponse
    {
        try {
            $serverInfo = $this->mt5Service->getServerCommon();

            if ($serverInfo === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve server information',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $serverInfo,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving server information',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}