<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use App\Enums\PlatformEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // CRITICAL SAFETY CHECK: Ensure we're in testing environment
        $this->assertEnvironment('testing');

        // CRITICAL SAFETY CHECK: Ensure we're using in-memory SQLite database
        $this->assertEquals('sqlite', config('database.default'));
        $this->assertEquals(':memory:', config('database.connections.sqlite.database'));

        // Run necessary seeders if needed
        $this->artisan('db:seed', ['--class' => 'AccountTypeSeeder']);
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
    public function test_password_update_form_shows_correct_action_for_x9_account()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Create an X9 account
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'platform' => PlatformEnum::X9->value,
            'code' => '67890',
            'trader_password' => 'oldpassword123',
            'invester_password' => 'oldpassword123',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('view-account-details', $account));

        $response->assertStatus(200);

        // Check that the form has the correct action URL
        $response->assertSee('action="' . route('change-mt5-password', $account) . '"', false);

        // Check that it shows X9 in the form
        $response->assertSee('X9 ACCOUNT');
    }

    /** @test */
    public function test_password_update_form_shows_correct_action_for_mt5_account()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Create an MT5 account
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'platform' => PlatformEnum::MT5->value,
            'code' => '12345',
            'trader_password' => 'oldpassword123',
            'invester_password' => 'oldpassword123',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('view-account-details', $account));

        $response->assertStatus(200);

        // Check that the form has the correct action URL
        $response->assertSee('action="' . route('change-mt5-password', $account) . '"', false);

        // Check that it shows MT5 in the form
        $response->assertSee('MT5 ACCOUNT');
    }

    /** @test */
    public function test_password_validation_works()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Create an account
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'platform' => PlatformEnum::X9->value,
            'code' => '67890',
        ]);

        $this->actingAs($user);

        // Test validation - missing fields
        $response = $this->post(route('change-mt5-password', $account), [
            'account_id' => $account->id,
            // Missing password_type and password
        ]);

        $response->assertSessionHasErrors(['password_type', 'password']);
    }

    /** @test */
    public function test_password_length_validation()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Create an account
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'platform' => PlatformEnum::X9->value,
            'code' => '67890',
        ]);

        $this->actingAs($user);

        // Test validation - password too short
        $response = $this->post(route('change-mt5-password', $account), [
            'account_id' => $account->id,
            'password_type' => 'main',
            'password' => '123', // Too short
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function test_invalid_password_type_validation()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Create an account
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'platform' => PlatformEnum::X9->value,
            'code' => '67890',
        ]);

        $this->actingAs($user);

        // Test validation - invalid password type
        $response = $this->post(route('change-mt5-password', $account), [
            'account_id' => $account->id,
            'password_type' => 'invalid_type',
            'password' => 'ValidPassword123!',
        ]);

        $response->assertSessionHasErrors(['password_type']);
    }

    /** @test */
    public function test_guest_cannot_access_password_update()
    {
        // Create an account without logging in
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'platform' => PlatformEnum::X9->value,
            'code' => '67890',
        ]);

        // Try to access the view as guest
        $response = $this->get(route('view-account-details', $account));

        // Should be redirected to login
        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_guest_cannot_submit_password_update()
    {
        // Create an account without logging in
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'platform' => PlatformEnum::X9->value,
            'code' => '67890',
        ]);

        // Try to submit password update as guest
        $response = $this->post(route('change-mt5-password', $account), [
            'account_id' => $account->id,
            'password_type' => 'main',
            'password' => 'NewPassword123!',
        ]);

        // Should be redirected to login
        $response->assertRedirect('/login');
    }
}
