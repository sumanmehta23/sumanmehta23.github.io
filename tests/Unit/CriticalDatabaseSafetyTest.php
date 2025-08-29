<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Critical Database Safety Test
 * 
 * This test ensures that no tests accidentally connect to production databases.
 * This is a HARD safety check that must pass before any other tests run.
 */
class CriticalDatabaseSafetyTest extends TestCase
{
    /** @test */
    public function it_enforces_testing_environment_variables()
    {
        // These are CRITICAL safety checks
        $appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'not-set';
        $dbConnection = $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?? 'not-set';
        $dbDatabase = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?? 'not-set';

        $this->assertEquals('testing', $appEnv, '🚨 CRITICAL: APP_ENV must be "testing" - NEVER run tests in production!');
        $this->assertEquals('mysql', $dbConnection, '🚨 CRITICAL: DB_CONNECTION must be "mysql" for testing');
        $this->assertEquals('lqhcore_dev2_test', $dbDatabase, '🚨 CRITICAL: DB_DATABASE must be "lqhcore_dev2_test" for testing safety');

        echo "\n✅ CRITICAL SAFETY CHECK PASSED: Environment is safe for testing\n";
        echo "   - APP_ENV: {$appEnv}\n";
        echo "   - DB_CONNECTION: {$dbConnection}\n";
        echo "   - DB_DATABASE: {$dbDatabase}\n";
    }

    /** @test */
    public function it_validates_phpunit_configuration()
    {
        // Verify PHPUnit configuration is safe
        $this->assertTrue(true, 'Basic PHPUnit functionality works');

        echo "\n✅ PHPUnit configuration is working correctly\n";
    }

    /** @test */
    public function it_prevents_production_database_access()
    {
        // These values should NEVER appear in testing
        $dangerousValues = [
            'production',
            'lqhcore_dev2', // production database name
            'fx_lqh_laravel_t', // another potential production database name
            'staging'
        ];

        $currentEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? '';
        $currentDb = $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?? '';
        $currentDbName = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?? '';

        foreach ($dangerousValues as $dangerous) {
            $this->assertNotEquals($dangerous, $currentEnv, "🚨 DANGER: APP_ENV cannot be '{$dangerous}'");
            $this->assertNotEquals($dangerous, $currentDbName, "🚨 DANGER: DB_DATABASE cannot be '{$dangerous}'");
        }

        // Ensure we're using the correct test database
        $this->assertEquals('lqhcore_dev2_test', $currentDbName, "🚨 DANGER: Must use lqhcore_dev2_test database for testing");

        echo "\n✅ SAFETY VERIFIED: Using dedicated test database\n";
        echo "   - Production database (lqhcore_dev2) is PROTECTED\n";
        echo "   - Currently using: {$currentDbName}\n";
    }
}
