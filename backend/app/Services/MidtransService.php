<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Exception;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Generate Snap Token/URL for a Booking.
     *
     * @param Booking $booking
     * @return array Array containing token and redirect_url
     * @throws Exception
     */
    public function createSnapToken(Booking $booking): array
    {
        // Default to a fallback if not authenticated (e.g. CLI/Seeder), though usually called within HTTP context
        $customerName = $booking->user ? $booking->user->name : 'Wisatawan Sarangan';
        $customerEmail = $booking->user ? $booking->user->email : 'guest@example.com';
        $customerPhone = $booking->user ? $booking->user->phone : '';

        $params = [
            'transaction_details' => [
                'order_id' => $booking->booking_code . '-' . time(), // append timestamp to prevent duplicate order_id in sandbox testing
                'gross_amount' => (int) $booking->total_amount,
            ],
            'customer_details' => [
                'first_name' => $customerName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
            ],
            'item_details' => [
                [
                    'id' => 'TICKET',
                    'price' => (int) $booking->total_amount,
                    'quantity' => 1,
                    'name' => 'Tiket Wisata Sarangan (' . $booking->booking_code . ')',
                ]
            ]
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            
            // Generate standard redirect URL manually because midtrans-php 
            // sometimes only returns token for simple getSnapToken calls
            $redirectUrl = config('midtrans.is_production')
                ? "https://app.midtrans.com/snap/v2/vtweb/" . $snapToken
                : "https://app.sandbox.midtrans.com/snap/v2/vtweb/" . $snapToken;

            return [
                'token' => $snapToken,
                'redirect_url' => $redirectUrl,
            ];
        } catch (Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
