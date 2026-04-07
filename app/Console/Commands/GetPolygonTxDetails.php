<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetPolygonTxDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage:
     * php artisan polygon:usd 0x40f26f491818f7cf81ed6060520bb87ace409500024f7bc09e4f22a6772885e8
     *
     * Options:
     * --api-key=YOUR_KEY   Use PolygonScan/Etherscan API key (optional)
     * --price=0.17         Manually specify native token USD price (optional)
     * --native=POL         Force native token symbol (POL or MATIC). Default=AUTO
     */
    protected $signature = 'polygon:usd
                            {hash : The Polygon transaction hash}
                            {--api-key= : PolygonScan API key (optional)}
                            {--price= : Manual native/USD price (optional)}
                            {--native=AUTO : Native token symbol to use: AUTO|POL|MATIC}';

    protected $description = 'Get transaction value in USD for a given Polygon transaction hash at the time of transaction (supports POL or MATIC as native)';

    public function handle()
    {
        $hash = (string)$this->argument('hash');
        $hash = trim($hash);                          // remove whitespace/newlines
        $hash = preg_replace('/[^a-fA-F0-9x]/', '', $hash); // remove any non-hex chars
        $apiKey = $this->option('api-key') ?? env('POLYGONSCAN_API_KEY', '');

        $this->info("Fetching transaction: {$hash}");

        // 1️⃣ Fetch transaction data from Polygon RPC
        $rpcResponse = Http::post('https://polygon-bor-rpc.publicnode.com', [
            'jsonrpc' => '2.0',
            'method'  => 'eth_getTransactionByHash',
            'params'  => [$hash],
            'id'      => 1,
        ]);
        if ($rpcResponse->failed() || !isset($rpcResponse['result'])) {
            $this->error('Failed to fetch transaction data from Polygon RPC.');
            return Command::FAILURE;
        }

        $tx = $rpcResponse->json('result');

        if (!$tx) {
            $this->error('Transaction not found.');
            return Command::FAILURE;
        }

        // Get block timestamp
        $blockNumber = hexdec($tx['blockNumber'] ?? '0x0');
        $blockResponse = Http::post('https://polygon-bor-rpc.publicnode.com', [
            'jsonrpc' => '2.0',
            'method' => 'eth_getBlockByNumber',
            'params' => ['0x' . dechex($blockNumber), false],
            'id' => 3,
        ]);

        $block = $blockResponse->json('result');
        $timestamp = hexdec($block['timestamp'] ?? '0x0');
        $transactionDate = date('Y-m-d H:i:s', $timestamp);

        $this->info("Transaction date: {$transactionDate} UTC");

        // 2️⃣ Fetch transaction receipt to get logs (for token transfers)
        $receiptResponse = Http::post('https://polygon-bor-rpc.publicnode.com', [
            'jsonrpc' => '2.0',
            'method' => 'eth_getTransactionReceipt',
            'params' => [$hash],
            'id' => 2,
        ]);

        $receipt = $receiptResponse->json('result');

        // 3️⃣ Check for native value (POL or MATIC)
        $valueWei = hexdec($tx['value'] ?? '0x0');
        $valueNative = $valueWei / 1e18;

        $result = [
            'hash' => $hash,
            'from' => $tx['from'] ?? null,
            'to' => $tx['to'] ?? null,
            'blockNumber' => $blockNumber,
            'timestamp' => $timestamp,
            'date' => $transactionDate,
        ];

        // 4️⃣ If native value is 0, check for ERC-20 token transfers
        if ($valueNative == 0 && $receipt && isset($receipt['logs'])) {
            $this->info("Native value is 0, checking for token transfers...");

            // Look for Transfer events (topic0 = keccak256("Transfer(address,address,uint256)"))
            $transferEventSignature = '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';

            foreach ($receipt['logs'] as $log) {
                if (isset($log['topics'][0]) && $log['topics'][0] === $transferEventSignature) {
                    // This is a token transfer
                    $tokenAddress = $log['address'];
                    $tokenValue = hexdec($log['data'] ?? '0x0');

                    $this->info("Found token transfer. Token contract: {$tokenAddress}");

                    // Try to get token info with historical price
                    $tokenInfo = $this->getTokenInfo($tokenAddress, $tokenValue, $timestamp, $apiKey);

                    if ($tokenInfo) {
                        $result['token_address'] = $tokenAddress;
                        $result['token_name'] = $tokenInfo['name'];
                        $result['token_symbol'] = $tokenInfo['symbol'];
                        $result['token_decimals'] = $tokenInfo['decimals'];
                        $result['token_amount'] = $tokenInfo['amount'];
                        $result['token_usd_rate_at_time'] = $tokenInfo['usd_rate'];
                        $result['value_usd_at_time'] = $tokenInfo['value_usd'];

                        $this->line(json_encode($result, JSON_PRETTY_PRINT));
                        return Command::SUCCESS;
                    }

                    // Fallback: show raw token value
                    $result['token_address'] = $tokenAddress;
                    $result['token_value_raw'] = $tokenValue;
                    $result['note'] = 'Token detected but unable to fetch price data';

                    break;
                }
            }
        }

        // 5️⃣ If we have native value, get historical USD price
        if ($valueNative > 0) {
            // Resolve native symbol: AUTO (cutover date), or forced via --native
            $nativeOpt = strtoupper((string)($this->option('native') ?? 'AUTO'));
            $cutoverTs = strtotime('2024-10-26 00:00:00 UTC'); // Approx Polygon POL migration cutover
            $nativeSymbol = $nativeOpt === 'AUTO'
                ? (($timestamp >= $cutoverTs) ? 'POL' : 'MATIC')
                : ($nativeOpt === 'POL' || $nativeOpt === 'MATIC' ? $nativeOpt : 'POL');

            // Check if manual price is provided
            $manualPrice = $this->option('price');

            if ($manualPrice && is_numeric($manualPrice) && $manualPrice > 0) {
                $priceUsd = (float) $manualPrice;
                $this->info("Using manually specified {$nativeSymbol} price: \${$priceUsd}");
                $result['price_source'] = 'manual';
            } else {
                $priceUsd = $this->getHistoricalNativePrice($nativeSymbol, $timestamp, $apiKey);

                if ($priceUsd === null) {
                    $this->warn("Failed to fetch historical {$nativeSymbol}/USD price, falling back to current price...");
                    $priceUsd = $this->getCurrentNativePrice($nativeSymbol);
                    $result['note'] = 'Using current price as historical price unavailable';
                }

                if ($priceUsd === null) {
                    $this->error("Failed to fetch {$nativeSymbol}/USD price from all sources.");
                    return Command::FAILURE;
                }
            }

            $valueUsd = $valueNative * $priceUsd;

            $result['native_symbol'] = $nativeSymbol;
            $result['value_native'] = round($valueNative, 8);
            $result['native_usd_rate_at_time'] = $priceUsd;
            $result['value_usd_at_time'] = round($valueUsd, 2);

            // Add note about potential price variance
            if (!isset($result['price_source']) || $result['price_source'] !== 'manual') {
                $this->comment("Note: Price may vary slightly from PolygonScan due to different data sources/timing");
            }
        }

        $this->line(json_encode($result, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }

    private function getHistoricalNativePrice(string $symbol, int $timestamp, string $apiKey = '')
    {
        $date = date('d-m-Y', $timestamp);
        $prices = [];

        $this->info("Fetching historical {$symbol} price for {$date}...");

        // Resolve provider identifiers by symbol
        [$coingeckoId, $binancePair] = $this->resolveMarketIds($symbol);

        // Try CoinGecko historical price API first (most reliable for historical data)
        try {
            $response = Http::timeout(10)->get("https://api.coingecko.com/api/v3/coins/{$coingeckoId}/history", [
                'date' => $date,
                'localization' => false,
            ]);

            if ($response->successful()) {
                $price = $response->json('market_data.current_price.usd');
                if ($price && $price > 0) {
                    $prices['coingecko'] = $price;
                    $this->info("CoinGecko historical price ({$symbol}): \${$price}");
                }
            }
        } catch (\Exception $e) {
            $this->warn("CoinGecko historical API failed: " . $e->getMessage());
        }

        // Try CryptoCompare historical API (hourly data - more precise)
        try {
            $response = Http::timeout(10)->get('https://min-api.cryptocompare.com/data/pricehistorical', [
                'fsym' => $symbol,
                'tsyms' => 'USD',
                'ts' => $timestamp,
            ]);

            if ($response->successful()) {
                $price = $response->json($symbol . '.USD');
                if ($price && $price > 0) {
                    $prices['cryptocompare'] = $price;
                    $this->info("CryptoCompare historical price ({$symbol}): \${$price}");
                }
            }
        } catch (\Exception $e) {
            $this->warn("CryptoCompare historical API failed: " . $e->getMessage());
        }

        // Try Binance historical klines for more accurate timestamp-based pricing
        try {
            // Get the 1-minute candle closest to the transaction time
            $response = Http::timeout(10)->get('https://api.binance.com/api/v3/klines', [
                'symbol' => $binancePair,
                'interval' => '1m',
                'startTime' => ($timestamp - 60) * 1000, // 1 min before
                'endTime' => ($timestamp + 60) * 1000,   // 1 min after
                'limit' => 3,
            ]);

            if ($response->successful() && count($response->json()) > 0) {
                $candles = $response->json();
                // Use the close price of the closest candle
                $closePrice = (float) $candles[0][4];
                if ($closePrice > 0) {
                    $prices['binance'] = $closePrice;
                    $this->info("Binance historical price ({$symbol}) (exact time): \${$closePrice}");
                }
            }
        } catch (\Exception $e) {
            $this->warn("Binance historical API failed: " . $e->getMessage());
        }

        // If we have prices, use the most accurate one (Binance > CryptoCompare > CoinGecko)
        if (isset($prices['binance'])) {
            $this->info("Using Binance price (most accurate): \${$prices['binance']}");
            return $prices['binance'];
        }
        if (isset($prices['cryptocompare'])) {
            $this->info("Using CryptoCompare price: \${$prices['cryptocompare']}");
            return $prices['cryptocompare'];
        }
        if (isset($prices['coingecko'])) {
            $this->info("Using CoinGecko price: \${$prices['coingecko']}");
            return $prices['coingecko'];
        }

        // PolygonScan API only provides current price, not historical
        // So we'll use it as last resort (current price)
        try {
            $params = [
                'module' => 'stats',
                'action' => 'maticprice', // No POL endpoint available (V1 deprecated), keep for MATIC compat
            ];

            if ($apiKey) {
                $params['apikey'] = $apiKey;
            }

            $response = Http::timeout(10)->get('https://api.polygonscan.com/api', $params);

            if ($response->successful() && $response->json('status') === '1') {
                $result = $response->json('result');
                $price = (float) ($result['maticusd'] ?? 0);

                if ($price > 0) {
                    $this->warn("Using current MATIC price from PolygonScan (historical not available): \${$price}");
                    return $price;
                }
            }
        } catch (\Exception $e) {
            $this->warn("PolygonScan API failed: " . $e->getMessage());
        }

        return null;
    }

    private function getCurrentNativePrice(string $symbol)
    {
        [$coingeckoId, $binancePair] = $this->resolveMarketIds($symbol);
        // Try PolygonScan current price
        try {
            $response = Http::timeout(10)->get('https://api.polygonscan.com/api', [
                'module' => 'stats',
                'action' => 'maticprice',
            ]);

            if ($response->successful() && $response->json('status') === '1') {
                $result = $response->json('result');
                $price = (float) ($result['maticusd'] ?? 0);

                if ($price > 0) {
                    $this->info("Fetched current MATIC price from PolygonScan: \${$price}");
                    if ($symbol === 'MATIC') {
                        return $price;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->warn("PolygonScan failed: " . $e->getMessage());
        }

        // Try CoinGecko
        try {
            $response = Http::timeout(10)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => $coingeckoId,
                'vs_currencies' => 'usd',
            ]);

            if ($response->successful()) {
                $price = $response->json($coingeckoId . '.usd');
                if ($price && $price > 0) {
                    $this->info("Fetched {$symbol} price from CoinGecko: \${$price}");
                    return $price;
                }
            }
        } catch (\Exception $e) {
            $this->warn("CoinGecko failed: " . $e->getMessage());
        }

        // Try CryptoCompare
        try {
            $response = Http::timeout(10)->get('https://min-api.cryptocompare.com/data/price', [
                'fsym' => $symbol,
                'tsyms' => 'USD',
            ]);

            if ($response->successful()) {
                $price = $response->json('USD');
                if ($price && $price > 0) {
                    $this->info("Fetched {$symbol} price from CryptoCompare: \${$price}");
                    return $price;
                }
            }
        } catch (\Exception $e) {
            $this->warn("CryptoCompare failed: " . $e->getMessage());
        }

        // Try Binance API as fallback
        try {
            $response = Http::timeout(10)->get('https://api.binance.com/api/v3/ticker/price', [
                'symbol' => $binancePair,
            ]);

            if ($response->successful()) {
                $price = (float) $response->json('price');
                if ($price && $price > 0) {
                    $this->info("Fetched {$symbol} price from Binance: \${$price}");
                    return $price;
                }
            }
        } catch (\Exception $e) {
            $this->warn("Binance failed: " . $e->getMessage());
        }

        return null;
    }

    private function resolveMarketIds(string $symbol): array
    {
        $symbol = strtoupper($symbol);
        // Map to CoinGecko id and Binance pair
        if ($symbol === 'POL') {
            return ['polygon-ecosystem-token', 'POLUSDT'];
        }
        // default MATIC
        return ['matic-network', 'MATICUSDT'];
    }

    private function getTokenInfo($tokenAddress, $tokenValueRaw, $timestamp, $apiKey = '')
    {
        try {
            $date = date('d-m-Y', $timestamp);

            // Common stablecoins and tokens on Polygon
            $knownTokens = [
                '0x2791bca1f2de4661ed88a30c99a7a9449aa84174' => ['symbol' => 'USDC', 'decimals' => 6, 'coingecko_id' => 'usd-coin'],
                '0xc2132d05d31c914a87c6611c10748aeb04b58e8f' => ['symbol' => 'USDT', 'decimals' => 6, 'coingecko_id' => 'tether'],
                '0x8f3cf7ad23cd3cadbd9735aff958023239c6a063' => ['symbol' => 'DAI', 'decimals' => 18, 'coingecko_id' => 'dai'],
                '0x1bfd67037b42cf73acf2047067bd4f2c47d9bfd6' => ['symbol' => 'WBTC', 'decimals' => 8, 'coingecko_id' => 'wrapped-bitcoin'],
                '0x7ceb23fd6bc0add59e62ac25578270cff1b9f619' => ['symbol' => 'WETH', 'decimals' => 18, 'coingecko_id' => 'weth'],
            ];

            $tokenAddress = strtolower($tokenAddress);

            if (isset($knownTokens[$tokenAddress])) {
                $token = $knownTokens[$tokenAddress];
                $decimals = $token['decimals'];
                $amount = $tokenValueRaw / pow(10, $decimals);

                // Try to get historical USD price from CoinGecko
                $priceResponse = Http::timeout(10)->get("https://api.coingecko.com/api/v3/coins/{$token['coingecko_id']}/history", [
                    'date' => $date,
                    'localization' => false,
                ]);

                if ($priceResponse->successful()) {
                    $usdRate = $priceResponse->json('market_data.current_price.usd', 0);

                    if ($usdRate > 0) {
                        $this->info("Fetched historical {$token['symbol']} price: \${$usdRate}");

                        return [
                            'name' => $token['symbol'],
                            'symbol' => $token['symbol'],
                            'decimals' => $decimals,
                            'amount' => round($amount, 8),
                            'usd_rate' => $usdRate,
                            'value_usd' => round($amount * $usdRate, 6),
                        ];
                    }
                }

                // Fallback to current price
                $this->warn("Historical price not available, using current price...");
                $priceResponse = Http::get('https://api.coingecko.com/api/v3/simple/price', [
                    'ids' => $token['coingecko_id'],
                    'vs_currencies' => 'usd',
                ]);

                if ($priceResponse->successful()) {
                    $usdRate = $priceResponse->json($token['coingecko_id'] . '.usd', 0);

                    return [
                        'name' => $token['symbol'],
                        'symbol' => $token['symbol'],
                        'decimals' => $decimals,
                        'amount' => round($amount, 8),
                        'usd_rate' => $usdRate,
                        'value_usd' => round($amount * $usdRate, 6),
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
