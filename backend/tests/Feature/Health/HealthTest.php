<?php

it('returns healthy API status', function () {
    $response = $this->getJson('/api/health');

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'status',
                'app',
                'version',
            ],
        ]);
});

it('returns database health status', function () {
    $response = $this->getJson('/api/health/database');

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'database' => 'connected',
        ]);
});
