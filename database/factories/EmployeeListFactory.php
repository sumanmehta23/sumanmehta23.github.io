<?php

namespace Database\Factories;

use App\Models\EmployeeList;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class EmployeeListFactory extends Factory
{
    protected $model = EmployeeList::class;

    public function definition(): array
    {
        return [
            'updated_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'remember_token' => Str::random(10),
            'state' => $this->faker->word(),
            'city' => $this->faker->city(),
            'zipcode' => $this->faker->randomNumber(),
            'dial_code' => $this->faker->word(),
            'country' => $this->faker->country(),
            'email_token_time' => $this->faker->unique()->safeEmail(),
            'email_confirmed' => $this->faker->unique()->safeEmail(),
            'emailToken' => $this->faker->unique()->safeEmail(),
            'userAccessLevel' => $this->faker->word(),
            'userRole' => $this->faker->word(),
            'userDepartment' => $this->faker->word(),
            'empId' => $this->faker->word(),
            'profile_pic' => $this->faker->word(),
            'status' => $this->faker->randomNumber(),
            'db_prefex' => $this->faker->word(),
            'company_number' => $this->faker->word(),
            'company_address' => $this->faker->address(),
            'company_name' => $this->faker->name(),
            'uid' => $this->faker->word(),
            'website' => $this->faker->word(),
            'address' => $this->faker->address(),
            'number' => $this->faker->word(),
            'two_factor_enabled' => $this->faker->boolean(),
            'two_factor_confirmed_at' => $this->faker->word(),
            'two_factor_recovery_codes' => $this->faker->word(),
            'two_factor_secret' => $this->faker->word(),
            'password' => bcrypt($this->faker->password()),
            'dob' => $this->faker->word(),
            'gender' => $this->faker->word(),
            'email' => $this->faker->unique()->safeEmail(),
            'username' => $this->faker->userName(),
            'client_index' => $this->faker->randomNumber(),

            'role_id' => Role::factory(),
        ];
    }
}
