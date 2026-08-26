<?php

namespace Tests\Unit\Models;

use App\Models\BookingVisitor;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingVisitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_visitor_belongs_to_booking()
    {
        $visitor = BookingVisitor::factory()->create();
        $this->assertInstanceOf(Booking::class, $visitor->booking);
    }
}
