<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'E-Ticket Sarangan API is running')
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.database', 'connected');
    }

    public function test_health_endpoint_has_consistent_json_shape(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'status',
                'app',
                'version',
                'database',
            ],
        ]);
    }
}
