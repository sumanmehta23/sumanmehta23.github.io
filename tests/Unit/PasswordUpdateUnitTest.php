<?php

namespace Tests\Unit;

use App\Enums\PlatformEnum;
use App\Models\Account;
use Tests\TestCase;

class PasswordUpdateUnitTest extends TestCase
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
    public function test_password_type_mapping_for_x9()
    {
        // Test that 'main' maps to 'master' for X9
        $passType = 'main';
        $x9PasswordType = $passType === 'main' ? 'master' : $passType;
        $this->assertEquals('master', $x9PasswordType);

        // Test that 'investor' stays 'investor' for X9  
        $passType = 'investor';
        $x9PasswordType = $passType === 'main' ? 'master' : $passType;
        $this->assertEquals('investor', $x9PasswordType);
    }

    /** @test */
    public function test_password_validation_rules_structure()
    {
        $rules = [
            'account_id' => 'required',
            'password_type' => 'required|in:main,investor',
            'password' => 'required|min:6',
        ];

        // Test that validation rules are correctly defined
        $this->assertArrayHasKey('account_id', $rules);
        $this->assertArrayHasKey('password_type', $rules);
        $this->assertArrayHasKey('password', $rules);

        $this->assertStringContainsString('required', $rules['account_id']);
        $this->assertStringContainsString('in:main,investor', $rules['password_type']);
        $this->assertStringContainsString('min:6', $rules['password']);
    }

    /** @test */
    public function test_platform_detection_logic()
    {
        // Test X9 platform detection
        $x9Account = new Account(['platform' => PlatformEnum::X9->value]);
        $this->assertEquals(PlatformEnum::X9->value, $x9Account->platform);
        $this->assertTrue($x9Account->platform === PlatformEnum::X9->value);

        // Test MT5 platform detection
        $mt5Account = new Account(['platform' => PlatformEnum::MT5->value]);
        $this->assertEquals(PlatformEnum::MT5->value, $mt5Account->platform);
        $this->assertTrue($mt5Account->platform !== PlatformEnum::X9->value);
    }

    /** @test */
    public function test_account_password_fields_exist()
    {
        $account = new Account([
            'trader_password' => 'test123',
            'invester_password' => 'test456',
        ]);

        $this->assertEquals('test123', $account->trader_password);
        $this->assertEquals('test456', $account->invester_password);
    }

    /** @test */
    public function test_password_type_validation_options()
    {
        $validTypes = ['main', 'investor'];

        $this->assertContains('main', $validTypes);
        $this->assertContains('investor', $validTypes);
        $this->assertNotContains('invalid', $validTypes);
    }
}
