<?php

namespace Database\Factories;

use App\Models\RelationshipManager;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RelationshipManagerFactory extends Factory
{
    protected $model = RelationshipManager::class;

    public function definition(): array
    {
        return [
            'user_id' => $this->faker->word(),
            'rm_id' => $this->faker->word(),
            'added_by' => $this->faker->word(),
            'created_at' => Carbon::now(),
        ];
    }
}
