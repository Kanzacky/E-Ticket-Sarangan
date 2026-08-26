<?php

namespace Tests\Unit\Models;

use App\Models\Checkin;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckinTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkin_belongs_to_ticket_and_officer()
    {
        $checkin = Checkin::factory()->create();

        $this->assertInstanceOf(Ticket::class, $checkin->ticket);
        $this->assertInstanceOf(User::class, $checkin->officer);
    }
}
