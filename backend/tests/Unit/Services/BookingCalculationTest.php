<?php

it('correctly calculates subtotals and grand total for tickets', function () {
    $items = [
        ['name' => 'Tiket Dewasa', 'price' => 20000, 'quantity' => 2],
        ['name' => 'Tiket Anak', 'price' => 10000, 'quantity' => 1],
        ['name' => 'Tiket Mancanegara', 'price' => 50000, 'quantity' => 1],
    ];

    $totalQuantity = 0;
    $totalAmount = 0;
    $calculatedItems = [];

    foreach ($items as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $totalQuantity += $item['quantity'];
        $totalAmount += $subtotal;

        $calculatedItems[] = [
            'name' => $item['name'],
            'subtotal' => $subtotal,
        ];
    }

    expect($totalQuantity)->toBe(4)
        ->and($totalAmount)->toBe(100000)
        ->and($calculatedItems[0]['subtotal'])->toBe(40000)
        ->and($calculatedItems[1]['subtotal'])->toBe(10000)
        ->and($calculatedItems[2]['subtotal'])->toBe(50000);
});
