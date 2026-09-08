<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'role' => 'admin',
        ]);
    }

    public function test_user_has_many_orders()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->orders->contains($order));
        $this->assertInstanceOf(Order::class, $user->orders->first());
    }
}
