<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupExports extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'export:cleanup {--days=7 : Number of days to keep exports}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old export files to free up storage space';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Cleaning up export files older than {$days} days...");
        
        try {
            $files = Storage::disk('local')->files('exports');
            $deletedCount = 0;
            $totalSize = 0;
            
            foreach ($files as $file) {
                $lastModified = Carbon::createFromTimestamp(
                    Storage::disk('local')->lastModified($file)
                );
                
                if ($lastModified->lt($cutoffDate)) {
                    $fileSize = Storage::disk('local')->size($file);
                    $totalSize += $fileSize;
                    
                    Storage::disk('local')->delete($file);
                    $deletedCount++;
                    
                    $this->line("Deleted: {$file} (" . $this->formatBytes($fileSize) . ")");
                }
            }
            
            if ($deletedCount > 0) {
                $this->info("Successfully deleted {$deletedCount} export files, freed up " . $this->formatBytes($totalSize));
                
                Log::info('Export cleanup completed', [
                    'deleted_files' => $deletedCount,
                    'total_size_freed' => $totalSize,
                    'cutoff_date' => $cutoffDate->toDateTimeString()
                ]);
            } else {
                $this->info('No old export files found to delete.');
            }
            
        } catch (\Exception $e) {
            $this->error('Error during cleanup: ' . $e->getMessage());
            Log::error('Export cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}
