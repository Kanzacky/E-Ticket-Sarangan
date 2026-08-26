<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('returns only orders belonging to the authenticated user', function () {
    $userA = User::factory()->create(['name' => 'User A']);
    $userB = User::factory()->create(['name' => 'User B']);

    $ticketType = TicketType::factory()->create(['price' => 20000]);

    // Orders for User A
    $orderA1 = Order::factory()->create([
        'user_id' => $userA->id,
        'order_code' => 'ETK-20260830-AAAA01',
    ]);
    OrderItem::factory()->create([
        'order_id' => $orderA1->id,
        'ticket_type_id' => $ticketType->id,
        'quantity' => 2,
    ]);

    $orderA2 = Order::factory()->create([
        'user_id' => $userA->id,
        'order_code' => 'ETK-20260830-AAAA02',
    ]);
    OrderItem::factory()->create([
        'order_id' => $orderA2->id,
        'ticket_type_id' => $ticketType->id,
        'quantity' => 1,
    ]);

    // Order for User B
    $orderB1 = Order::factory()->create([
        'user_id' => $userB->id,
        'order_code' => 'ETK-20260830-BBBB01',
    ]);
    OrderItem::factory()->create([
        'order_id' => $orderB1->id,
        'ticket_type_id' => $ticketType->id,
        'quantity' => 3,
    ]);

    // Request as User A
    Sanctum::actingAs($userA, ['*']);

    $response = $this->getJson('/api/orders');

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Riwayat booking berhasil diambil',
        ])
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['order_code' => 'ETK-20260830-AAAA01'])
        ->assertJsonFragment(['order_code' => 'ETK-20260830-AAAA02'])
        ->assertJsonMissing(['order_code' => 'ETK-20260830-BBBB01']);
});

it('returns empty array when user has no orders', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/orders');

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [],
        ]);
});

it('rejects unauthenticated access to order list', function () {
    $response = $this->getJson('/api/orders');

    $response->assertUnauthorized();
});
