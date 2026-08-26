<?php

namespace Database\Factories;

use App\Models\AccommodationBooking;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AccommodationBooking>
 */
class AccommodationBookingFactory extends Factory
{
    protected $model = AccommodationBooking::class;

    public function definition()
    {
        return [
            'booking_code' => 'ACC-' . strtoupper(Str::random(8)),
            'user_id' => null,
            'accommodation_id' => null,
            'check_in' => null,
            'check_out' => null,
            'rooms' => 1,
            'guests' => 2,
            'total_price' => null,
            'guest_name' => $this->faker->name,
            'guest_phone' => $this->faker->phoneNumber,
            'status' => 'pending',
            'notes' => null,
        ];
    }