<?php

namespace Tests\Unit\Models;

use App\Models\Ticket;
use App\Models\Booking;
use App\Models\TicketCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_belongs_to_booking_visitor()
    {
        $ticket = Ticket::factory()->create();

        $this->assertInstanceOf(\App\Models\BookingVisitor::class, $ticket->visitor);
    }
}
