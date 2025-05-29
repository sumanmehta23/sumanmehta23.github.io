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
        $rateToUse = 1;
        $pairData = PriceSnapshot::where('Symbol', $symbol)->first();

        if (!$pairData || $stopLossPips <= 0 || $accountSize <= 0) return 0;

        // Determine pip size
        if ($symbol === 'XAUUSD') {
            $pipSize = 0.01;
            $contractSize = 100; // 1 lot = 100 ounces
        } elseif (substr($symbol, -3) === 'JPY') {
            $pipSize = 0.01;
            $contractSize = 100000;
        } else {
            $pipSize = 0.0001;
            $contractSize = 100000;
        }

        // Determine conversion rate
        if ($pairData->component2 === "USD") {
            $rateToUse = 1;
        } elseif ($pairData->component1 === "USD" && $pairData->Price > 0) {
            $rateToUse = 1 / $pairData->Price;
        } else {
            $apiKey = config('services.1forge.api_key');
            $url = "https://api.1forge.com/convert?from={$pairData->component1}&to=USD&quantity=1&api_key=$apiKey";
            $res = json_decode(file_get_contents($url));
            if (isset($res->value) && is_numeric($res->value) && $pairData->Price > 0) {
                $rateToUse = $res->value / $pairData->Price;
            }
        }

        // Calculate pip value
        $pipValue = $pipSize * $contractSize * $rateToUse;

        // Calculate risk
        $riskAmount = $accountSize * ($riskPercent / 100);

        // Final lot size
        $lotSize = $riskAmount / ($stopLossPips * $pipValue);

        return number_format($lotSize, 2);
    }
}
