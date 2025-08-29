<?php

namespace Tests\Unit;

use Tests\TestCase;

class DatabaseSafetyTest extends TestCase
{
    /** @test */
    public function test_environment_is_testing()
    {
        $this->assertEquals('testing', app()->environment());
    }

    /** @test */
    public function test_database_is_sqlite_memory()
    {
        $this->assertEquals('sqlite', config('database.default'));
        $this->assertEquals(':memory:', config('database.connections.sqlite.database'));
    }

    /** @test */
    public function test_dangerous_env_vars_are_not_set_for_production()
    {
        // These should not point to production values in testing
        $dbHost = env('DB_HOST');
        $dbDatabase = env('DB_DATABASE');

        if ($dbDatabase && $dbDatabase !== ':memory:') {
            $this->fail("DB_DATABASE should be ':memory:' in testing, got: {$dbDatabase}");
        }

        $this->assertTrue(true, 'Database safety checks passed');
    }
}
