<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'booking_code' => strtoupper(Str::random(8)),
            'booking_date' => now()->format('Y-m-d'),
            'visit_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'total_amount' => $this->faker->numberBetween(50000, 200000),
            'status' => $this->faker->randomElement(['pending', 'paid', 'cancelled']),
        ];
    }
}
