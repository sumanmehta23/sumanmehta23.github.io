<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'uid' => null,
            'email' => $this->faker->unique()->safeEmail(),
            'email_confirmed' => $this->faker->boolean(),
            'password' => Hash::make('password'), // Default password
            'country_code' => null,
            'number' => $this->faker->phoneNumber(),
            'number_confirmed' => $this->faker->boolean(),
            'two_factor_enabled' => $this->faker->boolean(),
            'lockout_end_date' => null,
            'lockout_enabled' => $this->faker->boolean(),
            'access_count_failed' => 0,
            'username' => $this->faker->userName(),
            'profile_image_url' => null,
            'fullname' => $this->faker->name(),
            'byPartner' => 0,
            'date' => null,
            'status' => 1,
            'country' => $this->faker->country(),
            'dial_code' => null,
            'Isreferal' => $this->faker->boolean(),
            'referalId' => null,
            'zipcode' => $this->faker->postcode(),
            'address' => $this->faker->address(),
            'aboutme' => $this->faker->text(200),
            'imgName' => null,
            'education' => $this->faker->word(),
            'industry' => $this->faker->word(),
            'financial_industry' => null,
            'forex_exp' => null,
            'monthly_transaction' => null,
            'investment_plan' => null,
            'funds_source' => null,
            'investment_purpose' => null,
            'total_value' => null,
            'annual_income' => null,
            'polotically_person' => null,
            'bankruptcy' => null,
            'usa_resident' => null,
            'usa_tax' => null,
            'dob' => $this->faker->date(),
            'emailToken' => $this->faker->sha256(),
            'state' => $this->faker->state(),
            'city' => $this->faker->city(),
            'lang' => 'english',
            'email_token_time' => now(),
            'profile_image' => null,
            'gender' => $this->faker->randomElement(['male', 'female']),
            'referral' => '',
            'mail_otp' => null,
            'employee_status' => null,
            'cfd' => null,
            'other' => null,
            'kyc_type' => null,
            'kyc_front' => null,
            'kyc_back' => null,
            'bank_detail' => null,
            'account_holder_name' => null,
            'bank_name' => null,
            'bank_account_no' => null,
            'IFSC_Code' => null,
            'swift_code' => null,
            'kyc_verify' => 1,
            'client_status' => 0,
            'wallet_address' => null,
            'reg_date' => now(),
            'bank_status' => 0,
            'personal_status' => 0,
            'employemnet_status' => 0,
            'trading_status' => 0,
            'ib1' => '',
            'ib2' => null,
            'ib3' => null,
            'ib4' => null,
            'ib5' => null,
            'ib6' => null,
            'ib7' => null,
            'ib8' => null,
            'ib9' => null,
            'ib10' => null,
            'ib11' => null,
            'ib12' => null,
            'ib13' => null,
            'ib14' => null,
            'ib15' => null,
            'wallet_requested' => null,
            'wallet_enabled' => true,
            'wallet_requested_at' => null,
            'wallet_approved_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
    }
}