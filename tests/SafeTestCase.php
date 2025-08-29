<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class SafeTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // CRITICAL SAFETY ENFORCEMENT: Prevent tests from running against production
        $this->enforceTestingEnvironment();
    }

    /**
     * CRITICAL SAFETY METHOD: Ensures tests never run against production database
     * This method will abort test execution if not in proper testing environment
     */
    protected function enforceTestingEnvironment(): void
    {
        $currentEnv = app()->environment();

        // Check environment
        if ($currentEnv !== 'testing') {
            $this->fail("❌ CRITICAL SAFETY VIOLATION: Tests must run in 'testing' environment, currently in '{$currentEnv}'. ABORTING to protect production data!");
        }

        // Check database connection when using database
        $usesRefreshDatabase = in_array(\Illuminate\Foundation\Testing\RefreshDatabase::class, class_uses_recursive($this));
        if ($usesRefreshDatabase) {
            $dbConnection = config('database.default');
            $dbDatabase = config("database.connections.{$dbConnection}.database");

            // Ensure we're using SQLite in-memory for testing
            if ($dbConnection !== 'sqlite' || $dbDatabase !== ':memory:') {
                $this->fail("❌ CRITICAL SAFETY VIOLATION: Tests must use SQLite in-memory database. Current: {$dbConnection} -> {$dbDatabase}. ABORTING to protect production data!");
            }
        }

        // Additional safety: Check for production-like environment variables
        $dangerousEnvVars = [
            'DB_HOST' => env('DB_HOST'),
            'DB_DATABASE' => env('DB_DATABASE'),
            'DB_USERNAME' => env('DB_USERNAME'),
        ];

        foreach ($dangerousEnvVars as $var => $value) {
            if ($value && $var === 'DB_DATABASE' && $value !== ':memory:') {
                $this->fail("❌ CRITICAL SAFETY VIOLATION: {$var} is set to '{$value}' in testing. This could indicate production database access. ABORTING!");
            }
        }
    }

    /**
     * Assert that we're in the expected environment
     */
    protected function assertEnvironment(string $expectedEnv): void
    {
        $currentEnv = app()->environment();
        if ($currentEnv !== $expectedEnv) {
            $this->fail("❌ CRITICAL SAFETY VIOLATION: Expected '{$expectedEnv}' environment, currently in '{$currentEnv}'. ABORTING to protect production data!");
        }
    }

    /**
     * Assert that we're using the testing database
     */
    protected function assertTestingDatabase(): void
    {
        $this->assertEquals('sqlite', config('database.default'), 'Tests must use SQLite database');
        $this->assertEquals(':memory:', config('database.connections.sqlite.database'), 'Tests must use in-memory SQLite database');
    }
}
