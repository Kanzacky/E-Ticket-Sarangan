<?php

namespace Tests\Unit\Models;

use App\Models\TicketCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_category_can_be_created()
    {
        $category = TicketCategory::factory()->create([
            'name' => 'Dewasa',
            'price' => 20000,
        ]);

        $this->assertDatabaseHas('ticket_categories', [
            'name' => 'Dewasa',
            'price' => 20000,
        ]);
    }
}
