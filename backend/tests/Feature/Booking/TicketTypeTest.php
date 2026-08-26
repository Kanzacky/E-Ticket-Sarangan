<?php

use App\Models\TicketType;

it('returns available active ticket types', function () {
    $activeTicket = TicketType::factory()->create([
        'name' => 'Tiket Dewasa',
        'description' => 'Tiket untuk dewasa',
        'price' => 20000,
        'quota' => 500,
        'status' => 'ACTIVE',
    ]);

    $inactiveTicket = TicketType::factory()->create([
        'name' => 'Tiket Promo Lama',
        'description' => 'Tiket promo expired',
        'price' => 15000,
        'quota' => 50,
        'status' => 'INACTIVE',
    ]);

    $response = $this->getJson('/api/ticket-types');

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonFragment([
            'name' => 'Tiket Dewasa',
            'price' => 20000,
            'quota' => 500,
            'status' => 'ACTIVE',
        ])
        ->assertJsonMissing([
            'name' => 'Tiket Promo Lama',
        ]);
});

it('returns empty array when no active ticket types exist', function () {
    $response = $this->getJson('/api/ticket-types');

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [],
        ]);
});
