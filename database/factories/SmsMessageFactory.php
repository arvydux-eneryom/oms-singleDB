<?php

namespace Database\Factories;

use App\Models\SmsMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmsMessageFactory extends Factory
{
    protected $model = SmsMessage::class;

    public function definition(): array
    {
        return [
            'sms_sid' => 'SM' . $this->faker->uuid(),
            'to' => '+370' . $this->faker->numerify('########'),
            'from' => '+447426914907',
            'body' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['queued', 'sent', 'delivered', 'undelivered']),
            'account_sid' => 'AC' . $this->faker->uuid(),
            'message_type' => 'outgoing',
            'user_id' => User::factory(),
        ];
    }

    public function incoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'incoming',
            'from' => '+370' . $this->faker->numerify('########'),
            'to' => '+447426914907',
            'user_id' => null,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
        ]);
    }

    public function queued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'queued',
        ]);
    }
}
