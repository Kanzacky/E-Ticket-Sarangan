<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_public_health_endpoint_returns_ok_without_database(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'API e-Ticket Sarangan aktif');
    }

    public function test_database_health_endpoint_reports_db_status(): void
    {
        $response = $this->getJson('/api/health/database');

        $response->assertJsonStructure([
            'success',
            'database',
        ]);
    }
}
