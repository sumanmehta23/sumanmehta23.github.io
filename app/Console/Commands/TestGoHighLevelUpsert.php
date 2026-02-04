<?php

namespace App\Console\Commands;

use App\Services\GoHighLevelService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Test GoHighLevel Upsert Contact Functionality
 * 
 * This command tests the upsert endpoint to ensure:
 * 1. New contacts are created successfully
 * 2. Existing contacts (by phone) are updated instead of creating duplicates
 * 3. Tags and custom fields are properly handled
 * 
 * Usage:
 * php artisan test:ghl-upsert
 */
class TestGoHighLevelUpsert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:ghl-upsert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test GoHighLevel upsert contact functionality (create/update)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing GoHighLevel Upsert Contact Functionality');
        $this->newLine();

        $ghlService = app(GoHighLevelService::class);

        // Check credentials
        if (!$ghlService->hasValidCredentials()) {
            $this->error('❌ GHL credentials not configured!');
            $this->warn('Please set GHL_API_KEY and GHL_LOCATION_ID in your .env file');
            return 1;
        }

        $this->info('✅ GHL credentials configured');
        $this->newLine();

        // Generate unique test data
        $timestamp = time();
        $testEmail = "test-upsert-{$timestamp}@lqh-ghl-test.com";
        $testPhone = '+1555000' . substr($timestamp, -4);
        $testName = "Test Upsert User {$timestamp}";

        $this->info("📋 Test Data:");
        $this->line("   Email: {$testEmail}");
        $this->line("   Phone: {$testPhone}");
        $this->line("   Name: {$testName}");
        $this->newLine();

        // Test 1: Create new contact
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('TEST 1: Creating NEW contact (should CREATE)');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $contactPayload1 = [
            'email' => $testEmail,
            'fullname' => $testName,
            'number' => $testPhone,
            'country' => 'United States',
            'source' => 'Test Upsert',
            'refercode' => 'TEST' . $timestamp,
            'user_id' => 99999,
            'tags' => ['Test Contact', 'Upsert Test'],
        ];

        $this->line('Payload:');
        $this->line(json_encode($contactPayload1, JSON_PRETTY_PRINT));
        $this->newLine();

        $result1 = $ghlService->createContact($contactPayload1);

        if ($result1) {
            $this->info('✅ SUCCESS: Contact created/upserted');
            $this->warn('   Check GHL dashboard to verify contact was CREATED');
        } else {
            $this->error('❌ FAILED: Contact creation failed');
            $this->warn('   Check logs for details: storage/logs/laravel.log');
            return 1;
        }

        $this->newLine();
        $this->line('⏳ Waiting 2 seconds before next test...');
        sleep(2);
        $this->newLine();

        // Test 2: Update existing contact (same phone, different email/name)
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('TEST 2: Updating EXISTING contact (same phone, should UPDATE)');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $updatedEmail = "updated-{$timestamp}@lqh-ghl-test.com";
        $updatedName = "Updated Test User {$timestamp}";

        $contactPayload2 = [
            'email' => $updatedEmail, // Different email
            'fullname' => $updatedName, // Different name
            'number' => $testPhone, // SAME phone (this should match existing contact)
            'country' => 'United States',
            'source' => 'Test Upsert Updated',
            'refercode' => 'UPDATED' . $timestamp,
            'user_id' => 88888,
            'tags' => ['Test Contact', 'Upsert Test', 'Updated'], // Additional tag
        ];

        $this->line('Payload (same phone, different email/name):');
        $this->line(json_encode($contactPayload2, JSON_PRETTY_PRINT));
        $this->newLine();
        $this->warn('⚠️  Expected: Contact should be UPDATED (not create duplicate)');
        $this->warn('⚠️  Phone matches existing contact, so GHL should update that contact');
        $this->newLine();

        $result2 = $ghlService->createContact($contactPayload2);

        if ($result2) {
            $this->info('✅ SUCCESS: Contact upserted');
            $this->warn('   Check GHL dashboard to verify:');
            $this->warn('   - Contact was UPDATED (not duplicated)');
            $this->warn('   - Email/Name should be updated to new values');
            $this->warn('   - Tags should include all tags (merged)');
            $this->warn('   - Custom fields should be updated');
        } else {
            $this->error('❌ FAILED: Contact update failed');
            $this->warn('   Check logs for details: storage/logs/laravel.log');
            return 1;
        }

        $this->newLine();
        $this->line('⏳ Waiting 2 seconds before next test...');
        sleep(2);
        $this->newLine();

        // Test 3: Test with Main IB tag
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('TEST 3: Upsert with Main IB tag (should UPDATE existing)');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $contactPayload3 = [
            'email' => $updatedEmail,
            'fullname' => $updatedName,
            'number' => $testPhone, // Same phone again
            'country' => 'United States',
            'source' => 'Main IB',
            'refercode' => 'MAINIB' . $timestamp,
            'user_id' => 77777,
            'tags' => ['Main IB'], // Main IB tag
        ];

        $this->line('Payload (Main IB tag):');
        $this->line(json_encode($contactPayload3, JSON_PRETTY_PRINT));
        $this->newLine();
        $this->warn('⚠️  Expected: Contact should be UPDATED with "Main IB" tag');
        $this->newLine();

        $result3 = $ghlService->createContact($contactPayload3);

        if ($result3) {
            $this->info('✅ SUCCESS: Contact upserted with Main IB tag');
            $this->warn('   Check GHL dashboard to verify:');
            $this->warn('   - Contact has "Main IB" tag');
            $this->warn('   - Source is "Main IB"');
            $this->warn('   - Custom fields updated');
        } else {
            $this->error('❌ FAILED: Contact upsert failed');
            $this->warn('   Check logs for details: storage/logs/laravel.log');
            return 1;
        }

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('✅ All tests completed!');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $this->info('📊 Summary:');
        $this->line('   1. New contact creation: ' . ($result1 ? '✅ PASSED' : '❌ FAILED'));
        $this->line('   2. Existing contact update: ' . ($result2 ? '✅ PASSED' : '❌ FAILED'));
        $this->line('   3. Main IB tag update: ' . ($result3 ? '✅ PASSED' : '❌ FAILED'));
        $this->newLine();

        $this->warn('🔍 Next Steps:');
        $this->line('   1. Check GHL dashboard for contact: ' . $testPhone);
        $this->line('   2. Verify contact was UPDATED (not duplicated)');
        $this->line('   3. Check tags include: "Main IB"');
        $this->line('   4. Verify custom fields: referral_code, lqh_user_id');
        $this->line('   5. Check logs: storage/logs/laravel.log');
        $this->newLine();

        $this->info('📝 Test Contact Details:');
        $this->line('   Phone: ' . $testPhone);
        $this->line('   Email: ' . $updatedEmail);
        $this->line('   Name: ' . $updatedName);
        $this->line('   Source: Main IB');
        $this->line('   Tags: Main IB');
        $this->newLine();

        return 0;
    }
}
