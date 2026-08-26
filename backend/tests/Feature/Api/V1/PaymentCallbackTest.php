<?php

namespace Tests\Feature\Api\V1;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PaymentCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('midtrans.server_key', 'dummy_server_key');
    }

    public function test_midtrans_webhook_handles_settlement()
    {
        $booking = Booking::factory()->create([
            'booking_code' => 'TEST001',
            'status' => 'pending'
        ]);

        $orderId = 'TEST001-' . time();
        $statusCode = '200';
        $grossAmount = '50000.00';
        $transactionStatus = 'settlement';

        // Calculate expected signature
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . 'dummy_server_key');

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => $transactionStatus,
            'signature_key' => $signature,
            'payment_type' => 'gopay',
            'transaction_id' => 'dummy_tx_id_123'
        ];

        $response = $this->postJson('/api/payments/midtrans/notification', $payload);

        $response->assertStatus(200);

        // Verify database records
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'paid'
        ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'amount' => 50000.00,
            'status' => 'success',
            'payment_method' => 'gopay',
            'transaction_id' => 'dummy_tx_id_123'
        ]);
    }

    public function test_midtrans_webhook_handles_expire()
    {
        $booking = Booking::factory()->create([
            'booking_code' => 'TEST002',
            'status' => 'pending'
        ]);

        $orderId = 'TEST002-' . time();
        $statusCode = '200';
        $grossAmount = '75000.00';
        $transactionStatus = 'expire';

        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . 'dummy_server_key');

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => $transactionStatus,
            'signature_key' => $signature,
        ];

        $response = $this->postJson('/api/payments/midtrans/notification', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled' // Based on our logic, expire -> cancelled
        ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'status' => 'expired'
        ]);
    }

    public function test_midtrans_webhook_rejects_invalid_signature()
    {
        $payload = [
            'order_id' => 'TEST003-12345',
            'status_code' => '200',
            'gross_amount' => '10000.00',
            'transaction_status' => 'settlement',
            'signature_key' => 'invalid_signature_here',
        ];

        $response = $this->postJson('/api/payments/midtrans/notification', $payload);

        $response->assertStatus(403);
    }
}
