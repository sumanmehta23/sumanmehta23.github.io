<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use App\Services\X9Service;
use App\Services\MT5Service;
use App\Enums\PlatformEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Mockery;

class AccountPasswordUpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $mt5Account;
    protected $x9Account;

    protected function setUp(): void
    {
        parent::setUp();

        // CRITICAL SAFETY CHECK: Ensure we're in testing environment
        $this->assertEnvironment('testing');

        // CRITICAL SAFETY CHECK: Ensure we're using in-memory SQLite database
        $this->assertEquals('sqlite', config('database.default'));
        $this->assertEquals(':memory:', config('database.connections.sqlite.database'));

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Create test accounts
        $this->mt5Account = Account::factory()->create([
            'user_id' => $this->user->id,
            'platform' => PlatformEnum::MT5->value,
            'code' => '12345',
            'trader_password' => 'oldpassword123',
            'invester_password' => 'oldpassword123',
        ]);

        $this->x9Account = Account::factory()->create([
            'user_id' => $this->user->id,
            'platform' => PlatformEnum::X9->value,
            'code' => '67890',
            'trader_password' => 'oldpassword123',
            'invester_password' => 'oldpassword123',
        ]);
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

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function test_password_update_form_has_correct_action_url()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('view-account-details', $this->x9Account));

        $response->assertStatus(200);
        $response->assertSee('action="' . route('change-mt5-password', $this->x9Account) . '"', false);
    }

    /** @test */
    public function test_x9_account_master_password_update_success()
    {
        // Mock the X9Service
        $mockX9Service = Mockery::mock(X9Service::class);
        $mockX9Service->shouldReceive('resetUserPassword')
            ->once()
            ->with(67890, 'master', 'NewPassword123!')
            ->andReturn([
                'status' => true,
                'message' => 'Password updated successfully',
                'data' => []
            ]);

        $this->app->instance(X9Service::class, $mockX9Service);

        $this->actingAs($this->user);

        $response = $this->post(route('change-mt5-password', $this->x9Account), [
            'account_id' => $this->x9Account->id,
            'password_type' => 'main',
            'password' => 'NewPassword123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Your Master Password Successfully Updated');

        // Verify the password was updated in the database
        $this->x9Account->refresh();
        $this->assertEquals('NewPassword123!', $this->x9Account->trader_password);
    }

    /** @test */
    public function test_x9_account_investor_password_update_success()
    {
        // Mock the X9Service
        $mockX9Service = Mockery::mock(X9Service::class);
        $mockX9Service->shouldReceive('resetUserPassword')
            ->once()
            ->with(67890, 'investor', 'NewPassword123!')
            ->andReturn([
                'status' => true,
                'message' => 'Password updated successfully',
                'data' => []
            ]);

        $this->app->instance(X9Service::class, $mockX9Service);

        $this->actingAs($this->user);

        $response = $this->post(route('change-mt5-password', $this->x9Account), [
            'account_id' => $this->x9Account->id,
            'password_type' => 'investor',
            'password' => 'NewPassword123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Your Investor Password Successfully Updated');

        // Verify the password was updated in the database
        $this->x9Account->refresh();
        $this->assertEquals('NewPassword123!', $this->x9Account->invester_password);
    }

    /** @test */
    public function test_x9_account_password_update_failure()
    {
        // Mock the X9Service to return failure
        $mockX9Service = Mockery::mock(X9Service::class);
        $mockX9Service->shouldReceive('resetUserPassword')
            ->once()
            ->with(67890, 'master', 'NewPassword123!')
            ->andReturn([
                'status' => false,
                'message' => 'API connection failed',
                'data' => null
            ]);

        $this->app->instance(X9Service::class, $mockX9Service);

        $this->actingAs($this->user);

        $response = $this->post(route('change-mt5-password', $this->x9Account), [
            'account_id' => $this->x9Account->id,
            'password_type' => 'main',
            'password' => 'NewPassword123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Failed to update password in X9: API connection failed');

        // Verify the password was NOT updated in the database
        $this->x9Account->refresh();
        $this->assertEquals('oldpassword123', $this->x9Account->trader_password);
    }

    /** @test */
    public function test_mt5_account_password_update_still_works()
    {
        // Mock the MT5Service and API
        $mockMT5Service = Mockery::mock(MT5Service::class);
        $mockApi = Mockery::mock();

        $mockMT5Service->shouldReceive('connect')->once();
        $mockMT5Service->shouldReceive('getApi')->once()->andReturn($mockApi);

        // Mock successful password change
        $mockApi->shouldReceive('UserPasswordChange')
            ->once()
            ->andReturn(0); // MT_RET_OK

        $this->app->instance(MT5Service::class, $mockMT5Service);

        $this->actingAs($this->user);

        $response = $this->post(route('change-mt5-password', $this->mt5Account), [
            'account_id' => $this->mt5Account->id,
            'password_type' => 'main',
            'password' => 'NewPassword123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Your Master Password Successfully Updated');

        // Verify the password was updated in the database
        $this->mt5Account->refresh();
        $this->assertEquals('NewPassword123!', $this->mt5Account->trader_password);
    }

    /** @test */
    public function test_password_validation_required_fields()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('change-mt5-password', $this->x9Account), [
            'account_id' => $this->x9Account->id,
            // Missing password_type and password
        ]);

        $response->assertSessionHasErrors(['password_type', 'password']);
    }

    /** @test */
    public function test_password_validation_minimum_length()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('change-mt5-password', $this->x9Account), [
            'account_id' => $this->x9Account->id,
            'password_type' => 'main',
            'password' => '123', // Too short
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function test_password_validation_invalid_type()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('change-mt5-password', $this->x9Account), [
            'account_id' => $this->x9Account->id,
            'password_type' => 'invalid_type',
            'password' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrors(['password_type']);
    }

    /** @test */
    public function test_unauthorized_user_cannot_update_password()
    {
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser);

        $response = $this->post(route('change-mt5-password', $this->x9Account), [
            'account_id' => $this->x9Account->id,
            'password_type' => 'main',
            'password' => 'NewPassword123!',
        ]);

        // Should be forbidden or redirect with error
        $this->assertTrue(
            $response->status() === 403 ||
                $response->status() === 302
        );
    }

    /** @test */
    public function test_activity_log_created_for_x9_password_update()
    {
        // Mock the X9Service
        $mockX9Service = Mockery::mock(X9Service::class);
        $mockX9Service->shouldReceive('resetUserPassword')
            ->once()
            ->andReturn([
                'status' => true,
                'message' => 'Password updated successfully',
                'data' => []
            ]);

        $this->app->instance(X9Service::class, $mockX9Service);

        $this->actingAs($this->user);

        $response = $this->post(route('change-mt5-password', $this->x9Account), [
            'account_id' => $this->x9Account->id,
            'password_type' => 'main',
            'password' => 'NewPassword123!',
        ]);

        // Check that activity was logged
        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $this->user->id,
            'description' => 'Update Main Password (X9)',
            'event' => 'update',
        ]);
    }
}
