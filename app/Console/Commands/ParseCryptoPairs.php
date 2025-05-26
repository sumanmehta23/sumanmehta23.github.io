<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PriceSnapshot;
use Symfony\Component\DomCrawler\Crawler;

class ParseCryptoPairs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:parse-pairs {file? : Path to the HTML file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse cryptocurrency pairs from HTML file and store in price_snapshots table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filePath = $this->argument('file') ?? storage_path('logs/cryptopairs.html');

        if (!file_exists($filePath)) {
            $this->error("The file {$filePath} does not exist.");
            return 1;
        }

        $htmlContent = file_get_contents($filePath);

        // Extract cryptocurrency symbols and prices from the HTML
        $cryptoPairs = $this->extractCryptoPairsFromHTML($htmlContent);

        if (empty($cryptoPairs)) {
            $this->error("No cryptocurrency pairs found in the file.");
            return 1;
        }

        $timestamp = time(); // Current timestamp

        // Store data in the database
        foreach ($cryptoPairs as &$pair) {
            // Add additional fields for database storage
            $pair['Timestamp'] = $timestamp;
            $pair['RateToUSD'] = 1;
            $pair['digits'] = 5;
            $pair['mul_factor'] = 1;
            $pair['contractsize'] = 100000;
            $pair['minlots'] = 0.01;
            $pair['maxlots'] = 50;
            $pair['mmr'] = 2;
            $pair['leverage'] = 100;

            // Store in database
            PriceSnapshot::updateOrCreate(
                ['Symbol' => $pair['Symbol']],
                $pair
            );
        }

        // Output the results in a table
        $headers = ['Symbol', 'Component1', 'Component2', 'Price'];
        $tableData = array_map(function ($item) {
            return [
                'Symbol' => $item['Symbol'],
                'Component1' => $item['component1'],
                'Component2' => $item['component2'],
                'Price' => $item['Price']
            ];
        }, $cryptoPairs);
        $this->table($headers, $tableData);

        // Save to JSON file if needed
        $jsonFilePath = storage_path('app/cryptopairs.json');
        file_put_contents($jsonFilePath, json_encode($cryptoPairs, JSON_PRETTY_PRINT));
        $this->info("Cryptocurrency pairs saved to {$jsonFilePath}");

        $this->info(count($cryptoPairs) . " cryptocurrency pairs stored in the price_snapshots table.");

        return 0;
    }

    /**
     * Extract cryptocurrency pairs and price information from HTML content
     *
     * @param string $htmlContent
     * @return array
     */
    protected function extractCryptoPairsFromHTML($htmlContent)
    {
        $cryptoPairs = [];
        $crawler = new Crawler($htmlContent);

        // Find all rows in the table
        $rows = $crawler->filter('tr.row');

        $this->info("Found {$rows->count()} rows in the HTML table.");

        $rows->each(function ($row) use (&$cryptoPairs) {
            // Extract symbol
            $symbolElement = $row->filter('span.symbol');
            if ($symbolElement->count() == 0) {
                return; // Skip this row
            }

            $symbol = trim($symbolElement->text());

            // Extract price
            $priceElement = $row->filter('fin-streamer[data-field="regularMarketPrice"]');
            $price = 0;
            $bid = 0;
            $ask = 0;

            if ($priceElement->count() > 0) {
                $price = (float) $priceElement->attr('data-value');
                // For simplicity, setting bid and ask as slightly below and above the price
                // In a real scenario, you would extract these values from the HTML if available
                $bid = $price * 0.999; // 0.1% below price
                $ask = $price * 1.001; // 0.1% above price
            }

            // Split the symbol into component1 and component2
            if (preg_match('/^([^-]+)-(.+)$/', $symbol, $components)) {
                $cryptoPairs[] = [
                    'Symbol' => $symbol,
                    'component1' => $components[1],
                    'component2' => $components[2],
                    'Price' => $price,
                    'Bid' => $bid,
                    'Ask' => $ask
                ];
            }
        });

        return $cryptoPairs;
    }
}
