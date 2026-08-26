<?php

namespace Tests\Unit\Models;

use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_belongs_to_booking()
    {
        $payment = Payment::factory()->create();

        $this->assertInstanceOf(Booking::class, $payment->booking);
    }
}
