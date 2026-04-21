<?php

namespace App\Console\Commands;

use App\Models\ForexNewsItem;
use App\Services\ForexNewsFeedService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncForexNewsFeedCommand extends Command
{
    protected $signature = 'app:sync-forex-news {--force : Force refresh and bypass cached feed response}';

    protected $description = 'Sync FXStreet forex news feed into local database';

    public function __construct(private ForexNewsFeedService $forexNewsFeedService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $this->info('Starting forex news feed sync...');

        try {
            $records = $this->forexNewsFeedService->fetchNormalizedFeed($force);

            if (empty($records)) {
                $this->warn('No items fetched from remote feed.');
                Log::warning('Forex news sync returned no records');
                return self::SUCCESS;
            }

            ForexNewsItem::upsert(
                $records,
                ['guid_hash'],
                [
                    'title',
                    'description',
                    'link',
                    'published_at',
                    'date_label',
                    'time_label',
                    'currency',
                    'impact',
                    'forecast',
                    'previous',
                    'updated_at',
                ]
            );

            $this->info('Forex news sync completed. Items processed: ' . count($records));
            Log::info('Forex news sync completed', [
                'count' => count($records),
                'force' => $force,
            ]);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('Forex news sync failed: ' . $exception->getMessage());
            Log::error('Forex news sync failed', [
                'message' => $exception->getMessage(),
            ]);

            return self::FAILURE;
        }
    }
}

