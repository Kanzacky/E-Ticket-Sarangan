<?php

use App\Models\User;

it('throttles login after 5 attempts', function () {
    $user = User::factory()->create(['email' => 'throttle@test.com', 'password' => bcrypt('password')]);

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/auth/login', ['email' => 'throttle@test.com', 'password' => 'wrong'])->assertStatus(401);
    }
    // 6th should be throttled 429
    $this->postJson('/api/auth/login', ['email' => 'throttle@test.com', 'password' => 'wrong'])->assertStatus(429);
});

it('throttles register', function () {
    for ($i = 0; $i < 10; $i++) {
        $this->postJson('/api/auth/register', [
            'name' => 'Test', 'email' => "t{$i}@test.com", 'password' => 'password123', 'password_confirmation' => 'password123'
        ]);
    }
    // 11th should be throttled
    $this->postJson('/api/auth/register', [
        'name' => 'Test', 'email' => 't10@test.com', 'password' => 'password123', 'password_confirmation' => 'password123'
    ])->assertStatus(429);
});
