<?php

use App\Models\User;

it('allows a user to register as wisatawan by default', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '081234567890',
    ]);

    $response
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data' => [
                'user' => [
                    'name' => 'Budi Santoso',
                    'email' => 'budi@example.com',
                    'role' => 'wisatawan',
                ],
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'budi@example.com',
        'role' => 'wisatawan',
    ]);
});

it('allows a user to login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'wisatawan@sarangan.test',
        'password' => bcrypt('password123'),
        'role' => 'wisatawan',
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'wisatawan@sarangan.test',
        'password' => 'password123',
    ]);

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Login berhasil',
        ])
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email', 'role'],
                'access_token',
            ],
        ]);
});

it('rejects login with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'user@test.com',
        'password' => bcrypt('correct-password'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'user@test.com',
        'password' => 'wrong-password',
    ]);

    $response
        ->assertUnauthorized()
        ->assertJson([
            'success' => false,
            'message' => 'Kredensial tidak valid',
        ]);
});

it('fetches authenticated user profile', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@test.com',
        'role' => 'wisatawan',
    ]);

    \Laravel\Sanctum\Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/auth/me');

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => 'John Doe',
                    'email' => 'john@test.com',
                ],
            ],
        ]);
});

it('allows authenticated user to logout and revokes token', function () {
    $user = User::factory()->create();

    \Laravel\Sanctum\Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/auth/logout');

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
});
