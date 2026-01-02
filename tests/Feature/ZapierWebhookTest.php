<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use App\Models\TradeDeposit;
use App\Models\BonusTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ZapierWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected $webhookUrl = '/api/zapier/create-account';

    /**
     * Test successful account creation via Zapier webhook
     */
    public function test_zapier_webhook_creates_account_successfully()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'phone' => '+12345678901',
            'account_type' => 'Standard',
            'country' => 'United States',
            'country_code' => '+1',
        ];

        $response = $this->postJson($this->webhookUrl, $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Account successfully created with $50 bonus',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user_id',
                    'email',
                    'account_code',
                    'account_id',
                    'bonus_amount',
                ]
            ]);

        // Verify user was created
        $this->assertDatabaseHas('aspnetusers', [
            'email' => 'testuser@example.com',
            'created_from' => 'zapier',
        ]);

        // Verify account was created
        $this->assertDatabaseHas('accounts', [
            'email' => 'testuser@example.com',
            'created_from' => 'zapier',
            'demo' => 1, // Should be demo account
        ]);

        // Verify bonus deposit was created
        $this->assertDatabaseHas('trade_deposits', [
            'email' => 'testuser@example.com',
            'deposit_type' => 'zapier_bonus_deposit',
            'deposit_amount' => 50,
        ]);

        // Verify bonus transaction was created
        $this->assertDatabaseHas('bonus_transactions', [
            'email' => 'testuser@example.com',
            'bonus_type' => 'zapier_bonus_deposit',
            'bonus_amount' => 50,
        ]);
    }

    /**
     * Test webhook rejects duplicate email
     */
    public function test_zapier_webhook_rejects_duplicate_email()
    {
        // Create first user
        $payload = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'phone' => '+12345678901',
        ];

        $this->postJson($this->webhookUrl, $payload);

        // Try to create another with same email
        $duplicatePayload = [
            'name' => 'Duplicate User',
            'email' => 'existing@example.com',
            'phone' => '+12345678902',
        ];

        $response = $this->postJson($this->webhookUrl, $duplicatePayload);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test webhook requires all required fields
     */
    public function test_zapier_webhook_requires_name()
    {
        $payload = [
            'email' => 'test@example.com',
            'phone' => '+12345678901',
        ];

        $response = $this->postJson($this->webhookUrl, $payload);

        $response->assertStatus(400);
        $response->assertJsonValidationErrors('name');
    }

    /**
     * Test webhook requires valid email
     */
    public function test_zapier_webhook_requires_valid_email()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'phone' => '+12345678901',
        ];

        $response = $this->postJson($this->webhookUrl, $payload);

        $response->assertStatus(400);
        $response->assertJsonValidationErrors('email');
    }

    /**
     * Test webhook requires phone
     */
    public function test_zapier_webhook_requires_phone()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ];

        $response = $this->postJson($this->webhookUrl, $payload);

        $response->assertStatus(400);
        $response->assertJsonValidationErrors('phone');
    }

    /**
     * Test health check endpoint
     */
    public function test_health_check_endpoint()
    {
        $response = $this->getJson('/api/zapier/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'service' => 'Zapier Account Creation Webhook',
            ])
            ->assertJsonStructure([
                'status',
                'service',
                'timestamp',
            ]);
    }

    /**
     * Test created user is email verified
     */
    public function test_zapier_user_is_email_verified()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'verified@example.com',
            'phone' => '+12345678901',
        ];

        $this->postJson($this->webhookUrl, $payload);

        $user = User::where('email', 'verified@example.com')->first();

        $this->assertTrue($user->email_confirmed == 1);
    }

    /**
     * Test bonus is non-withdrawable
     */
    public function test_zapier_bonus_is_marked_non_withdrawable()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'bonus@example.com',
            'phone' => '+12345678901',
        ];

        $this->postJson($this->webhookUrl, $payload);

        $bonus = BonusTransaction::where('email', 'bonus@example.com')->first();

        $this->assertNotNull($bonus);
        $this->assertEquals('Non-Withdrawable Bonus - Zapier', $bonus->admin_remark);
    }

    /**
     * Test email sanitization
     */
    public function test_email_is_sanitized_to_lowercase()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'TestUser@EXAMPLE.COM',
            'phone' => '+12345678901',
        ];

        $response = $this->postJson($this->webhookUrl, $payload);

        $response->assertStatus(201);

        $user = User::where('email', 'testuser@example.com')->first();
        $this->assertNotNull($user);
    }

    /**
     * Test created_from field is set correctly
     */
    public function test_created_from_field_is_set_to_zapier()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'createdfrom@example.com',
            'phone' => '+12345678901',
        ];

        $this->postJson($this->webhookUrl, $payload);

        $user = User::where('email', 'createdfrom@example.com')->first();
        $this->assertEquals('zapier', $user->created_from);

        $account = Account::where('user_id', $user->id)->first();
        $this->assertEquals('zapier', $account->created_from);
    }

    /**
     * Test transaction rollback on error
     */
    public function test_account_creation_rolls_back_on_user_error()
    {
        $payload = [
            'name' => str_repeat('x', 300), // Invalid name length
            'email' => 'test@example.com',
            'phone' => '+12345678901',
        ];

        $response = $this->postJson($this->webhookUrl, $payload);

        $response->assertStatus(400);

        // Verify nothing was created
        $this->assertDatabaseMissing('aspnetusers', [
            'email' => 'test@example.com',
        ]);
    }
}
