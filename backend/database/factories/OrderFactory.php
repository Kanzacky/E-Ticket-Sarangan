<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $visitDate = fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d');
        $datePrefix = date('Ymd', strtotime($visitDate));

        return [
            'user_id' => User::factory(),
            'order_code' => 'ETK-' . $datePrefix . '-' . strtoupper(Str::random(6)),
            'visit_date' => $visitDate,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => '08' . fake()->numerify('##########'),
            'total_quantity' => 1,
            'total_amount' => 20000,
            'status' => 'PENDING',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PAID',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'CANCELLED',
        ]);
    }
}
