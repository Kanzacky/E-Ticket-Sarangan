<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ScanLog;
use App\Models\TicketType;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->ticketType = TicketType::factory()->create(['price' => 10000, 'quota' => 100, 'status' => 'ACTIVE']);
    $this->petugas = User::factory()->create(['role' => 'petugas']);
    $this->wisatawan = User::factory()->create(['role' => 'wisatawan']);
});

it('rejects scan when order not found', function () {
    Sanctum::actingAs($this->petugas, ['*']);
    $this->postJson('/api/scan', ['order_code' => 'ETK-NOTFOUND'])->assertStatus(404);
    expect((bool) ScanLog::where('order_code','ETK-NOTFOUND')->first()->is_valid)->toBeFalse();
});

it('rejects scan when order not PAID', function () {
    $order = Order::create([
        'user_id' => $this->wisatawan->id, 'order_code' => 'ETK-PENDING', 'visit_date' => now()->format('Y-m-d'),
        'customer_name' => 'Test', 'customer_email' => 'a@test.com', 'customer_phone' => '0811',
        'total_quantity' => 1, 'total_amount' => 10000, 'status' => 'PENDING',
    ]);
    OrderItem::create(['order_id' => $order->id, 'ticket_type_id' => $this->ticketType->id, 'quantity' => 1, 'price' => 10000, 'subtotal' => 10000]);

    Sanctum::actingAs($this->petugas, ['*']);
    $this->postJson('/api/scan', ['order_code' => 'ETK-PENDING'])->assertStatus(400)->assertJsonPath('success', false);
});

it('rejects scan when QR expired', function () {
    $order = Order::create([
        'user_id' => $this->wisatawan->id, 'order_code' => 'ETK-EXPIRED', 'visit_date' => now()->subDays(2)->format('Y-m-d'),
        'customer_name' => 'Test', 'customer_email' => 'a@test.com', 'customer_phone' => '0811',
        'total_quantity' => 1, 'total_amount' => 10000, 'status' => 'PAID',
        'qr_expires_at' => now()->subDay(),
    ]);
    OrderItem::create(['order_id' => $order->id, 'ticket_type_id' => $this->ticketType->id, 'quantity' => 1, 'price' => 10000, 'subtotal' => 10000]);

    Sanctum::actingAs($this->petugas, ['*']);
    $this->postJson('/api/scan', ['order_code' => 'ETK-EXPIRED'])->assertStatus(400);
});

it('rejects duplicate scan', function () {
    $order = Order::create([
        'user_id' => $this->wisatawan->id, 'order_code' => 'ETK-USED', 'visit_date' => now()->format('Y-m-d'),
        'customer_name' => 'Test', 'customer_email' => 'a@test.com', 'customer_phone' => '0811',
        'total_quantity' => 1, 'total_amount' => 10000, 'status' => 'PAID',
        'qr_expires_at' => now()->addDay(), 'scanned_at' => now(),
    ]);
    OrderItem::create(['order_id' => $order->id, 'ticket_type_id' => $this->ticketType->id, 'quantity' => 1, 'price' => 10000, 'subtotal' => 10000]);

    Sanctum::actingAs($this->petugas, ['*']);
    $this->postJson('/api/scan', ['order_code' => 'ETK-USED'])->assertStatus(400);
});

it('accepts valid PAID scan and marks COMPLETED', function () {
    $order = Order::create([
        'user_id' => $this->wisatawan->id, 'order_code' => 'ETK-VALID', 'visit_date' => now()->format('Y-m-d'),
        'customer_name' => 'Test', 'customer_email' => 'a@test.com', 'customer_phone' => '0811',
        'total_quantity' => 1, 'total_amount' => 10000, 'status' => 'PAID',
        'qr_expires_at' => now()->addDay(),
    ]);
    OrderItem::create(['order_id' => $order->id, 'ticket_type_id' => $this->ticketType->id, 'quantity' => 1, 'price' => 10000, 'subtotal' => 10000]);

    Sanctum::actingAs($this->petugas, ['*']);
    $this->postJson('/api/scan', ['order_code' => 'ETK-VALID'])->assertOk()->assertJsonPath('success', true);
    expect(Order::where('order_code','ETK-VALID')->first()->status)->toBe('COMPLETED');
    expect((bool) ScanLog::where('order_code','ETK-VALID')->first()->is_valid)->toBeTrue();
});

it('requires petugas role for scan', function () {
    Sanctum::actingAs($this->wisatawan, ['*']);
    $this->postJson('/api/scan', ['order_code' => 'ETK-VALID'])->assertForbidden();
});
