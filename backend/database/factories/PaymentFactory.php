<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'payment_method' => 'bank_transfer',
            'amount' => $this->faker->numberBetween(50000, 200000),
            'status' => $this->faker->randomElement(['pending', 'success', 'failed']),
            'transaction_id' => Str::random(20),
            'paid_at' => null,
        ];
    }
}
