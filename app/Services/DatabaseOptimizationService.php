<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Database Performance Optimization Service
 */
class DatabaseOptimizationService
{
    /**
     * Optimize trades table for better performance
     */
    public static function optimizeTradesTable(): void
    {
        $startTime = microtime(true);

        // 1. Analyze table statistics
        DB::statement('ANALYZE TABLE trades');

        // 2. Optimize table structure
        DB::statement('OPTIMIZE TABLE trades');

        // 3. Update table statistics for query optimizer
        DB::statement('FLUSH TABLE trades');

        $optimizationTime = round((microtime(true) - $startTime) * 1000, 2);
        Log::info("Database optimization completed in {$optimizationTime}ms");
    }

    /**
     * Check for unused indexes and suggest removal
     */
    public static function analyzeIndexUsage(): array
    {
        $query = "
            SELECT 
                s.table_name,
                s.index_name,
                s.column_name,
                s.cardinality,
                IFNULL(t.rows_read, 0) as rows_read,
                IFNULL(t.rows_requested, 0) as rows_requested
            FROM information_schema.statistics s
            LEFT JOIN performance_schema.table_io_waits_summary_by_index_usage t 
                ON s.table_schema = t.object_schema 
                AND s.table_name = t.object_name 
                AND s.index_name = t.index_name
            WHERE s.table_schema = DATABASE()
                AND s.table_name = 'trades'
            ORDER BY t.rows_read DESC, s.cardinality DESC
        ";

        return DB::select($query);
    }

    /**
     * Get table size and optimization recommendations
     */
    public static function getTableStats(): array
    {
        $stats = DB::select("
            SELECT 
                table_name,
                table_rows,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                ROUND((data_length / 1024 / 1024), 2) AS data_mb,
                ROUND((index_length / 1024 / 1024), 2) AS index_mb,
                ROUND((data_free / 1024 / 1024), 2) AS free_mb
            FROM information_schema.tables 
            WHERE table_schema = DATABASE() 
                AND table_name = 'trades'
        ");

        return $stats ? (array) $stats[0] : [];
    }
}
