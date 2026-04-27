<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetPolygonTxDetails extends Command
{
    /**
     * Usage:
     *   php artisan polygon:usd <hash>
     *   php artisan polygon:usd <hash> --chain=ethereum
     *   php artisan polygon:usd <hash> --chain=monad
     *   php artisan polygon:usd <hash> --chain=polygon --api-key=YOUR_KEY
     *   php artisan polygon:usd <hash> --price=3200
     *
     * --chain=AUTO   Try Polygon first, then Ethereum, then Monad (default)
     * --chain=polygon
     * --chain=ethereum
     * --chain=monad
     */
    protected $signature = 'polygon:usd
                            {hash              : Transaction hash (Polygon, Ethereum, or Monad)}
                            {--chain=AUTO      : Chain to query: AUTO|polygon|ethereum|monad}
                            {--api-key=        : PolygonScan / Etherscan API key (optional)}
                            {--price=          : Manually specify native token USD price (optional)}
                            {--native=AUTO     : Force native symbol override: AUTO|POL|MATIC|MON}';

    protected $description = 'Get transaction value in USD for a Polygon, Ethereum, or Monad transaction hash';

    // ─── Chain config ──────────────────────────────────────────────────────────

    private array $chains = [
        'polygon' => [
            'rpc'          => 'https://polygon-bor-rpc.publicnode.com',
            'label'        => 'Polygon',
            'native'       => 'POL',   // resolved dynamically for polygon
            'explorer_api' => 'https://api.polygonscan.com/api',
            'explorer_tx'  => 'https://polygonscan.com/tx/%s',
        ],
        'ethereum' => [
            'rpc'          => 'https://ethereum-rpc.publicnode.com',
            'label'        => 'Ethereum',
            'native'       => 'ETH',
            'explorer_api' => 'https://api.etherscan.io/api',
            'explorer_tx'  => 'https://etherscan.io/tx/%s',
        ],
        'monad' => [
            'rpc'          => 'https://rpc.monad.xyz',
            'label'        => 'Monad',
            'native'       => 'MON',
            'explorer_api' => 'https://monadscan.com',
            'explorer_tx'  => 'https://monadscan.com/tx/%s',
        ],
    ];

    // ─── Known ERC-20 tokens per chain ────────────────────────────────────────

    private array $knownTokens = [
        // ── Polygon ──────────────────────────────────────────────────────────
        'polygon' => [
            '0x2791bca1f2de4661ed88a30c99a7a9449aa84174' => ['symbol' => 'USDC',  'decimals' => 6,  'coingecko_id' => 'usd-coin'],
            '0x3c499c542cef5e3811e1192ce70d8cc03d5c3359' => ['symbol' => 'USDC',  'decimals' => 6,  'coingecko_id' => 'usd-coin'],
            '0xc2132d05d31c914a87c6611c10748aeb04b58e8f' => ['symbol' => 'USDT',  'decimals' => 6,  'coingecko_id' => 'tether'],
            '0x8f3cf7ad23cd3cadbd9735aff958023239c6a063' => ['symbol' => 'DAI',   'decimals' => 18, 'coingecko_id' => 'dai'],
            '0x1bfd67037b42cf73acf2047067bd4f2c47d9bfd6' => ['symbol' => 'WBTC',  'decimals' => 8,  'coingecko_id' => 'wrapped-bitcoin'],
            '0x7ceb23fd6bc0add59e62ac25578270cff1b9f619' => ['symbol' => 'WETH',  'decimals' => 18, 'coingecko_id' => 'weth'],
            '0xdab529f40e671a1d4bf91361c21bf9f0c9712ab7' => ['symbol' => 'PYUSD', 'decimals' => 6,  'coingecko_id' => 'paypal-usd'],
        ],
        // ── Ethereum ─────────────────────────────────────────────────────────
        'ethereum' => [
            '0xa0b86991c6218b36c1d19d4a2e9eb0ce3606eb48' => ['symbol' => 'USDC',  'decimals' => 6,  'coingecko_id' => 'usd-coin'],
            '0xdac17f958d2ee523a2206206994597c13d831ec7' => ['symbol' => 'USDT',  'decimals' => 6,  'coingecko_id' => 'tether'],
            '0x6b175474e89094c44da98b954eedeac495271d0f' => ['symbol' => 'DAI',   'decimals' => 18, 'coingecko_id' => 'dai'],
            '0x2260fac5e5542a773aa44fbcfedf7c193bc2c599' => ['symbol' => 'WBTC',  'decimals' => 8,  'coingecko_id' => 'wrapped-bitcoin'],
            '0xc02aaa39b223fe8d0a0e5c4f27ead9083c756cc2' => ['symbol' => 'WETH',  'decimals' => 18, 'coingecko_id' => 'weth'],
            '0x6c3ea9036406852006290770bedfcaba0e23a0e8' => ['symbol' => 'PYUSD', 'decimals' => 6,  'coingecko_id' => 'paypal-usd'],
            '0x514910771af9ca656af840dff83e8264ecf986ca' => ['symbol' => 'LINK',  'decimals' => 18, 'coingecko_id' => 'chainlink'],
            '0x1f9840a85d5af5bf1d1762f925bdaddc4201f984' => ['symbol' => 'UNI',   'decimals' => 18, 'coingecko_id' => 'uniswap'],
            '0x7fc66500c84a76ad7e9c93437bfc5ac33e2ddae9' => ['symbol' => 'AAVE',  'decimals' => 18, 'coingecko_id' => 'aave'],
            '0x95ad61b0a150d79219dcf64e1e6cc01f0b64c4ce' => ['symbol' => 'SHIB',  'decimals' => 18, 'coingecko_id' => 'shiba-inu'],
        ],
        // ── Monad ────────────────────────────────────────────────────────────
        'monad' => [],
    ];

    // ─── handle() ─────────────────────────────────────────────────────────────

    public function handle()
    {
        $hash   = preg_replace('/[^a-fA-F0-9x]/', '', trim((string) $this->argument('hash')));
        $apiKey = $this->option('api-key') ?? env('POLYGONSCAN_API_KEY', '');
        $chainOpt = strtolower(trim($this->option('chain') ?? 'AUTO'));

        $this->info("Fetching transaction: {$hash}");

        // 1️⃣ Detect / confirm chain
        [$chain, $tx] = $this->resolveChain($hash, $chainOpt);

        if (!$chain || !$tx) {
            $this->error('Transaction not found on Polygon, Ethereum, or Monad.');
            $this->warn('Possible causes: wrong hash, pending/dropped tx, or wrong chain option.');
            return Command::FAILURE;
        }

        $rpc   = $this->chains[$chain]['rpc'];
        $label = $this->chains[$chain]['label'];
        $this->info("✓ Found on {$label}");
        $explorerDisplayedUsd = $this->getExplorerDisplayedUsdValue($chain, $hash);

        // 2️⃣ Block timestamp
        $blockNumber = hexdec($tx['blockNumber'] ?? '0x0');
        $blockResp   = Http::post($rpc, [
            'jsonrpc' => '2.0', 'method' => 'eth_getBlockByNumber',
            'params'  => ['0x' . dechex($blockNumber), false], 'id' => 3,
        ]);
        $block           = $blockResp->json('result');
        $timestamp       = hexdec($block['timestamp'] ?? '0x0');
        $transactionDate = date('Y-m-d H:i:s', $timestamp);
        $this->info("Transaction date: {$transactionDate} UTC");

        // 3️⃣ Receipt (for ERC-20 logs)
        $receiptResp = Http::post($rpc, [
            'jsonrpc' => '2.0', 'method' => 'eth_getTransactionReceipt',
            'params'  => [$hash], 'id' => 2,
        ]);
        $receipt = $receiptResp->json('result');

        // 4️⃣ Native value
        $valueWei    = hexdec($tx['value'] ?? '0x0');
        $valueNative = $valueWei / 1e18;

        $result = [
            'chain'       => $label,
            'hash'        => $hash,
            'from'        => $tx['from'] ?? null,
            'to'          => $tx['to']   ?? null,
            'blockNumber' => $blockNumber,
            'timestamp'   => $timestamp,
            'date'        => $transactionDate,
        ];

        if ($explorerDisplayedUsd !== null) {
            $result['explorer_displayed_value_usd'] = $explorerDisplayedUsd;
        }

        // 5️⃣ ERC-20 path (native == 0)
        if ($valueNative == 0 && $receipt && isset($receipt['logs'])) {
            $this->info('Native value is 0, checking for ERC-20 token transfers...');

            $transferSig = '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';

            foreach ($receipt['logs'] as $log) {
                if (($log['topics'][0] ?? '') !== $transferSig) {
                    continue;
                }

                $tokenAddress = $log['address'];
                $tokenValue   = hexdec($log['data'] ?? '0x0');
                $this->info("Found token transfer. Contract: {$tokenAddress}");

                $tokenInfo = $this->getTokenInfo($tokenAddress, $tokenValue, $timestamp, $chain, $rpc, $explorerDisplayedUsd);

                if ($tokenInfo) {
                    $result['token_address']        = $tokenAddress;
                    $result['token_name']            = $tokenInfo['name'];
                    $result['token_symbol']          = $tokenInfo['symbol'];
                    $result['token_decimals']        = $tokenInfo['decimals'];
                    $result['token_amount']          = $tokenInfo['amount'];
                    $result['token_usd_rate_at_time'] = $tokenInfo['usd_rate'];
                    $result['value_usd_at_time']     = $tokenInfo['value_usd'];
                    if (!empty($tokenInfo['price_source'])) {
                        $result['price_source'] = $tokenInfo['price_source'];
                    }

                    $this->line(json_encode($result, JSON_PRETTY_PRINT));
                    return Command::SUCCESS;
                }

                // Partial fallback
                $result['token_address']   = $tokenAddress;
                $result['token_value_raw'] = $tokenValue;
                $result['note']            = 'Token detected but unable to fetch price data';
                break;
            }
        }

        // 6️⃣ Native value path
        if ($valueNative > 0) {
            $nativeSymbol = $this->resolveNativeSymbol($chain, $timestamp);
            $manualPrice  = $this->option('price');

            if ($manualPrice && is_numeric($manualPrice) && $manualPrice > 0) {
                $priceUsd = (float) $manualPrice;
                $this->info("Using manually specified {$nativeSymbol} price: \${$priceUsd}");
                $result['price_source'] = 'manual';
            } elseif ($explorerDisplayedUsd !== null) {
                $priceUsd = round($explorerDisplayedUsd / $valueNative, 8);
                $this->info("Using {$label} explorer displayed value: \${$explorerDisplayedUsd}");
                $result['price_source'] = 'explorer_displayed_current';
            } else {
                $priceUsd = $this->getHistoricalNativePrice($nativeSymbol, $timestamp, $apiKey);

                if ($priceUsd === null) {
                    $this->warn("Historical price unavailable, falling back to current price...");
                    $priceUsd = $this->getCurrentNativePrice($nativeSymbol);
                    $result['note'] = 'Using current price — historical unavailable';
                }

                if ($priceUsd === null) {
                    $this->error("Failed to fetch {$nativeSymbol}/USD price from all sources.");
                    return Command::FAILURE;
                }
            }

            $result['native_symbol']         = $nativeSymbol;
            $result['value_native']           = round($valueNative, 8);
            $result['native_usd_rate_at_time'] = $priceUsd;
            $result['value_usd_at_time']      = isset($result['price_source']) && $result['price_source'] === 'explorer_displayed_current'
                ? $explorerDisplayedUsd
                : round($valueNative * $priceUsd, 2);

            if (!isset($result['price_source'])) {
                $this->comment('Note: Price may vary slightly due to different data sources/timing.');
            }
        }

        $this->line(json_encode($result, JSON_PRETTY_PRINT));
        return Command::SUCCESS;
    }

    // ─── Chain detection ───────────────────────────────────────────────────────

    /**
     * Try to fetch the transaction from the specified chain (or both if AUTO).
     * Returns [chainKey, txArray] or [null, null].
     */
    private function resolveChain(string $hash, string $chainOpt): array
    {
        $order = match ($chainOpt) {
            'polygon'  => ['polygon'],
            'ethereum' => ['ethereum'],
            'monad'    => ['monad'],
            default    => ['polygon', 'ethereum', 'monad'],   // AUTO: try Polygon first
        };

        foreach ($order as $chain) {
            $rpc  = $this->chains[$chain]['rpc'];
            $name = $this->chains[$chain]['label'];
            $this->info("Trying {$name} RPC...");

            try {
                $resp = Http::timeout(10)->post($rpc, [
                    'jsonrpc' => '2.0',
                    'method'  => 'eth_getTransactionByHash',
                    'params'  => [$hash],
                    'id'      => 1,
                ]);

                $tx = $resp->json('result');

                if ($tx && isset($tx['hash'])) {
                    return [$chain, $tx];
                }
            } catch (\Exception $e) {
                $this->warn("{$name} RPC error: " . $e->getMessage());
            }
        }

        return [null, null];
    }

    // ─── Native symbol resolution ──────────────────────────────────────────────

    private function resolveNativeSymbol(string $chain, int $timestamp): string
    {
        if ($chain === 'ethereum') {
            return 'ETH';
        }

        if ($chain === 'monad') {
            return 'MON';
        }

        // Polygon: MATIC before Oct 2024 cutover, POL after
        $nativeOpt  = strtoupper((string) ($this->option('native') ?? 'AUTO'));
        $cutoverTs  = strtotime('2024-10-26 00:00:00 UTC');

        if ($nativeOpt === 'MATIC' || $nativeOpt === 'POL') {
            return $nativeOpt;
        }

        return ($timestamp >= $cutoverTs) ? 'POL' : 'MATIC';
    }

    // ─── Historical price ──────────────────────────────────────────────────────

    private function getHistoricalNativePrice(string $symbol, int $timestamp, string $apiKey = ''): ?float
    {
        $date            = date('d-m-Y', $timestamp);
        $prices          = [];
        $normalizedSymbol = $this->normalizeMarketSymbol($symbol);

        $this->info("Fetching historical {$symbol} price for {$date}...");

        [$coingeckoId, $binancePair] = $this->resolveMarketIds($symbol);

        // CoinGecko (daily)
        try {
            $resp = Http::timeout(10)->get("https://api.coingecko.com/api/v3/coins/{$coingeckoId}/history", [
                'date' => $date, 'localization' => false,
            ]);
            $price = $resp->json('market_data.current_price.usd');
            if ($price > 0) {
                $prices['coingecko'] = $price;
                $this->info("CoinGecko historical ({$symbol}): \${$price}");
            }
        } catch (\Exception $e) {
            $this->warn("CoinGecko historical failed: " . $e->getMessage());
        }

        // CryptoCompare is symbol-based, so skip Monad aliases and rely on exact CoinGecko IDs.
        if (!in_array($normalizedSymbol, ['MON', 'WMON'], true)) {
            try {
                $resp  = Http::timeout(10)->get('https://min-api.cryptocompare.com/data/pricehistorical', [
                    'fsym' => $normalizedSymbol, 'tsyms' => 'USD', 'ts' => $timestamp,
                ]);
                $price = $resp->json($normalizedSymbol . '.USD');
                if ($price > 0) {
                    $prices['cryptocompare'] = $price;
                    $this->info("CryptoCompare historical ({$symbol}): \${$price}");
                }
            } catch (\Exception $e) {
                $this->warn("CryptoCompare historical failed: " . $e->getMessage());
            }
        }

        // Binance (1-minute candle — most precise)
        if ($binancePair) {
            try {
                $resp = Http::timeout(10)->get('https://api.binance.com/api/v3/klines', [
                    'symbol'    => $binancePair,
                    'interval'  => '1m',
                    'startTime' => ($timestamp - 60) * 1000,
                    'endTime'   => ($timestamp + 60) * 1000,
                    'limit'     => 3,
                ]);
                $candles = $resp->json();
                if ($resp->successful() && count($candles) > 0) {
                    $close = (float) $candles[0][4];
                    if ($close > 0) {
                        $prices['binance'] = $close;
                        $this->info("Binance historical ({$symbol}): \${$close}");
                    }
                }
            } catch (\Exception $e) {
                $this->warn("Binance historical failed: " . $e->getMessage());
            }
        }

        // Priority: Binance > CryptoCompare > CoinGecko
        foreach (['binance', 'cryptocompare', 'coingecko'] as $src) {
            if (isset($prices[$src])) {
                $this->info("Using {$src} price: \${$prices[$src]}");
                return (float) $prices[$src];
            }
        }

        return null;
    }

    // ─── Current price fallback ────────────────────────────────────────────────

    private function getCurrentNativePrice(string $symbol): ?float
    {
        [$coingeckoId, $binancePair] = $this->resolveMarketIds($symbol);
        $normalizedSymbol = $this->normalizeMarketSymbol($symbol);

        // CoinGecko
        try {
            $resp  = Http::timeout(10)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => $coingeckoId, 'vs_currencies' => 'usd',
            ]);
            $price = $resp->json($coingeckoId . '.usd');
            if ($price > 0) {
                $this->info("Current {$symbol} price (CoinGecko): \${$price}");
                return (float) $price;
            }
        } catch (\Exception $e) {
            $this->warn("CoinGecko current price failed: " . $e->getMessage());
        }

        // CryptoCompare is symbol-based, so skip Monad aliases and rely on exact CoinGecko IDs.
        if (!in_array($normalizedSymbol, ['MON', 'WMON'], true)) {
            try {
                $resp  = Http::timeout(10)->get('https://min-api.cryptocompare.com/data/price', [
                    'fsym' => $normalizedSymbol, 'tsyms' => 'USD',
                ]);
                $price = $resp->json('USD');
                if ($price > 0) {
                    $this->info("Current {$symbol} price (CryptoCompare): \${$price}");
                    return (float) $price;
                }
            } catch (\Exception $e) {
                $this->warn("CryptoCompare current price failed: " . $e->getMessage());
            }
        }

        // Binance
        if ($binancePair) {
            try {
                $resp  = Http::timeout(10)->get('https://api.binance.com/api/v3/ticker/price', ['symbol' => $binancePair]);
                $price = (float) $resp->json('price');
                if ($price > 0) {
                    $this->info("Current {$symbol} price (Binance): \${$price}");
                    return $price;
                }
            } catch (\Exception $e) {
                $this->warn("Binance current price failed: " . $e->getMessage());
            }
        }

        return null;
    }

    // ─── Market ID resolution ──────────────────────────────────────────────────

    private function resolveMarketIds(string $symbol): array
    {
        return match ($this->normalizeMarketSymbol($symbol)) {
            'ETH'   => ['ethereum',               'ETHUSDT'],
            'POL'   => ['polygon-ecosystem-token', 'POLUSDT'],
            'MATIC' => ['matic-network',           'MATICUSDT'],
            'MON'   => ['monad',                  null],
            'WMON'  => ['wrapped-mon',            null],
            'WETH'  => ['weth',                    'ETHUSDT'],
            'WBTC'  => ['wrapped-bitcoin',         'BTCUSDT'],
            default => ['matic-network',           'MATICUSDT'],
        };
    }

    // ─── Token info (ERC-20) ───────────────────────────────────────────────────

    private function getTokenInfo(
        string $tokenAddress,
        int    $tokenValueRaw,
        int    $timestamp,
        string $chain,
        string $rpc,
        ?float $explorerDisplayedUsd = null
    ): ?array {
        try {
            $date             = date('d-m-Y', $timestamp);
            $tokenAddressNorm = strtolower($tokenAddress);
            $knownList        = $this->knownTokens[$chain] ?? [];

            // ── Known token ───────────────────────────────────────────────────
            if (isset($knownList[$tokenAddressNorm])) {
                $token    = $knownList[$tokenAddressNorm];
                $decimals = $token['decimals'];
                $amount   = $tokenValueRaw / pow(10, $decimals);
                if ($explorerDisplayedUsd !== null && $amount > 0) {
                    return [
                        'name'      => $token['symbol'],
                        'symbol'    => $token['symbol'],
                        'decimals'  => $decimals,
                        'amount'    => round($amount, 8),
                        'usd_rate'  => round($explorerDisplayedUsd / $amount, 8),
                        'value_usd' => $explorerDisplayedUsd,
                        'price_source' => 'explorer_displayed_current',
                    ];
                }

                $price    = $this->fetchTokenUsdPrice($token['coingecko_id'], $token['symbol'], $date);

                return [
                    'name'      => $token['symbol'],
                    'symbol'    => $token['symbol'],
                    'decimals'  => $decimals,
                    'amount'    => round($amount, 8),
                    'usd_rate'  => $price['rate'] ?? null,
                    'value_usd' => $price ? round($amount * $price['rate'], 6) : null,
                    'price_source' => $price ? 'market_data' : null,
                ];
            }

            // ── Unknown token — resolve from chain ────────────────────────────
            $this->info('Token not in known list — fetching metadata from chain...');
            $meta     = $this->getTokenMetadataFromChain($tokenAddress, $rpc);
            $symbol   = $meta['symbol'];
            $decimals = $meta['decimals'];
            $amount   = $tokenValueRaw / pow(10, $decimals);

            $this->info('Chain metadata — symbol: ' . ($symbol ?? 'unknown') . ", decimals: {$decimals}");

            if ($explorerDisplayedUsd !== null && $amount > 0) {
                return [
                    'name'      => $symbol   ?? 'Unknown',
                    'symbol'    => $symbol   ?? 'Unknown',
                    'decimals'  => $decimals,
                    'amount'    => round($amount, 8),
                    'usd_rate'  => round($explorerDisplayedUsd / $amount, 8),
                    'value_usd' => $explorerDisplayedUsd,
                    'price_source' => 'explorer_displayed_current',
                ];
            }

            $coingeckoId = $symbol ? $this->resolveCoingeckoId($symbol) : null;
            $price       = $coingeckoId ? $this->fetchTokenUsdPrice($coingeckoId, $symbol, $date) : null;

            return [
                'name'      => $symbol   ?? 'Unknown',
                'symbol'    => $symbol   ?? 'Unknown',
                'decimals'  => $decimals,
                'amount'    => round($amount, 8),
                'usd_rate'  => $price['rate'] ?? null,
                'value_usd' => $price ? round($amount * $price['rate'], 6) : null,
                'price_source' => $price ? 'market_data' : null,
            ];

        } catch (\Exception $e) {
            $this->warn('getTokenInfo exception: ' . $e->getMessage());
            return null;
        }
    }

    // ─── On-chain token metadata (symbol + decimals via eth_call) ─────────────

    private function getTokenMetadataFromChain(string $tokenAddress, string $rpc): array
    {
        $symbol   = null;
        $decimals = 18;

        // symbol()
        try {
            $resp = Http::timeout(10)->post($rpc, [
                'jsonrpc' => '2.0', 'method' => 'eth_call',
                'params'  => [['to' => $tokenAddress, 'data' => '0x95d89b41'], 'latest'],
                'id'      => 10,
            ]);
            $hex = $resp->json('result');
            if ($hex && $hex !== '0x') {
                $raw    = hex2bin(substr(ltrim($hex, '0x'), 128));
                $symbol = trim(preg_replace('/[^\x20-\x7E]/', '', $raw)) ?: null;
            }
        } catch (\Exception $e) {
            $this->warn('RPC symbol() failed: ' . $e->getMessage());
        }

        // decimals()
        try {
            $resp = Http::timeout(10)->post($rpc, [
                'jsonrpc' => '2.0', 'method' => 'eth_call',
                'params'  => [['to' => $tokenAddress, 'data' => '0x313ce567'], 'latest'],
                'id'      => 11,
            ]);
            $hex = $resp->json('result');
            if ($hex && $hex !== '0x') {
                $decimals = hexdec(ltrim($hex, '0x'));
            }
        } catch (\Exception $e) {
            $this->warn('RPC decimals() failed: ' . $e->getMessage());
        }

        return ['symbol' => $symbol, 'decimals' => (int) $decimals];
    }

    // ─── CoinGecko ID search ───────────────────────────────────────────────────

    private function resolveCoingeckoId(string $symbol): ?string
    {
        $normalizedSymbol = $this->normalizeMarketSymbol($symbol);
        $directMap = [
            'ETH'   => 'ethereum',
            'POL'   => 'polygon-ecosystem-token',
            'MATIC' => 'matic-network',
            'MON'   => 'monad',
            'WMON'  => 'wrapped-mon',
            'WETH'  => 'weth',
            'WBTC'  => 'wrapped-bitcoin',
        ];

        if (isset($directMap[$normalizedSymbol])) {
            $this->info("Resolved CoinGecko ID for {$symbol} via alias: {$directMap[$normalizedSymbol]}");
            return $directMap[$normalizedSymbol];
        }

        try {
            $resp  = Http::timeout(10)->get('https://api.coingecko.com/api/v3/search', ['query' => $normalizedSymbol]);
            $coins = $resp->json('coins') ?? [];
            foreach ($coins as $coin) {
                if (strtoupper($coin['symbol'] ?? '') === $normalizedSymbol) {
                    $this->info("Resolved CoinGecko ID for {$symbol}: {$coin['id']}");
                    return $coin['id'];
                }
            }
        } catch (\Exception $e) {
            $this->warn('CoinGecko search failed: ' . $e->getMessage());
        }
        return null;
    }

    private function getExplorerDisplayedUsdValue(string $chain, string $hash): ?float
    {
        $explorerTx = $this->chains[$chain]['explorer_tx'] ?? null;

        if (!$explorerTx) {
            return null;
        }

        try {
            $resp = Http::timeout(15)
                ->retry(2, 300)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; LQHManualPaymentBot/1.0; +https://lqdfx.com)',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->get(sprintf($explorerTx, $hash));

            if (!$resp->successful()) {
                return null;
            }

            $html = $resp->body();

            $patterns = [
                '/id=[\'"]data-vprice[\'"][^>]*>\s*\$([\d,]+(?:\.\d+)?)/i',
                '/id=[\'"]ContentPlaceHolder1_spanClosingPrice[\'"][^>]*>.*?\$([\d,]+(?:\.\d+)?)/is',
                '/<span class=[\'"][^\'"]*text-muted[^\'"]*[\'"][^>]*>\(\$([\d,]+(?:\.\d+)?)\)<\/span>/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    $value = (float) str_replace(',', '', $matches[1]);
                    if ($value > 0) {
                        $this->info("Explorer displayed USD value ({$chain}): \${$value}");
                        return $value;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->warn("Explorer USD value fetch failed: " . $e->getMessage());
        }

        return null;
    }

    // ─── CoinGecko price fetch (historical → current fallback) ────────────────

    private function fetchTokenUsdPrice(string $coingeckoId, string $symbol, string $date): ?array
    {
        // Historical
        try {
            $resp = Http::timeout(10)->get("https://api.coingecko.com/api/v3/coins/{$coingeckoId}/history", [
                'date' => $date, 'localization' => false,
            ]);
            $rate = $resp->json('market_data.current_price.usd', 0);
            if ($rate > 0) {
                $this->info("Historical {$symbol} price: \${$rate}");
                return ['rate' => $rate];
            }
        } catch (\Exception $e) {
            $this->warn("CoinGecko historical failed for {$symbol}: " . $e->getMessage());
        }

        // Current fallback
        $this->warn("Historical price unavailable for {$symbol}, using current price...");
        try {
            $resp = Http::timeout(10)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => $coingeckoId, 'vs_currencies' => 'usd',
            ]);
            $rate = $resp->json($coingeckoId . '.usd', 0);
            if ($rate > 0) {
                $this->info("Current {$symbol} price: \${$rate}");
                return ['rate' => $rate];
            }
        } catch (\Exception $e) {
            $this->warn("CoinGecko current price failed for {$symbol}: " . $e->getMessage());
        }

        return null;
    }

    private function normalizeMarketSymbol(string $symbol): string
    {
        $normalized = strtoupper(trim($symbol));
        $normalized = str_replace(['-', ' ', '.'], '_', $normalized);

        return match ($normalized) {
            'ETHEREUM_ETH' => 'ETH',
            'POLYGON_POL'  => 'POL',
            'POLYGON_MATIC' => 'MATIC',
            'MONAD_MON'    => 'MON',
            'MONAD_WMON', 'WRAPPED_MON' => 'WMON',
            default        => $normalized,
        };
    }
}
