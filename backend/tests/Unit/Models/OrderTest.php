<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

it('belongs to a user', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    expect($order->user)->toBeInstanceOf(User::class)
        ->and($order->user->id)->toBe($user->id);
});

it('has many order items', function () {
    $order = Order::factory()->create();
    OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

    expect($order->items)->toHaveCount(3);
});
