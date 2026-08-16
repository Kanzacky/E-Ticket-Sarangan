<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_returns_ok_with_full_shape(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'E-Ticket Sarangan API is running')
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.app', 'e-Ticket Sarangan')
            ->assertJsonPath('data.version', 'v1');
    }

    public function test_database_health_endpoint_reports_db_status(): void
    {
        $response = $this->getJson('/api/health/database');

        $response->assertJsonStructure([
            'success',
            'database',
        ]);
    }

    public function test_root_endpoint_returns_json(): void
    {
        $response = $this->getJson('/');

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('message', 'e-Ticket Sarangan API');
    }
}