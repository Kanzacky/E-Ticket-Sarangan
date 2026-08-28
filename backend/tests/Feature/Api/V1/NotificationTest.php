<?php

use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('requires authentication for notifications', function () {
    $this->getJson('/api/notifications')->assertUnauthorized();
    $this->getJson('/api/notifications/unread-count')->assertUnauthorized();
});

it('returns empty notifications for new user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $this->getJson('/api/notifications')->assertOk()->assertJson(['success' => true]);
    $this->getJson('/api/notifications/unread-count')->assertOk()->assertJsonPath('data.unread_count', 0);
});

it('creates and lists notifications via service', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    \App\Services\NotificationService::send($user->id, 'Test Title', 'Test Message', 'test_type', ['foo' => 'bar']);

    $res = $this->getJson('/api/notifications')->assertOk();
    $data = $res->json('data');
    expect($data)->toHaveCount(1)
        ->and($data[0]['title'])->toBe('Test Title')
        ->and($data[0]['type'])->toBe('test_type');

    $this->getJson('/api/notifications/unread-count')->assertOk()->assertJsonPath('data.unread_count', 1);
});

it('marks single notification as read', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $notif = \App\Services\NotificationService::send($user->id, 'A', 'B', 'order_pending');

    $this->patchJson("/api/notifications/{$notif->id}/read")->assertOk();
    $this->assertDatabaseHas('notifications', ['id' => $notif->id]);
    expect(Notification::find($notif->id)->read_at)->not->toBeNull();
    $this->getJson('/api/notifications/unread-count')->assertJsonPath('data.unread_count', 0);
});

it('marks all notifications as read', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    \App\Services\NotificationService::send($user->id, 'A', 'B');
    \App\Services\NotificationService::send($user->id, 'C', 'D');

    $this->patchJson('/api/notifications/read-all')->assertOk();
    $this->getJson('/api/notifications/unread-count')->assertJsonPath('data.unread_count', 0);
});

it('deletes own notification', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $notif = \App\Services\NotificationService::send($user->id, 'Del', 'Msg');
    $this->deleteJson("/api/notifications/{$notif->id}")->assertOk();
    $this->assertDatabaseMissing('notifications', ['id' => $notif->id]);
});

it('prevents deleting other user notification', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $notif = \App\Services\NotificationService::send($owner->id, 'Private', 'Msg');

    Sanctum::actingAs($other, ['*']);
    $this->deleteJson("/api/notifications/{$notif->id}")->assertNotFound();
});

it('sends notification on order webhook paid', function () {
    $user = User::factory()->create();
    $ticketType = \App\Models\TicketType::create(['name' => 'Test', 'price' => 10000, 'quota' => 100, 'status' => 'ACTIVE']);
    $order = \App\Models\Order::create([
        'user_id' => $user->id,
        'order_code' => 'ETK-TEST123',
        'visit_date' => now()->addDays(2)->format('Y-m-d'),
        'customer_name' => 'Test',
        'customer_email' => 'test@test.com',
        'customer_phone' => '0812',
        'total_quantity' => 1,
        'total_amount' => 10000,
        'status' => 'PENDING',
    ]);

    // simulate webhook
    $this->postJson('/api/payments/xendit/webhook', ['external_id' => 'ETK-TEST123', 'status' => 'PAID'])->assertOk();
    expect(Order::where('order_code', 'ETK-TEST123')->first()->status)->toBe('PAID');
    $this->assertDatabaseHas('notifications', ['user_id' => $user->id, 'type' => 'order_paid']);
});
