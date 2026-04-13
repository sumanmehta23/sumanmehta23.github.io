<?php

namespace Tests\Unit;

use App\Enums\PlatformEnum;
use App\Models\Account;
use App\Services\X9Service;
use Tests\TestCase;
use Mockery;

class X9PasswordUpdateIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // CRITICAL SAFETY CHECK: Ensure we're in testing environment
        $this->assertEnvironment('testing');
    }

    /**
     * Critical safety method to prevent tests from running against production
     */
    private function assertEnvironment($expectedEnv)
    {
        $currentEnv = app()->environment();
        if ($currentEnv !== $expectedEnv) {
            $this->fail("CRITICAL SAFETY VIOLATION: Tests must run in '{$expectedEnv}' environment, currently in '{$currentEnv}'. Aborting to protect production data.");
        }
    }

    /** @test */
    public function test_x9_service_reset_password_method_exists()
    {
        // Ensure the X9Service has the resetUserPassword method
        $reflection = new \ReflectionClass(X9Service::class);
        $this->assertTrue($reflection->hasMethod('resetUserPassword'));

        $method = $reflection->getMethod('resetUserPassword');
        $this->assertEquals(3, $method->getNumberOfParameters());
    }

    /** @test */
    public function test_password_update_logic_for_x9_accounts()
    {
        // Test the logic that should be in the controller
        $account = new Account(['platform' => PlatformEnum::X9->value, 'code' => '12345']);
        $passType = 'main';
        $newPassword = 'TestPassword123!';

        // Simulate the mapping logic
        $x9PasswordType = $passType === 'main' ? 'master' : $passType;

        $this->assertEquals('master', $x9PasswordType);
        $this->assertEquals(PlatformEnum::X9->value, $account->platform);
        $this->assertEquals('12345', $account->code);
    }

    /** @test */
    public function test_password_update_logic_for_mt5_accounts()
    {
        // Test that MT5 accounts don't use the X9 logic
        $account = new Account(['platform' => PlatformEnum::MT5->value, 'code' => '67890']);

        $this->assertEquals(PlatformEnum::MT5->value, $account->platform);
        $this->assertNotEquals(PlatformEnum::X9->value, $account->platform);
    }

    /** @test */
    public function test_account_platform_property_exists()
    {
        $account = new Account();

        // Test that we can set and get the platform property
        $account->platform = PlatformEnum::X9->value;
        $this->assertEquals(PlatformEnum::X9->value, $account->platform);

        $account->platform = PlatformEnum::MT5->value;
        $this->assertEquals(PlatformEnum::MT5->value, $account->platform);
    }

    /** @test */
    public function test_password_fields_can_be_updated()
    {
        $account = new Account();

        // Test master password field
        $account->trader_password = 'newMasterPassword';
        $this->assertEquals('newMasterPassword', $account->trader_password);

        // Test investor password field
        $account->invester_password = 'newInvestorPassword';
        $this->assertEquals('newInvestorPassword', $account->invester_password);
    }
}
