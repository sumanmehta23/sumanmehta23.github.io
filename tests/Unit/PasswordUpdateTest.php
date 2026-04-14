<?php

namespace Tests\Unit;

use App\Enums\PlatformEnum;
use Tests\TestCase;
use App\Models\Account;
use App\Models\User;
use App\Services\MT5Service;
use App\Services\X9Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we're using test database
        $this->assertDatabaseConnection();

        // Mock the settings function to prevent database queries during service initialization
        $this->app->instance('view', new class {
            public function shared($key)
            {
                return [
                    'mt5_server_ip' => '127.0.0.1',
                    'mt5_server_port' => 443,
                    'mt5_server_web_login' => 'test_login',
                    'mt5_server_web_password' => 'test_password',
                    'x9_api_url' => 'https://test.x9.com',
                    'x9_api_key' => 'test_api_key',
                ];
            }
        });

        // Mock external services to prevent real connections
        $this->app->bind(MT5Service::class, function () {
            return $this->createMock(MT5Service::class);
        });

        $this->app->bind(X9Service::class, function () {
            return $this->createMock(X9Service::class);
        });
    }

    private function assertDatabaseConnection(): void
    {
        $connection = config('database.default');
        $database = config('database.connections.' . $connection . '.database');

        $this->assertEquals('sqlite', $connection, 'Tests must use SQLite connection');
        $this->assertEquals(':memory:', $database, 'Tests must use in-memory database');
    }

    /** @test */
    public function it_can_create_test_accounts()
    {
        // Test that we can create accounts without hitting production database
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $account = Account::factory()->create([
            'user_id' => $user->id,
            'platform' => PlatformEnum::MT5->value,
            'login' => '12345',
        ]);

        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'platform' => PlatformEnum::MT5->value,
            'login' => '12345',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // Verify we're using the correct database
        $this->assertDatabaseConnection();
    }

    /** @test */
    public function it_identifies_platform_correctly()
    {
        $user = User::factory()->create();

        $mt5Account = Account::factory()->create([
            'user_id' => $user->id,
            'platform' => PlatformEnum::MT5->value,
        ]);

        $x9Account = Account::factory()->create([
            'user_id' => $user->id,
            'platform' => PlatformEnum::X9->value,
        ]);

        $this->assertEquals(PlatformEnum::MT5->value, $mt5Account->platform);
        $this->assertEquals(PlatformEnum::X9->value, $x9Account->platform);
    }

    /** @test */
    public function it_passes_database_safety_checks()
    {
        // Verify all safety measures
        $this->assertEquals('testing', config('app.env'), 'Must be in testing environment');
        $this->assertEquals('sqlite', config('database.default'), 'Must use SQLite for testing');
        $this->assertEquals(':memory:', config('database.connections.sqlite.database'), 'Must use in-memory database');

        // Test that we can safely create and delete data
        $user = User::factory()->create(['email' => 'safety@test.com']);
        $this->assertDatabaseHas('users', ['email' => 'safety@test.com']);

        $user->delete();
        $this->assertDatabaseMissing('users', ['email' => 'safety@test.com']);

        echo "✅ All database safety checks passed\n";
    }
}
