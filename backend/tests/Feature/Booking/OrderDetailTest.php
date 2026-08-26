<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows user to view their own order details with full breakdown', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $ticketType = TicketType::factory()->create([
        'name' => 'Tiket Dewasa',
        'price' => 20000,
    ]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_code' => 'ETK-20260830-DETAIL1',
        'visit_date' => '2026-08-30',
        'total_quantity' => 2,
        'total_amount' => 40000,
        'status' => 'PENDING',
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'ticket_type_id' => $ticketType->id,
        'quantity' => 2,
        'price' => 20000,
        'subtotal' => 40000,
    ]);

    $response = $this->getJson("/api/orders/{$order->order_code}");

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Detail booking berhasil diambil',
            'data' => [
                'order_code' => 'ETK-20260830-DETAIL1',
                'visit_date' => '2026-08-30',
                'total_quantity' => 2,
                'total_amount' => 40000,
                'status' => 'PENDING',
                'items' => [
                    [
                        'quantity' => 2,
                        'price' => 20000,
                        'subtotal' => 40000,
                        'ticket_type' => [
                            'name' => 'Tiket Dewasa',
                        ],
                    ],
                ],
            ],
        ]);
});

it('prevents user from accessing another user order details', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $orderA = Order::factory()->create([
        'user_id' => $userA->id,
        'order_code' => 'ETK-20260830-USERA99',
    ]);

    // Login as User B and attempt to view User A's order
    Sanctum::actingAs($userB, ['*']);

    $response = $this->getJson("/api/orders/{$orderA->order_code}");

    $response
        ->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Data booking tidak ditemukan',
        ]);
});

it('returns 404 for non-existent order code', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/orders/ETK-NONEXISTENT-9999');

    $response
        ->assertStatus(404)
        ->assertJson([
            'success' => false,
        ]);
});
