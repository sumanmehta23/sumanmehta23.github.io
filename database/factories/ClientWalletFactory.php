<?php

namespace Database\Factories;

use App\Models\ClientWallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientWallet>
 */
class ClientWalletFactory extends Factory
{
    protected $model = ClientWallet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'client_wallet_id' => 0, // Assuming it serves a specific purpose
            'wallet_name' => $this->faker->randomElement(['Trust Wallet', 'MetaMask', 'Coinbase Wallet']),
            'wallet_currency' => $this->faker->randomElement(['USDT', 'BTC', 'ETH']),
            'wallet_network' => $this->faker->randomElement(['ETH_USDT', 'BSC_USDT', 'BTC']),
            'wallet_address' => $this->faker->regexify('0x[a-fA-F0-9]{40}'),
            'status' => $this->faker->randomElement([0, 1]), // 0 = inactive, 1 = active
            'user_id' => User::factory(), // Generates an associated user
            'admin_action_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
    }
}