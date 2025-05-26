<?php

namespace App\Console\Commands;

use App\Models\PriceSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdatePriceSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-price-snapshots {--source=alphavantage} {--symbols=} {--key=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update price snapshots using external APIs';

    protected $apiKeys = [
        'alphavantage' => null, // Will be set from .env or command line
    ];

    /**
     * The API endpoints.
     *
     * @var array
     */
    protected $endpoints = [
        'alphavantage' => 'https://www.alphavantage.co/query',
        'coingecko' => 'https://api.coingecko.com/api/v3',
        'exchangerate' => 'https://open.er-api.com/v6/latest',
    ];

    /**
     * Default forex symbols to track if none specified.
     *
     * @var array
     */
    protected $defaultSymbols = [
        'EURUSD',
        'GBPUSD',
        'USDJPY',
        'USDCHF',
        'AUDUSD',
        'USDCAD'
    ];

    /**
     * Default crypto symbols to track if using crypto source.
     *
     * @var array
     */
    protected $defaultCryptoSymbols = [
        'BTCUSD',
        'ETHUSD',
        'LTCUSD',
        'XRPUSD'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $source = $this->option('source');
        $symbols = $this->option('symbols') ? explode(',', $this->option('symbols')) : $this->defaultSymbols;

        // Get API key from command line or .env
        $this->apiKeys['alphavantage'] = $this->option('key') ?: env('ALPHAVANTAGE_API_KEY');

        $this->info("Updating price snapshots using {$source} API...");

        // Add crypto symbols if using a crypto source
        if ($source === 'coingecko') {
            $symbols = array_merge($symbols, $this->defaultCryptoSymbols);
        }

        $method = 'updateFrom' . ucfirst($source);

        if (method_exists($this, $method)) {
            $count = $this->$method($symbols);
            $this->info("Successfully updated {$count} price snapshots.");
        } else {
            $this->error("Source '{$source}' is not supported.");
        }
    }

    /**
     * Update price snapshots from Alpha Vantage API.
     *
     * @param array $symbols
     * @return int
     */
    protected function updateFromAlphavantage(array $symbols): int
    {
        $apiKey = $this->apiKeys['alphavantage'];

        if (!$apiKey) {
            $this->error('Alpha Vantage API key is required. Please provide it via --key option or ALPHAVANTAGE_API_KEY in .env file.');
            return 0;
        }

        $count = 0;
        $endpoint = $this->endpoints['alphavantage'];

        foreach ($symbols as $symbol) {
            $this->info("Fetching data for {$symbol}...");

            try {
                // Extract currency pairs
                $from = substr($symbol, 0, 3);
                $to = substr($symbol, 3, 3);

                // Query Alpha Vantage for forex data
                $response = Http::get($endpoint, [
                    'function' => 'CURRENCY_EXCHANGE_RATE',
                    'from_currency' => $from,
                    'to_currency' => $to,
                    'apikey' => $apiKey
                ]);

                if (!$response->successful()) {
                    $this->warn("Failed to fetch data for {$symbol}: " . $response->status());
                    continue;
                }

                $data = $response->json();

                // Check if we have data
                if (empty($data['Realtime Currency Exchange Rate'])) {
                    $this->warn("No data available for {$symbol}");
                    continue;
                }

                $exchangeData = $data['Realtime Currency Exchange Rate'];

                // Calculate values
                $price = (float) $exchangeData['5. Exchange Rate'];

                // Small spread for demo purposes
                $spread = $price * 0.0002; // 2 pips spread as example
                $ask = $price + $spread / 2;
                $bid = $price - $spread / 2;

                // Default values that make sense for forex
                $digits = $to === 'JPY' ? 3 : 5;
                $mulFactor = pow(10, $digits);

                // Get rate to USD for non-USD pairs
                $rateToUSD = 1.0;
                if ($to !== 'USD') {
                    // We'd need another API call, but for simplicity:
                    $rateToUSD = $to === 'EUR' ? 1.08 : ($to === 'GBP' ? 1.25 : 0.01);
                }

                // Create or update the record
                PriceSnapshot::updateOrCreate(
                    ['Symbol' => $symbol],
                    [
                        'component1' => $from,
                        'component2' => $to,
                        'Timestamp' => time(),
                        'Price' => $price,
                        'Ask' => $ask,
                        'Bid' => $bid,
                        'RateToUSD' => $rateToUSD,
                        'digits' => $digits,
                        'mul_factor' => $mulFactor,
                        'contractsize' => 100000, // Standard lot size for forex
                        'minlots' => 0.01,
                        'maxlots' => 50,
                        'mmr' => 2,
                        'leverage' => 100,
                    ]
                );

                $count++;

                // Sleep to respect API rate limits
                sleep(1);
            } catch (\Exception $e) {
                $this->error("Error processing {$symbol}: " . $e->getMessage());
                Log::error("Error updating price snapshot for {$symbol}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Update price snapshots from CoinGecko API.
     *
     * @param array $symbols
     * @return int
     */
    protected function updateFromCoingecko(array $symbols): int
    {
        $count = 0;
        $endpoint = $this->endpoints['coingecko'] . '/simple/price';

        // Extract crypto symbols
        $cryptoSymbols = array_filter($symbols, function ($symbol) {
            return in_array($symbol, $this->defaultCryptoSymbols);
        });

        if (empty($cryptoSymbols)) {
            $cryptoSymbols = $this->defaultCryptoSymbols;
        }

        $this->info("Fetching crypto data...");

        try {
            // Prepare crypto IDs for CoinGecko (they use ids like bitcoin, ethereum)
            $cryptoIds = [
                'BTCUSD' => 'bitcoin',
                'ETHUSD' => 'ethereum',
                'LTCUSD' => 'litecoin',
                'XRPUSD' => 'ripple',
            ];

            $ids = implode(',', array_intersect_key($cryptoIds, array_flip($cryptoSymbols)));

            if (empty($ids)) {
                $this->warn("No valid crypto symbols specified for CoinGecko API.");
                return 0;
            }

            // Query CoinGecko API
            $response = Http::get($endpoint, [
                'ids' => $ids,
                'vs_currencies' => 'usd',
                'include_24hr_change' => 'true',
                'include_market_cap' => 'true',
            ]);

            if (!$response->successful()) {
                $this->error("Failed to fetch data from CoinGecko: " . $response->status());
                return 0;
            }

            $data = $response->json();

            // Process each crypto
            foreach ($cryptoIds as $symbol => $id) {
                if (!isset($data[$id])) {
                    continue;
                }

                $cryptoData = $data[$id];

                // Calculate values
                $price = (float) $cryptoData['usd'];

                // Small spread for demo purposes
                $spread = $price * 0.001; // 0.1% spread
                $ask = $price + $spread / 2;
                $bid = $price - $spread / 2;

                // Extract crypto currency code (e.g., BTC from BTCUSD)
                $from = substr($symbol, 0, 3);
                $to = substr($symbol, 3, 3);

                // Create or update the record
                PriceSnapshot::updateOrCreate(
                    ['Symbol' => $symbol],
                    [
                        'component1' => $from,
                        'component2' => $to,
                        'Timestamp' => time(),
                        'Price' => $price,
                        'Ask' => $ask,
                        'Bid' => $bid,
                        'RateToUSD' => 1.0, // Already in USD
                        'digits' => $from === 'BTC' ? 2 : 5, // BTC has fewer digits
                        'mul_factor' => $from === 'BTC' ? 100 : 100000,
                        'contractsize' => $from === 'BTC' ? 1 : 10, // Smaller size for expensive crypto
                        'minlots' => 0.01,
                        'maxlots' => $from === 'BTC' ? 10 : 50, // Lower max for expensive crypto
                        'mmr' => 5, // Higher margin requirement for crypto
                        'leverage' => 20, // Lower leverage for crypto
                    ]
                );

                $count++;
            }
        } catch (\Exception $e) {
            $this->error("Error processing crypto data: " . $e->getMessage());
            Log::error("Error updating crypto price snapshots: " . $e->getMessage());
        }

        // Process forex symbols using Exchange Rate API
        $forexSymbols = array_diff($symbols, $cryptoSymbols);
        if (!empty($forexSymbols)) {
            $count += $this->updateFromExchangerate($forexSymbols);
        }

        return $count;
    }

    /**
     * Update price snapshots from Exchange Rate API.
     *
     * @param array $symbols
     * @return int
     */
    protected function updateFromExchangerate(array $symbols): int
    {
        $count = 0;
        $endpoint = $this->endpoints['exchangerate'];

        $this->info("Fetching forex data from Exchange Rate API...");

        try {
            // Get USD base rates first
            $response = Http::get($endpoint, [
                'base' => 'USD'
            ]);

            if (!$response->successful()) {
                $this->error("Failed to fetch data from Exchange Rate API: " . $response->status());
                return 0;
            }

            $data = $response->json();

            if (!isset($data['rates'])) {
                $this->warn("No rates available from Exchange Rate API");
                return 0;
            }

            $rates = $data['rates'];

            // Process each forex pair
            foreach ($symbols as $symbol) {
                // Skip crypto symbols
                if (in_array($symbol, $this->defaultCryptoSymbols)) {
                    continue;
                }

                $from = substr($symbol, 0, 3);
                $to = substr($symbol, 3, 3);

                // Calculate price based on available rates
                $price = null;

                if ($from === 'USD' && isset($rates[$to])) {
                    $price = $rates[$to];
                } elseif ($to === 'USD' && isset($rates[$from])) {
                    $price = 1 / $rates[$from];
                } elseif (isset($rates[$from]) && isset($rates[$to])) {
                    $price = $rates[$to] / $rates[$from];
                }

                if ($price === null) {
                    $this->warn("Could not calculate rate for {$symbol}");
                    continue;
                }

                // Small spread for demo purposes
                $spread = $price * 0.0002; // 2 pips spread
                $ask = $price + $spread / 2;
                $bid = $price - $spread / 2;

                // Default values that make sense for forex
                $digits = $to === 'JPY' ? 3 : 5;
                $mulFactor = pow(10, $digits);

                // Get rate to USD
                $rateToUSD = 1.0;
                if ($to !== 'USD') {
                    $rateToUSD = isset($rates[$to]) ? 1 / $rates[$to] : 1.0;
                }

                // Create or update the record
                PriceSnapshot::updateOrCreate(
                    ['Symbol' => $symbol],
                    [
                        'component1' => $from,
                        'component2' => $to,
                        'Timestamp' => time(),
                        'Price' => $price,
                        'Ask' => $ask,
                        'Bid' => $bid,
                        'RateToUSD' => $rateToUSD,
                        'digits' => $digits,
                        'mul_factor' => $mulFactor,
                        'contractsize' => 100000, // Standard lot size for forex
                        'minlots' => 0.01,
                        'maxlots' => 50,
                        'mmr' => 2,
                        'leverage' => 100,
                    ]
                );

                $count++;
            }
        } catch (\Exception $e) {
            $this->error("Error processing forex data: " . $e->getMessage());
            Log::error("Error updating forex price snapshots: " . $e->getMessage());
        }

        return $count;
    }
}
