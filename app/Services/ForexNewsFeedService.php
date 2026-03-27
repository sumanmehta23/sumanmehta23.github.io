<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ForexNewsFeedService
{
    public function fetchNormalizedFeed(bool $forceRefresh = false): array
    {
        $cacheKey = 'fxstreet:rss2json:normalized';
        $ttl = (int) config('services.fxstreet.cache_ttl', 900);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }

        // Do not keep stale empty cache values around.
        if (is_array($cached) && empty($cached)) {
            Cache::forget($cacheKey);
        }

        $records = $this->requestAndNormalize();

        // Cache only successful non-empty payloads so transient failures do not hide data.
        if (!empty($records)) {
            Cache::put($cacheKey, $records, now()->addSeconds(max($ttl, 60)));
        }

        return $records;
    }

    private function requestAndNormalize(): array
    {
        $rss2jsonUrl = (string) config('services.fxstreet.rss2json_url', 'https://api.rss2json.com/v1/api.json');
        $rssUrl = (string) config('services.fxstreet.rss_url', 'https://www.fxstreet.com/rss/news');
        $apiKey = (string) config('services.fxstreet.rss2json_api_key', '');

        $query = ['rss_url' => $rssUrl];

        if ($apiKey !== '') {
            $query['api_key'] = $apiKey;
            $query['count'] = 100;
        }

        try {
            $response = Http::timeout(20)
                ->retry(2, 500)
                ->acceptJson()
                ->get($rss2jsonUrl, $query);

            if (!$response->successful()) {
                // Some rss2json tiers reject `count` unless key is valid. Retry once without count.
                if ($response->status() === 422 && array_key_exists('count', $query)) {
                    $retryQuery = ['rss_url' => $rssUrl];
                    if ($apiKey !== '') {
                        $retryQuery['api_key'] = $apiKey;
                    }

                    $response = Http::timeout(20)
                        ->retry(1, 300)
                        ->acceptJson()
                        ->get($rss2jsonUrl, $retryQuery);
                }
            }

            if (!$response->successful()) {
                Log::warning('Forex news sync request failed', [
                    'url' => $rss2jsonUrl,
                    'rss_url' => $rssUrl,
                    'status' => $response->status(),
                    'body' => Str::limit($response->body(), 1000),
                ]);

                return $this->fallbackFromRawRss($rssUrl);
            }

            $payload = $response->json();
            if (!is_array($payload) || Arr::get($payload, 'status') !== 'ok') {
                Log::warning('Forex news payload invalid', [
                    'url' => $rss2jsonUrl,
                    'rss_url' => $rssUrl,
                    'status' => Arr::get($payload, 'status'),
                    'message' => Arr::get($payload, 'message'),
                ]);

                return $this->fallbackFromRawRss($rssUrl);
            }

            $items = Arr::get($payload, 'items', []);
            if (!is_array($items)) {
                return [];
            }

            $normalized = [];
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $normalizedItem = $this->normalizeItem($item);
                if ($normalizedItem !== null) {
                    $normalized[] = $normalizedItem;
                }
            }

            return $normalized;
        } catch (\Throwable $exception) {
            Log::error('Forex news sync exception', [
                'url' => $rss2jsonUrl,
                'rss_url' => $rssUrl,
                'message' => $exception->getMessage(),
            ]);

            return $this->fallbackFromRawRss($rssUrl);
        }
    }

    private function fallbackFromRawRss(string $rssUrl): array
    {
        try {
            $response = Http::timeout(20)
                ->retry(1, 300)
                ->get($rssUrl);

            if (!$response->successful()) {
                Log::warning('Forex news raw RSS fallback failed', [
                    'rss_url' => $rssUrl,
                    'status' => $response->status(),
                ]);
                return [];
            }

            $xml = @simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
            if (!$xml || !isset($xml->channel->item)) {
                Log::warning('Forex news raw RSS fallback invalid xml', [
                    'rss_url' => $rssUrl,
                ]);
                return [];
            }

            $normalized = [];
            foreach ($xml->channel->item as $item) {
                $raw = [
                    'title' => (string) ($item->title ?? ''),
                    'description' => (string) ($item->description ?? ''),
                    'link' => (string) ($item->link ?? ''),
                    'guid' => (string) ($item->guid ?? ''),
                    'pubDate' => (string) ($item->pubDate ?? ''),
                ];

                $normalizedItem = $this->normalizeItem($raw);
                if ($normalizedItem !== null) {
                    $normalized[] = $normalizedItem;
                }
            }

            Log::info('Forex news raw RSS fallback succeeded', [
                'rss_url' => $rssUrl,
                'count' => count($normalized),
            ]);

            return $normalized;
        } catch (\Throwable $exception) {
            Log::error('Forex news raw RSS fallback exception', [
                'rss_url' => $rssUrl,
                'message' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    private function normalizeItem(array $item): ?array
    {
        $title = trim((string) Arr::get($item, 'title', ''));
        $description = trim((string) Arr::get($item, 'description', ''));
        $link = trim((string) Arr::get($item, 'link', ''));
        $guid = trim((string) Arr::get($item, 'guid', ''));
        $pubDate = (string) Arr::get($item, 'pubDate', '');

        if ($title === '' || $link === '') {
            return null;
        }

        try {
            $publishedAt = $pubDate !== '' ? Carbon::parse($pubDate) : null;
        } catch (\Throwable $e) {
            $publishedAt = null;
        }

        $eventText = $title . ' ' . strip_tags($description);
        $forecast = $this->extractMetric($eventText, 'forecast');
        $previous = $this->extractMetric($eventText, 'previous');

        return [
            'guid_hash' => hash('sha256', $guid !== '' ? $guid : $link),
            'title' => $title,
            'description' => $description !== '' ? strip_tags($description) : null,
            'link' => $link,
            'published_at' => $publishedAt,
            'date_label' => $publishedAt ? $publishedAt->format('m-d-Y') : null,
            'time_label' => $publishedAt ? $publishedAt->format('H:i') : null,
            'currency' => $this->detectCurrency($eventText),
            'impact' => $this->detectImpact($eventText),
            'forecast' => $forecast,
            'previous' => $previous,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function detectCurrency(string $text): ?string
    {
        $currencies = [
            'USD', 'EUR', 'GBP', 'JPY', 'AUD', 'NZD', 'CAD', 'CHF',
            'CNY', 'HKD', 'SGD', 'SEK', 'NOK', 'TRY', 'ZAR',
        ];

        $upper = strtoupper($text);
        foreach ($currencies as $currency) {
            if (preg_match('/\b' . preg_quote($currency, '/') . '\b/', $upper) === 1) {
                return $currency;
            }
        }

        return null;
    }

    private function detectImpact(string $text): string
    {
        $lower = strtolower($text);

        if (Str::contains($lower, ['high impact', 'high'])) {
            return 'high';
        }

        if (Str::contains($lower, ['medium impact', 'moderate', 'medium'])) {
            return 'medium';
        }

        return 'low';
    }

    private function extractMetric(string $text, string $label): ?string
    {
        $pattern = '/\b' . preg_quote($label, '/') . '\b\s*[:\-]?\s*([^\n\r\|,;]{1,50})/i';
        if (preg_match($pattern, $text, $matches) !== 1) {
            return null;
        }

        $value = trim($matches[1]);
        return $value !== '' ? $value : null;
    }
}

