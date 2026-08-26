<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('rejects unauthenticated booking attempt', function () {
    $response = $this->postJson('/api/orders', [
        'visit_date' => now()->addDay()->format('Y-m-d'),
        'customer_name' => 'John Doe',
        'customer_email' => 'john@example.com',
        'customer_phone' => '08123456789',
        'items' => [
            ['ticket_type_id' => 1, 'quantity' => 2],
        ],
    ]);

    $response->assertUnauthorized();
});

it('allows authenticated user to create a booking successfully', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $ticketType = TicketType::factory()->create([
        'name' => 'Tiket Dewasa',
        'price' => 20000,
        'quota' => 100,
        'status' => 'ACTIVE',
    ]);

    $visitDate = now()->addDays(2)->format('Y-m-d');

    $response = $this->postJson('/api/orders', [
        'visit_date' => $visitDate,
        'customer_name' => 'Budi Santoso',
        'customer_email' => 'budi@example.com',
        'customer_phone' => '081234567890',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 2,
            ],
        ],
    ]);

    $response
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Booking berhasil dibuat',
            'data' => [
                'visit_date' => $visitDate,
                'total_quantity' => 2,
                'total_amount' => 40000,
                'status' => 'PENDING',
                'customer_name' => 'Budi Santoso',
            ],
        ]);

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'status' => 'PENDING',
        'total_quantity' => 2,
        'total_amount' => 40000,
    ]);

    $this->assertDatabaseHas('order_items', [
        'ticket_type_id' => $ticketType->id,
        'quantity' => 2,
        'price' => 20000,
        'subtotal' => 40000,
    ]);
});

it('rejects past visit date', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $ticketType = TicketType::factory()->create(['status' => 'ACTIVE']);

    $response = $this->postJson('/api/orders', [
        'visit_date' => now()->subDay()->format('Y-m-d'),
        'customer_name' => 'Budi Santoso',
        'customer_email' => 'budi@example.com',
        'customer_phone' => '081234567890',
        'items' => [
            ['ticket_type_id' => $ticketType->id, 'quantity' => 1],
        ],
    ]);

    $response->assertUnprocessable();
});

it('rejects empty or missing items array', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/orders', [
        'visit_date' => now()->addDay()->format('Y-m-d'),
        'customer_name' => 'Budi Santoso',
        'customer_email' => 'budi@example.com',
        'customer_phone' => '081234567890',
        'items' => [],
    ]);

    $response->assertUnprocessable();
});

it('rejects invalid ticket_type_id', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/orders', [
        'visit_date' => now()->addDay()->format('Y-m-d'),
        'customer_name' => 'Budi Santoso',
        'customer_email' => 'budi@example.com',
        'customer_phone' => '081234567890',
        'items' => [
            ['ticket_type_id' => 9999, 'quantity' => 1],
        ],
    ]);

    $response->assertUnprocessable();
});

it('rejects booking with inactive ticket type', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $inactiveTicket = TicketType::factory()->inactive()->create([
        'name' => 'Tiket Spesial Nonaktif',
        'price' => 10000,
    ]);

    $response = $this->postJson('/api/orders', [
        'visit_date' => now()->addDay()->format('Y-m-d'),
        'customer_name' => 'Budi Santoso',
        'customer_email' => 'budi@example.com',
        'customer_phone' => '081234567890',
        'items' => [
            ['ticket_type_id' => $inactiveTicket->id, 'quantity' => 1],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

it('does not trust client supplied price and calculates correctly from database', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $ticketType = TicketType::factory()->create([
        'name' => 'Tiket Dewasa',
        'price' => 20000,
        'quota' => 100,
        'status' => 'ACTIVE',
    ]);

    $response = $this->postJson('/api/orders', [
        'visit_date' => now()->addDay()->format('Y-m-d'),
        'customer_name' => 'Budi Santoso',
        'customer_email' => 'budi@example.com',
        'customer_phone' => '081234567890',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 2,
                'price' => 1, // Tampered client price!
                'subtotal' => 2, // Tampered client subtotal!
            ],
        ],
    ]);

    $response->assertCreated();

    // The backend MUST have calculated 2 * 20.000 = 40.000
    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'total_amount' => 40000,
    ]);

    $this->assertDatabaseHas('order_items', [
        'price' => 20000,
        'subtotal' => 40000,
    ]);
});

it('rejects booking when quantity exceeds quota', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $ticketType = TicketType::factory()->create([
        'name' => 'Tiket Terbatas',
        'price' => 20000,
        'quota' => 3,
        'status' => 'ACTIVE',
    ]);

    $response = $this->postJson('/api/orders', [
        'visit_date' => now()->addDay()->format('Y-m-d'),
        'customer_name' => 'Budi Santoso',
        'customer_email' => 'budi@example.com',
        'customer_phone' => '081234567890',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 4, // Exceeds quota of 3
            ],
        ],
    ]);

    $response->assertStatus(409)
        ->assertJson([
            'success' => false,
        ]);
});

it('calculates multi-item totals accurately', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $dewasa = TicketType::factory()->create([
        'name' => 'Tiket Dewasa',
        'price' => 20000,
        'quota' => 100,
        'status' => 'ACTIVE',
    ]);

    $anak = TicketType::factory()->create([
        'name' => 'Tiket Anak',
        'price' => 10000,
        'quota' => 100,
        'status' => 'ACTIVE',
    ]);

    $response = $this->postJson('/api/orders', [
        'visit_date' => now()->addDay()->format('Y-m-d'),
        'customer_name' => 'Budi Santoso',
        'customer_email' => 'budi@example.com',
        'customer_phone' => '081234567890',
        'items' => [
            ['ticket_type_id' => $dewasa->id, 'quantity' => 2], // 40.000
            ['ticket_type_id' => $anak->id, 'quantity' => 1],   // 10.000
        ],
    ]);

    $response->assertCreated()
        ->assertJson([
            'data' => [
                'total_quantity' => 3,
                'total_amount' => 50000,
            ],
        ]);
});

it('generates unique order codes for concurrent bookings', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $ticketType = TicketType::factory()->create([
        'price' => 20000,
        'quota' => 100,
        'status' => 'ACTIVE',
    ]);

    $visitDate = now()->addDays(3)->format('Y-m-d');

    $res1 = $this->postJson('/api/orders', [
        'visit_date' => $visitDate,
        'customer_name' => 'User 1',
        'customer_email' => 'u1@test.com',
        'customer_phone' => '0811111111',
        'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 1]],
    ]);

    $res2 = $this->postJson('/api/orders', [
        'visit_date' => $visitDate,
        'customer_name' => 'User 2',
        'customer_email' => 'u2@test.com',
        'customer_phone' => '0822222222',
        'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 1]],
    ]);

    $code1 = $res1->json('data.order_code');
    $code2 = $res2->json('data.order_code');

    expect($code1)->not->toBeEmpty()
        ->and($code2)->not->toBeEmpty()
        ->and($code1)->not->toEqual($code2)
        ->and($code1)->toStartWith('ETK-');
});
