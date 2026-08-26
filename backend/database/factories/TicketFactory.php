<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\TicketCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_visitor_id' => \App\Models\BookingVisitor::factory(),
            'ticket_code' => strtoupper(Str::random(10)),
            'status' => $this->faker->randomElement(['valid', 'used', 'expired', 'cancelled']),
        ];
    }
}
