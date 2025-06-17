<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PriceSnapshot;

class LotSizeCalculatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return api response on validate failure
        $validator = validator(request()->all(), [
            'sym' => 'required|string',
            'accSize' => 'required|numeric',
            'rr' => 'required|numeric',
            'slpips' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        
        $sym = request('sym');
        $accSize = request('accSize');
        $rr = request('rr');
        $slpips = request('slpips');
        $lotSize = $this->getLotSize($sym, $accSize, $rr, $slpips);
        if ($lotSize) {
            return response()->json([
                'lotSize' => $lotSize,
            ]);
        } else {
            return response()->json([
                'error' => 'Unable to calculate lot size',
            ], 400);
        }
    }
    // function getLotSize($sym = '', $accSize = '', $rr = '', $slpips = '')
    // {
    //     $ratetouse = 1;

    //     // Get all currencies in the price_snapshot table
    //     $currenciesdata = PriceSnapshot::all()->toArray();

    //     if ($currenciesdata) {
    //         foreach ($currenciesdata as $currencydata) {
    //             if ($currencydata['Symbol'] == $sym) {
    //                 // If component2 is USD, no conversion needed
    //                 if ($currencydata['component2'] == "USD") {
    //                     break;
    //                 } else {
    //                     // If component1 is USD, invert the price
    //                     if ($currencydata['component1'] == "USD" && $currencydata['Price'] > 0) {
    //                         $ratetouse = 1 / $currencydata['Price'];
    //                         break;
    //                     } else {
    //                         // Retrieve rate for component1 against USD
    //                         $urltocall = "https://api.1forge.com/convert?from=" . $currencydata['component1'] . "&to=USD&quantity=1&api_key=v5KfAwd5pGB0MILxpnkS3sGkjxzvDJb4";
    //                         $urlresponse = file_get_contents($urltocall);
    //                         $urlresponsearray = json_decode($urlresponse);

    //                         if ($urlresponsearray && is_numeric($urlresponsearray->value) && $urlresponsearray->value > 0 && $currencydata['Price'] > 0) {
    //                             $ratetouse = ($urlresponsearray->value) / $currencydata['Price'];
    //                             break;
    //                         }
    //                     }
    //                 }
    //             }
    //         }

    //         $riskAmount = $accSize * ($rr / 100);
    //         $lotSize = $riskAmount / ($slpips * $ratetouse * 10);

    //         if (substr($sym, -3) == "JPY") {
    //             $lotSize = $lotSize / 100;
    //         }

    //         return number_format($lotSize ?? 0, 2);
    //     }
    // }

    function getLotSize($symbol, $accountSize, $riskPercent, $stopLossPips)
    {
        $pairData = PriceSnapshot::where('Symbol', $symbol)->first();
        if (!$pairData || $stopLossPips <= 0 || $accountSize <= 0 || $riskPercent <= 0) {
            return 0;
        }

        $baseCurrency = $pairData->component1;
        $quoteCurrency = $pairData->component2;
        $price = $pairData->Price;

        // Determine pip size and contract size
        $metals = ['XAUUSD', 'XAUEUR', 'XAUGBP', 'XAUAUD', 'XAUCHF', 'XAUJPY', 'XAGUSD', 'XAGEUR', 'XAGAUD', 'XPTUSD', 'XPDUSD'];
        if (in_array($symbol, $metals)) {
            switch ($symbol) {
                case 'XAGUSD':
                case 'XAGEUR':
                case 'XAGAUD':
                    $pipSize = 0.01;
                    $contractSize = 5000; // 1 lot = 5000 oz of silver
                    break;
                case 'XPTUSD':
                case 'XPDUSD':
                    $pipSize = 0.01;
                    $contractSize = 100; // 1 lot = 100 oz platinum/palladium
                    break;
                default:
                    $pipSize = 0.01;
                    $contractSize = 100; // 1 lot = 100 oz gold
            }
        } elseif (str_ends_with($symbol, 'JPY')) {
            $pipSize = 0.01;
            $contractSize = 100000;
        } else {
            $pipSize = 0.0001;
            $contractSize = 100000;
        }

        // Calculate pip value in quote currency
        $pipValueInQuoteCurrency = $pipSize * $contractSize;

        // Convert pip value to USD
        $pipValueInUSD = $this->convertToUSD($pipValueInQuoteCurrency, $quoteCurrency, $pairData);
        if ($pipValueInUSD <= 0) {
            return 0;
        }

        // Calculate risk amount
        $riskAmount = $accountSize * ($riskPercent / 100);

        // Calculate lot size
        $lotSize = $riskAmount / ($stopLossPips * $pipValueInUSD);

        return number_format($lotSize, 2);
    }

    private function convertToUSD($amount, $currency, $pairData)
    {
        if ($currency === 'USD') {
            return $amount;
        }

        // Try to find direct USD pair
        $usdPair = PriceSnapshot::where(function ($query) use ($currency) {
            $query->where('component1', $currency)->where('component2', 'USD')
                ->orWhere('component1', 'USD')->where('component2', $currency);
        })->first();
        if ($usdPair && $usdPair->Price > 0) {
            if ($usdPair->component2 === 'USD') {
                // e.g., CADUSD
                return $amount * $usdPair->Price;
            } else {
                // e.g., USDCAD
                return $amount / $usdPair->Price;
            }
        }

        // Fallback to API
        $apiKey = config('services.1forge.api_key');
        if (!$apiKey) {
            \Log::warning("No API key found for currency conversion", ['currency' => $currency]);
            return 0;
        }

        $url = "https://api.1forge.com/convert?from={$currency}&to=USD&quantity={$amount}&api_key={$apiKey}";
        $response = @file_get_contents($url);
        if ($response) {
            $data = json_decode($response);
            if (isset($data->value) && is_numeric($data->value) && $data->value > 0) {
                return $data->value;
            }
        }

        \Log::warning("Failed to convert currency to USD", [
            'currency' => $currency,
            'amount' => $amount,
            'response' => $response
        ]);
        return 0;
    }
}
