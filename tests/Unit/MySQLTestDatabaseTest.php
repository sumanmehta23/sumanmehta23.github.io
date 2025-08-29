<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * MySQL Test Database Connection Test
 * 
 * This test verifies that Laravel tests can connect to the MySQL test database
 * and that the RefreshDatabase trait works correctly.
 */
class MySQLTestDatabaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_connects_to_mysql_test_database()
    {
        // Verify database configuration
        $this->assertEquals('mysql', config('database.default'));
        $this->assertEquals('lqhcore_dev2_test', config('database.connections.mysql.database'));

        echo "\n✅ Connected to MySQL test database: " . config('database.connections.mysql.database') . "\n";
    }

    /** @test */
    public function it_can_run_database_operations_safely()
    {
        // Test basic database operations
        $result = DB::select('SELECT DATABASE() as db_name');
        $currentDb = $result[0]->db_name;

        $this->assertEquals('lqhcore_dev2_test', $currentDb, 'Must be using test database');

        // Verify we can create and query tables (migrations should work)
        DB::statement('CREATE TABLE IF NOT EXISTS test_safety_check (id INT PRIMARY KEY, name VARCHAR(255))');
        DB::insert('INSERT INTO test_safety_check (id, name) VALUES (1, ?)', ['safety_test']);

        $record = DB::select('SELECT * FROM test_safety_check WHERE id = 1');
        $this->assertEquals('safety_test', $record[0]->name);

        // Clean up
        DB::statement('DROP TABLE test_safety_check');

        echo "\n✅ Database operations work correctly on test database\n";
        echo "   - Current database: {$currentDb}\n";
        echo "   - Production database (lqhcore_dev2) is PROTECTED\n";
    }

    /** @test */
    public function it_prevents_accidental_production_access()
    {
        // Get the current database name from the actual connection
        $result = DB::select('SELECT DATABASE() as db_name');
        $currentDb = $result[0]->db_name;

        // Critical safety checks
        $this->assertNotEquals('lqhcore_dev2', $currentDb, '🚨 CRITICAL: Must not use production database');
        $this->assertNotEquals('fx_lqh_laravel_t', $currentDb, '🚨 CRITICAL: Must not use old production database');
        $this->assertEquals('lqhcore_dev2_test', $currentDb, '🚨 CRITICAL: Must use test database');

        echo "\n🛡️ PRODUCTION DATABASE ACCESS PREVENTED\n";
        echo "   - Current: {$currentDb} ✅\n";
        echo "   - Production (lqhcore_dev2): PROTECTED 🔒\n";
    }
}
