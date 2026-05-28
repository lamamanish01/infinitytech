<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),

            'email' => fake()->unique()->safeEmail(),

            'username' => fake()->unique()->userName(),

            'password' => bcrypt('password'),

            'address' => fake()->address(),

            'contact_number' => fake()->phoneNumber(),

            'internet_plan_id' => InternetPlan::inRandomOrder()->value('id'),

            'branch_id' => 1,

            'registered' => now(),

            'expire_date' => Carbon::now()->addDays(rand(1, 30)),

            'status' => 'active',
        ];
    }
}
