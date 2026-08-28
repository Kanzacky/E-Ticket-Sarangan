<?php

use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

it('enforces quota via lockForUpdate - rejects when exceeding remaining', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $ticketType = TicketType::factory()->create(['quota' => 2, 'price' => 10000, 'status' => 'ACTIVE']);
    $visitDate = now()->addDays(2)->format('Y-m-d');

    // user1 books 2 (fills quota)
    Sanctum::actingAs($user1, ['*']);
    $this->postJson('/api/orders', [
        'visit_date' => $visitDate,
        'customer_name' => 'User1',
        'customer_email' => 'u1@test.com',
        'customer_phone' => '081234567891',
        'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 2]],
    ])->assertCreated();

    // user2 tries to book 1 more on same date -> should 409
    Sanctum::actingAs($user2, ['*']);
    $this->postJson('/api/orders', [
        'visit_date' => $visitDate,
        'customer_name' => 'User2',
        'customer_email' => 'u2@test.com',
        'customer_phone' => '081234567892',
        'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 1]],
    ])->assertStatus(409);

    // but different date should succeed
    $this->postJson('/api/orders', [
        'visit_date' => now()->addDays(3)->format('Y-m-d'),
        'customer_name' => 'User2',
        'customer_email' => 'u2@test.com',
        'customer_phone' => '081234567893',
        'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 1]],
    ])->assertCreated();
});

it('allows PENDING and PAID both count toward quota', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);
    $ticketType = TicketType::factory()->create(['quota' => 1, 'price' => 10000, 'status' => 'ACTIVE']);
    $visitDate = now()->addDays(2)->format('Y-m-d');

    // create PAID order directly
    $paidUser = User::factory()->create();
    \App\Models\Order::create([
        'user_id' => $paidUser->id,
        'order_code' => 'ETK-QUOTA-PAID',
        'visit_date' => $visitDate,
        'customer_name' => 'Paid',
        'customer_email' => 'paid@test.com',
        'customer_phone' => '0811',
        'total_quantity' => 1,
        'total_amount' => 10000,
        'status' => 'PAID',
    ]);
    \App\Models\OrderItem::create(['order_id' => \App\Models\Order::where('order_code','ETK-QUOTA-PAID')->first()->id, 'ticket_type_id' => $ticketType->id, 'quantity' => 1, 'price' => 10000, 'subtotal' => 10000]);

    $this->postJson('/api/orders', [
        'visit_date' => $visitDate,
        'customer_name' => 'X',
        'customer_email' => 'x@test.com',
        'customer_phone' => '081234567890',
        'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 1]],
    ])->assertStatus(409);
});
