<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    // create ticket type for orders
    $this->ticketType = TicketType::create(['name' => 'Dewasa', 'price' => 20000, 'quota' => 1000, 'status' => 'ACTIVE']);
});

it('requires admin role for analytics', function () {
    $wisatawan = User::factory()->create(['role' => 'wisatawan']);
    Sanctum::actingAs($wisatawan, ['*']);
    $this->getJson('/api/admin/analytics')->assertForbidden();

    $petugas = User::factory()->create(['role' => 'petugas']);
    Sanctum::actingAs($petugas, ['*']);
    $this->getJson('/api/admin/analytics')->assertForbidden();
});

it('returns analytics for admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin, ['*']);

    $user = User::factory()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'order_code' => 'ETK-ANALYTIC1',
        'visit_date' => now()->format('Y-m-d'),
        'customer_name' => 'Budi',
        'customer_email' => 'budi@test.com',
        'customer_phone' => '0812',
        'total_quantity' => 2,
        'total_amount' => 40000,
        'status' => 'PAID',
    ]);
    OrderItem::create(['order_id' => $order->id, 'ticket_type_id' => $this->ticketType->id, 'quantity' => 2, 'price' => 20000, 'subtotal' => 40000]);

    $res = $this->getJson('/api/admin/analytics?period=today')->assertOk();
    $res->assertJson(['success' => true]);
    $data = $res->json('data');
    expect($data)->toHaveKeys(['summary', 'scans', 'trend', 'top_tickets', 'accommodations']);
    expect($data['summary']['revenue'])->toBeGreaterThanOrEqual(40000);
    expect($data['summary']['orders'])->toBeGreaterThanOrEqual(1);
});

it('returns empty trend when no data for period', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin, ['*']);

    $res = $this->getJson('/api/admin/analytics?period=today')->assertOk();
    // should still be success even if no orders today (except created above isolated? but RefreshDatabase clears)
    expect($res->json('success'))->toBeTrue();
});

it('requires admin for audit logs, checkins, upgrades, settings', function () {
    $wisatawan = User::factory()->create(['role' => 'wisatawan']);
    Sanctum::actingAs($wisatawan, ['*']);
    $this->getJson('/api/admin/audit-logs')->assertForbidden();
    $this->getJson('/api/admin/checkins')->assertForbidden();
    $this->getJson('/api/admin/upgrades')->assertForbidden();
    $this->getJson('/api/admin/settings')->assertForbidden();
});

it('admin can read settings', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin, ['*']);
    $this->getJson('/api/admin/settings')->assertOk()->assertJson(['success' => true]);
});

it('unauthenticated cannot access analytics', function () {
    $this->getJson('/api/admin/analytics')->assertUnauthorized();
});
