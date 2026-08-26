<?php

namespace Tests\Feature\Api\V1;

use App\Models\Order;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        TicketType::create([
            'name' => 'Tiket Dewasa',
            'description' => 'Tiket dewasa',
            'price' => 20000,
            'quota' => 10,
            'status' => 'ACTIVE',
        ]);

        TicketType::create([
            'name' => 'Tiket Anak',
            'description' => 'Tiket anak',
            'price' => 10000,
            'quota' => 5,
            'status' => 'ACTIVE',
        ]);

        TicketType::create([
            'name' => 'Tiket Non-Aktif',
            'description' => 'Tiket promo lama',
            'price' => 15000,
            'quota' => 10,
            'status' => 'INACTIVE',
        ]);
    }

    public function test_can_get_active_ticket_types()
    {
        $response = $this->getJson('/api/ticket-types');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(2, 'data'); // Only ACTIVE types
    }

    public function test_unauthenticated_user_cannot_create_order()
    {
        $response = $this->postJson('/api/orders', [
            'visit_date' => date('Y-m-d', strtotime('+1 day')),
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '08123456789',
            'items' => [
                ['ticket_type_id' => 1, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_cannot_create_order_with_past_visit_date()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/orders', [
            'visit_date' => '2020-01-01',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '08123456789',
            'items' => [
                ['ticket_type_id' => 1, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_create_order_successfully()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $visitDate = date('Y-m-d', strtotime('+2 days'));

        $response = $this->postJson('/api/orders', [
            'visit_date' => $visitDate,
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '08123456789',
            'items' => [
                ['ticket_type_id' => 1, 'quantity' => 2], // 2 x 20.000 = 40.000
                ['ticket_type_id' => 2, 'quantity' => 1], // 1 x 10.000 = 10.000
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Booking berhasil dibuat',
                'data' => [
                    'total_quantity' => 3,
                    'total_amount' => 50000,
                    'status' => 'PENDING',
                    'customer_name' => 'Budi Santoso',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_quantity' => 3,
            'total_amount' => 50000,
            'status' => 'PENDING',
        ]);

        $this->assertDatabaseCount('order_items', 2);
    }

    public function test_order_fails_when_quantity_exceeds_quota()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $visitDate = date('Y-m-d', strtotime('+3 days'));

        // Tiket Anak has quota 5. Requesting 6 must fail with 409
        $response = $this->postJson('/api/orders', [
            'visit_date' => $visitDate,
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '08123456789',
            'items' => [
                ['ticket_type_id' => 2, 'quantity' => 6],
            ],
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_user_can_view_own_orders_only()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $orderA = Order::create([
            'user_id' => $userA->id,
            'order_code' => 'ETK-20260830-USERA1',
            'visit_date' => '2026-08-30',
            'customer_name' => 'User A',
            'customer_email' => 'a@test.com',
            'customer_phone' => '081111111',
            'total_quantity' => 2,
            'total_amount' => 40000,
            'status' => 'PENDING',
        ]);

        $orderB = Order::create([
            'user_id' => $userB->id,
            'order_code' => 'ETK-20260830-USERB1',
            'visit_date' => '2026-08-30',
            'customer_name' => 'User B',
            'customer_email' => 'b@test.com',
            'customer_phone' => '082222222',
            'total_quantity' => 1,
            'total_amount' => 20000,
            'status' => 'PENDING',
        ]);

        // Login as User A
        Sanctum::actingAs($userA, ['*']);

        // Check index
        $indexResponse = $this->getJson('/api/orders');
        $indexResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['order_code' => 'ETK-20260830-USERA1']);

        // User A cannot view User B's order by code
        $showResponse = $this->getJson("/api/orders/{$orderB->order_code}");
        $showResponse->assertStatus(404);

        // User A can view own order
        $ownResponse = $this->getJson("/api/orders/{$orderA->order_code}");
        $ownResponse->assertStatus(200)
            ->assertJsonFragment(['order_code' => 'ETK-20260830-USERA1']);
    }
}
