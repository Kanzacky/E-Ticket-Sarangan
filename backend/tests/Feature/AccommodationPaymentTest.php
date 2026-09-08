<?php

use App\Models\Accommodation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('creates accommodation booking with payment_url when Xendit mocked', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);
    $acc = Accommodation::factory()->create(['price_per_night' => 200000, 'total_rooms' => 5, 'available_rooms' => 5, 'is_active' => true]);

    $res = $this->postJson('/api/accommodation-bookings', [
        'accommodation_id' => $acc->id,
        'check_in' => now()->addDays(2)->format('Y-m-d'),
        'check_out' => now()->addDays(3)->format('Y-m-d'),
        'rooms' => 1,
        'guests' => 2,
        'guest_name' => 'Test',
        'guest_phone' => '0811',
    ])->assertCreated();

    expect($res->json('data.booking_code'))->toStartWith('ACC-');
    expect($res->json('data.total_price'))->toBe(200000);
    // available_rooms should decrement
    expect(Accommodation::find($acc->id)->available_rooms)->toBe(4);
});

it('webhook confirms accommodation booking', function () {
    $user = User::factory()->create();
    $acc = Accommodation::factory()->create(['price_per_night' => 100000, 'total_rooms' => 5, 'available_rooms' => 5]);
    $booking = \App\Models\AccommodationBooking::create([
        'booking_code' => 'ACC-WEBHOOK1',
        'user_id' => $user->id,
        'accommodation_id' => $acc->id,
        'check_in' => now()->addDays(2)->format('Y-m-d'),
        'check_out' => now()->addDays(3)->format('Y-m-d'),
        'rooms' => 1,
        'guests' => 2,
        'total_price' => 100000,
        'guest_name' => 'Test',
        'guest_phone' => '0811',
        'status' => 'pending',
    ]);
    $acc->decrement('available_rooms', 1);

    $this->postJson('/api/payments/xendit/webhook', ['external_id' => 'ACC-WEBHOOK1', 'status' => 'PAID'])->assertOk();
    expect(\App\Models\AccommodationBooking::where('booking_code','ACC-WEBHOOK1')->first()->status)->toBe('confirmed');
});

it('webhook expires accommodation and releases rooms', function () {
    $user = User::factory()->create();
    $acc = Accommodation::factory()->create(['price_per_night' => 100000, 'total_rooms' => 5, 'available_rooms' => 4]);
    $booking = \App\Models\AccommodationBooking::create([
        'booking_code' => 'ACC-EXPIRE1',
        'user_id' => $user->id,
        'accommodation_id' => $acc->id,
        'check_in' => now()->addDays(2)->format('Y-m-d'),
        'check_out' => now()->addDays(3)->format('Y-m-d'),
        'rooms' => 1,
        'guests' => 1,
        'total_price' => 100000,
        'guest_name' => 'Test',
        'guest_phone' => '0811',
        'status' => 'pending',
    ]);

    $this->postJson('/api/payments/xendit/webhook', ['external_id' => 'ACC-EXPIRE1', 'status' => 'EXPIRED'])->assertOk();
    expect(\App\Models\AccommodationBooking::where('booking_code','ACC-EXPIRE1')->first()->status)->toBe('cancelled');
    expect(Accommodation::find($acc->id)->available_rooms)->toBe(5);
});
