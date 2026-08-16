<?php

namespace Tests\Feature;

use Tests\TestCase;

class CorsTest extends TestCase
{
    private const PROD_FRONTEND = 'https://e-ticket-sarangan-anx4.vercel.app';

    public function test_preflight_request_returns_204_with_cors_headers(): void
    {
        $response = $this->call('OPTIONS', '/api/health', [], [], [], [
            'HTTP_ORIGIN' => self::PROD_FRONTEND,
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'Content-Type, Authorization',
        ]);

        $response->assertStatus(204)
            ->assertHeader('Access-Control-Allow-Origin', self::PROD_FRONTEND)
            ->assertHeader('Access-Control-Allow-Methods');
    }

    public function test_get_health_returns_allow_origin_for_allowed_frontend(): void
    {
        $response = $this->getJson('/api/health', ['Origin' => self::PROD_FRONTEND]);

        $response->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', self::PROD_FRONTEND);
    }

    public function test_disallowed_origin_does_not_receive_allow_origin_header(): void
    {
        $response = $this->getJson('/api/health', ['Origin' => 'https://evil.example.com']);

        $response->assertOk()
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }
}