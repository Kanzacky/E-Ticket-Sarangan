<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\TicketType;

class OrderApiTest extends TestCase {
    use RefreshDatabase;
    public function test_booking() {
        $user = User::factory()->create();
        $ticket = TicketType::factory()->create(['status' => 'ACTIVE', 'quota' => 10, 'price' => 10000]);
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/orders', [
            'visit_date' => date('Y-m-d', strtotime('+1 day')),
            'customer_name' => 'Test',
            'customer_email' => 'test@test.com',
            'customer_phone' => '0812345678',
            'items' => [
                ['ticket_type_id' => $ticket->id, 'quantity' => 1]
            ]
        ]);
        $response->dump();
    }
}
