<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\User;
use App\Models\AccountType;
use App\Enums\PlatformEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $account_type_id = AccountType::first()->value('id');
        return [
            'id' => $this->faker->uuid(),
            'user_id' => User::factory(), // Creates a user when creating an account
            'account_type_id' => $account_type_id, // For now we assign same account type for all accounts
            'demo' => $this->faker->boolean(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'code' => $this->faker->randomNumber(6, true),
            'credit' => null,
            'leverage' => $this->faker->randomElement(['50', '100', '200', '500']),
            'currency' => $this->faker->currencyCode(),
            'balance' => $this->faker->randomFloat(2, 0, 10000),
            'equity' => $this->faker->randomFloat(2, 0, 10000),
            'lots_completed' => $this->faker->randomFloat(2, 0, 100),
            'margin_free' => $this->faker->randomFloat(2, 0, 10000),
            'margin_level' => $this->faker->randomFloat(2, 0, 500),
            'margin_level_type' => $this->faker->randomElement(['ok', 'margin call', 'stop out']),
            'adjustment' => $this->faker->randomFloat(2, 0, 100),
            'deposit' => $this->faker->randomFloat(2, 0, 10000),
            'withdraw' => $this->faker->randomFloat(2, 0, 10000),
            'internal_transfer' => $this->faker->randomFloat(2, 0, 10000),
            'internal_deposit' => $this->faker->randomFloat(2, 0, 10000),
            'trader_password' => $this->faker->password(8, 16),
            'invester_password' => $this->faker->password(8, 16),
            'phone_password' => $this->faker->password(8, 16),
            'platform' => $this->faker->randomElement(PlatformEnum::all()),
            'registered_date' => $this->faker->dateTimeThisYear(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'suspended']),
            'bonus_deposit' => $this->faker->randomFloat(2, 0, 10000),
            'w_bonus_deposit' => $this->faker->randomFloat(2, 0, 10000),
            'ib1' => '',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
    }
}
