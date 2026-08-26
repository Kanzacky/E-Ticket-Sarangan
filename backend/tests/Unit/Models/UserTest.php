<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Booking;
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

    public function test_user_has_many_bookings()
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->bookings->contains($booking));
        $this->assertInstanceOf(Booking::class, $user->bookings->first());
    }
}
