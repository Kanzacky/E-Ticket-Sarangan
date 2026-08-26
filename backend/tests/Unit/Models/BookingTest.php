<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_belongs_to_user()
    {
        $booking = Booking::factory()->create();
        $this->assertInstanceOf(User::class, $booking->user);
    }

    public function test_booking_has_many_visitors()
    {
        $booking = Booking::factory()->create();
        $visitor = \App\Models\BookingVisitor::factory()->create(['booking_id' => $booking->id]);

        $this->assertTrue($booking->visitors->contains($visitor));
    }

    public function test_booking_has_one_payment()
    {
        $booking = Booking::factory()->create();
        $payment = Payment::factory()->create(['booking_id' => $booking->id]);

        $this->assertInstanceOf(Payment::class, $booking->payment);
    }
}
