<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\TicketType;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

echo "Starting Integration Test...\n";

// 1. Get Wisatawan (existing)
$wisatawan = User::where('role', 'WISATAWAN')->first();
if (!$wisatawan) {
    echo "Error: Tidak ada user dengan role WISATAWAN di database.\n";
    exit(1);
}
echo "Using Wisatawan: {$wisatawan->email}\n";
$wisatawan->tokens()->delete(); // Clear old tokens just in case
$token = $wisatawan->createToken('test-token')->plainTextToken;
echo "Wisatawan Token: $token\n";

// 2. Ensure we have an active ticket type
$ticketType = TicketType::firstOrCreate(
    ['name' => 'Tiket Dewasa Test'],
    [
        'description' => 'Tiket untuk dewasa',
        'price' => 15000,
        'quota' => 100,
        'status' => 'ACTIVE'
    ]
);
echo "Ticket Type ID: {$ticketType->id}\n";

// 3. Place an order
$response = Http::withToken($token)->post('http://127.0.0.1:8000/api/orders', [
    'visit_date' => date('Y-m-d', strtotime('+1 day')),
    'customer_name' => 'Test Wisatawan',
    'customer_email' => 'testwisatawan@example.com',
    'customer_phone' => '081234567890',
    'items' => [
        [
            'ticket_type_id' => $ticketType->id,
            'quantity' => 2
        ]
    ]
]);

$orderData = $response->json();
if ($response->failed()) {
    echo "Failed to create order:\n";
    print_r($orderData);
    exit(1);
}

$orderCode = $orderData['data']['order_code'];
$paymentUrl = $orderData['data']['payment_url'];
echo "Order created successfully! Code: $orderCode\n";
echo "Payment URL (Xendit Invoice): $paymentUrl\n";

// 4. Simulate Xendit Webhook (Paid)
echo "\nSimulating Xendit Webhook (PAID)...\n";
$webhookResponse = Http::post('http://127.0.0.1:8000/api/payments/xendit/webhook', [
    'external_id' => $orderCode,
    'status' => 'PAID'
]);

if ($webhookResponse->failed()) {
    echo "Webhook failed:\n";
    print_r($webhookResponse->json());
    exit(1);
}
echo "Webhook success: " . $webhookResponse->body() . "\n";

// 5. Verify Order Status in DB
$order = Order::where('order_code', $orderCode)->first();
echo "Order Status after webhook: {$order->status}\n";
echo "QR Expires At: {$order->qr_expires_at}\n";

// 6. Test Scanner API as Petugas
$petugas = User::where('role', 'PETUGAS')->first();
if (!$petugas) {
    echo "Error: Tidak ada user dengan role PETUGAS di database.\n";
    exit(1);
}
echo "\nUsing Petugas: {$petugas->email}\n";
$petugas->tokens()->delete();
$petugasToken = $petugas->createToken('petugas-token')->plainTextToken;

echo "\nTesting Scanner API...\n";
$scanResponse = Http::withToken($petugasToken)->post('http://127.0.0.1:8000/api/scan', [
    'order_code' => $orderCode
]);

if ($scanResponse->successful()) {
    echo "Scan successful! Data:\n";
    print_r($scanResponse->json());
} else {
    echo "Scan failed!\n";
    print_r($scanResponse->json());
}

// 7. Test Double Scan (should fail)
echo "\nTesting Double Scan...\n";
$scanResponse2 = Http::withToken($petugasToken)->post('http://127.0.0.1:8000/api/scan', [
    'order_code' => $orderCode
]);

if ($scanResponse2->failed()) {
    echo "Double scan rejected (as expected): " . $scanResponse2->json()['message'] . "\n";
} else {
    echo "Error: Double scan was accepted!\n";
}

echo "\nIntegration Test Complete.\n";
