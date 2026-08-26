<?php

namespace Database\Factories;

use App\Models\Accommodation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Accommodation>
 */
class AccommodationFactory extends Factory
{
    protected $model = Accommodation::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'description' => $this->faker->sentence,
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'image_url' => null,
            'price_per_night' => $this->faker->numberBetween(50000, 1000000),
            'total_rooms' => $this->faker->numberBetween(5, 50),
            'available_rooms' => $this->faker->numberBetween(1, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5),
            'facilities' => null,
            'is_active' => $this->faker->boolean,
        ];
    }