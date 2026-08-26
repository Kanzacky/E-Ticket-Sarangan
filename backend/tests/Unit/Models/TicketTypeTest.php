<?php

use App\Models\OrderItem;
use App\Models\TicketType;

it('casts price and quota correctly', function () {
    $ticketType = TicketType::factory()->create([
        'price' => '25000.00',
        'quota' => '150',
    ]);

    expect($ticketType->price)->toBe(25000.0)
        ->and($ticketType->quota)->toBe(150);
});

it('has many order items', function () {
    $ticketType = TicketType::factory()->create();
    OrderItem::factory()->count(2)->create(['ticket_type_id' => $ticketType->id]);

    expect($ticketType->orderItems)->toHaveCount(2);
});
