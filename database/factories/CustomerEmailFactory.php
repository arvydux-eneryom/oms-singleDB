<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerEmail>
 */
class CustomerEmailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => \App\Models\Customer::factory(),
            'email' => fake()->safeEmail(),
            'type' => fake()->randomElement(['primary', 'work', 'personal']),
            'is_verified' => fake()->boolean(),
        ];
    }
}
