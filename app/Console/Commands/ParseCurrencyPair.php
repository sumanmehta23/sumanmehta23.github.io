<?php

namespace App\Console\Commands;

use App\Models\PriceSnapshot;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;

class ParseCurrencyPair extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:currency-pairs {file? : Path to the HTML file to parse}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse currency pairs from HTML file and store in price_snapshots table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the file path from the command argument or use the default
        $filePath = $this->argument('file') ?? storage_path('logs/currencypair.html');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("Parsing HTML file: {$filePath}");

        // Load HTML file
        $htmlContent = file_get_contents($filePath);

        // Create a new DOM Document
        $dom = new DOMDocument();

        // Suppress warnings for malformed HTML
        @$dom->loadHTML($htmlContent);

        // Create XPath object
        $xpath = new DOMXPath($dom);

        // Query to find all rows with data-rowkey attribute
        $rows = $xpath->query('//tr[@data-rowkey]');

        $totalRows = $rows->length;
        $processedRows = 0;
        $this->info("Found {$totalRows} rows to process");

        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        foreach ($rows as $row) {
            $dataRowKey = $row->getAttribute('data-rowkey');

            // Skip if data-rowkey doesn't match expected format
            if (!str_contains($dataRowKey, 'FX_IDC:')) {
                $bar->advance();
                continue;
            }

            // Extract symbol from data-rowkey (e.g., "FX_IDC:AEDAUD" -> "AEDAUD")
            $symbol = str_replace('FX_IDC:', '', $dataRowKey);

            // Skip if the symbol length is not 6 (not a standard currency pair)
            if (strlen($symbol) != 6) {
                $bar->advance();
                continue;
            }

            // Extract components
            $component1 = substr($symbol, 0, 3);
            $component2 = substr($symbol, 3, 3);

            // Extract prices from cells
            $cells = $xpath->query('.//td', $row);
            $price = 0.00000;
            $ask = 0.00000;
            $bid = 0.00000;

            if ($cells->length >= 8) {
                // Price is typically in the second cell (index 1)
                $priceText = trim($cells->item(1)->textContent);
                if (is_numeric($priceText)) {
                    $price = (float) $priceText;
                }

                // Bid appears to be in the 5th cell (index 4)
                $bidText = trim($cells->item(4)->textContent);
                if (is_numeric($bidText)) {
                    $bid = (float) $bidText;
                }

                // Ask appears to be in the 6th cell (index 5)
                $askText = trim($cells->item(5)->textContent);
                if (is_numeric($askText)) {
                    $ask = (float) $askText;
                }
            } else if ($cells->length >= 2) {
                // Fallback to using the price for all fields
                $priceText = trim($cells->item(1)->textContent);
                if (is_numeric($priceText)) {
                    $price = $ask = $bid = (float) $priceText;
                }
            }

            // Current timestamp
            $timestamp = time();

            // Create or update record in price_snapshots table
            PriceSnapshot::updateOrCreate(
                ['Symbol' => $symbol],
                [
                    'component1' => $component1,
                    'component2' => $component2,
                    'Timestamp' => $timestamp,
                    'Price' => $price,
                    'Ask' => $ask,
                    'Bid' => $bid,
                ]
            );

            $processedRows++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Show summary
        $this->info("Summary:");
        $this->info("- Total rows found: {$totalRows}");
        $this->info("- Processed currency pairs: {$processedRows}");
        $this->info("- Skipped rows: " . ($totalRows - $processedRows));

        // Show some sample data
        $this->newLine();
        $this->info("Sample data from price_snapshots table:");
        $samples = PriceSnapshot::take(5)->get();

        $headers = ['Symbol', 'Component1', 'Component2', 'Price', 'Ask', 'Bid', 'Timestamp'];
        $rows = [];

        foreach ($samples as $sample) {
            $rows[] = [
                $sample->Symbol,
                $sample->component1,
                $sample->component2,
                $sample->Price,
                $sample->Ask,
                $sample->Bid,
                date('Y-m-d H:i:s', $sample->Timestamp)
            ];
        }

        $this->table($headers, $rows);
        $this->info("Successfully saved currency pair data to the database.");

        return 0;
    }
}
