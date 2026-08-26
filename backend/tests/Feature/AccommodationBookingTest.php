<?php

use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('returns accommodation bookings for authenticated user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $accommodation = Accommodation::create([
        'name' => 'Villa Sarangan',
        'description' => 'Villa yang indah di Telaga Sarangan',
        'address' => 'Magetan, Jawa Timur',
        'phone' => '081234567890',
        'price_per_night' => 500000,
        'total_rooms' => 10,
        'available_rooms' => 10,
        'is_active' => true,
    ]);

    // Create a booking for this user
    $booking = AccommodationBooking::create([
        'booking_code' => 'ACC-TEST001',
        'user_id' => $user->id,
        'accommodation_id' => $accommodation->id,
        'check_in' => now()->addDays(5)->format('Y-m-d'),
        'check_out' => now()->addDays(8)->format('Y-m-d'),
        'rooms' => 2,
        'guests' => 4,
        'guest_name' => 'Budi Santoso',
        'guest_phone' => '081234567890',
        'status' => 'pending',
        'total_price' => 1500000,
    ]);

    $response = $this->getJson('/api/accommodation-bookings');

    $response->assertOk();

    $data = $response->json('data');
    expect($data)->toHaveCount(1)
        ->and($data[0]['booking_code'])->toBe('ACC-TEST001')
        ->and($data[0]['user_id'])->toBe($user->id)
        ->and($data[0]['accommodation_id'])->toBe($accommodation->id)
        ->and($data[0]['status'])->toBe('pending');
});

it('creates accommodation booking successfully', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $accommodation = Accommodation::create([
        'name' => 'Villa Sarangan',
        'description' => 'Villa yang indah di Telaga Sarangan',
        'address' => 'Magetan, Jawa Timur',
        'phone' => '081234567890',
        'price_per_night' => 500000,
        'total_rooms' => 10,
        'available_rooms' => 10,
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/accommodation-bookings', [
        'accommodation_id' => $accommodation->id,
        'check_in' => now()->addDays(5)->format('Y-m-d'),
        'check_out' => now()->addDays(8)->format('Y-m-d'),
        'rooms' => 2,
        'guests' => 4,
        'guest_name' => 'Budi Santoso',
        'guest_phone' => '081234567890',
    ]);

    $response->assertCreated();

    $data = $response->json('data');
    expect($data['booking_code'])->toStartWith('ACC-')
        ->and($data['user_id'])->toBe($user->id)
        ->and($data['accommodation_id'])->toBe($accommodation->id)
        ->and($data['status'])->toBe('pending')
        ->and($data['total_price'])->toBe(3000000);

    $this->assertDatabaseHas('accommodation_bookings', [
        'user_id' => $user->id,
        'accommodation_id' => $accommodation->id,
        'status' => 'pending',
        'total_price' => 3000000,
    ]);

    $this->assertDatabaseHas('accommodations', [
        'id' => $accommodation->id,
        'available_rooms' => 10,
    ]);
});

it('rejects booking when rooms exceed available', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $accommodation = Accommodation::create([
        'name' => 'Villa Sarangan',
        'description' => 'Villa yang indah di Telaga Sarangan',
        'address' => 'Magetan, Jawa Timur',
        'phone' => '081234567890',
        'price_per_night' => 500000,
        'total_rooms' => 3,
        'available_rooms' => 3,
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/accommodation-bookings', [
        'accommodation_id' => $accommodation->id,
        'check_in' => now()->addDays(5)->format('Y-m-d'),
        'check_out' => now()->addDays(8)->format('Y-m-d'),
        'rooms' => 5, // More than available
        'guests' => 4,
        'guest_name' => 'Budi Santoso',
        'guest_phone' => '081234567890',
    ]);

    $response->assertStatus(409)
        ->assertJson(['success' => false, 'message' => 'Jumlah kamar yang diminta melebihi ketersediaan (3 kamar tersedia).']);
});

it('rejects unauthenticated accommodation booking attempt', function () {
    $accommodation = Accommodation::create([
        'name' => 'Villa Sarangan',
        'description' => 'Villa yang indah di Telaga Sarangan',
        'address' => 'Magetan, Jawa Timur',
        'phone' => '081234567890',
        'price_per_night' => 500000,
        'total_rooms' => 10,
        'available_rooms' => 10,
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/accommodation-bookings', [
        'accommodation_id' => $accommodation->id,
        'check_in' => now()->addDays(5)->format('Y-m-d'),
        'check_out' => now()->addDays(8)->format('Y-m-d'),
        'rooms' => 1,
        'guests' => 2,
        'guest_name' => 'Budi Santoso',
        'guest_phone' => '081234567890',
    ]);

    $response->assertUnauthorized();
});

it('rejects booking with check_out before check_in', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $accommodation = Accommodation::create([
        'name' => 'Villa Sarangan',
        'description' => 'Villa yang indah di Telaga Sarangan',
        'address' => 'Magetan, Jawa Timur',
        'phone' => '081234567890',
        'price_per_night' => 500000,
        'total_rooms' => 10,
        'available_rooms' => 10,
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/accommodation-bookings', [
        'accommodation_id' => $accommodation->id,
        'check_in' => now()->addDays(8)->format('Y-m-d'),
        'check_out' => now()->addDays(5)->format('Y-m-d'), // Before check_in
        'rooms' => 1,
        'guests' => 2,
        'guest_name' => 'Budi Santoso',
        'guest_phone' => '081234567890',
    ]);

    $response->assertUnprocessable();
});

it('rejects booking with check_out on same day as check_in', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $accommodation = Accommodation::create([
        'name' => 'Villa Sarangan',
        'description' => 'Villa yang indah di Telaga Sarangan',
        'address' => 'Magetan, Jawa Timur',
        'phone' => '081234567890',
        'price_per_night' => 500000,
        'total_rooms' => 10,
        'available_rooms' => 10,
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/accommodation-bookings', [
        'accommodation_id' => $accommodation->id,
        'check_in' => now()->addDays(5)->format('Y-m-d'),
        'check_out' => now()->addDays(5)->format('Y-m-d'), // Same day
        'rooms' => 1,
        'guests' => 2,
        'guest_name' => 'Budi Santoso',
        'guest_phone' => '081234567890',
    ]);

    $response->assertUnprocessable();
});

it('rejects booking with past check_in date', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $accommodation = Accommodation::create([
        'name' => 'Villa Sarangan',
        'description' => 'Villa yang indah di Telaga Sarangan',
        'address' => 'Magetan, Jawa Timur',
        'phone' => '081234567890',
        'price_per_night' => 500000,
        'total_rooms' => 10,
        'available_rooms' => 10,
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/accommodation-bookings', [
        'accommodation_id' => $accommodation->id,
        'check_in' => now()->subDays(5)->format('Y-m-d'), // Past date
        'check_out' => now()->format('Y-m-d'),
        'rooms' => 1,
        'guests' => 2,
        'guest_name' => 'Budi Santoso',
        'guest_phone' => '081234567890',
    ]);

    $response->assertUnprocessable();
});

it('rejects unauthenticated get bookings attempt', function () {
    $accommodation = Accommodation::create([
        'name' => 'Villa Sarangan',
        'description' => 'Villa yang indah di Telaga Sarangan',
        'address' => 'Magetan, Jawa Timur',
        'phone' => '081234567890',
        'price_per_night' => 500000,
        'total_rooms' => 10,
        'available_rooms' => 10,
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/accommodation-bookings');

    $response->assertUnauthorized();
});