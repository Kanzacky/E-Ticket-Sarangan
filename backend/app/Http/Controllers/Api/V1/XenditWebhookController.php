<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class XenditWebhookController extends Controller
{
    /**
     * Handle incoming webhook from Xendit
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $expectedToken = env('XENDIT_CALLBACK_TOKEN');
        if (!empty($expectedToken)) {
            $callbackToken = $request->header('x-callback-token');
            if ($callbackToken !== $expectedToken) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }

        $externalId = $request->input('external_id');
        $status = $request->input('status');

        if (!$externalId || !$status) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // Accommodation booking (ACC-...)
        if (str_starts_with($externalId, 'ACC-')) {
            $booking = \App\Models\AccommodationBooking::where('booking_code', $externalId)->first();
            if (!$booking) {
                return response()->json(['message' => 'Booking not found, but webhook received'], 200);
            }
            if (in_array($status, ['PAID', 'SETTLED'])) {
                if ($booking->status === 'pending') {
                    $booking->status = 'confirmed';
                    $booking->save();
                    try {
                        \App\Services\NotificationService::send(
                            $booking->user_id,
                            'Pembayaran penginapan berhasil',
                            "Booking {$booking->booking_code} lunas. Penginapan siap digunakan.",
                            'accommodation_paid',
                            ['booking_code' => $booking->booking_code]
                        );
                    } catch (\Throwable $e) {}
                }
            } elseif (in_array($status, ['EXPIRED', 'FAILED'])) {
                if ($booking->status === 'pending') {
                    $booking->status = 'cancelled';
                    $booking->save();
                    // release kamar
                    try {
                        $acc = $booking->accommodation;
                        if ($acc) { $acc->available_rooms += $booking->rooms; $acc->save(); }
                    } catch (\Throwable $e) {}
                    try {
                        \App\Services\NotificationService::send(
                            $booking->user_id,
                            'Booking penginapan kadaluarsa',
                            "Booking {$booking->booking_code} dibatalkan karena pembayaran tidak selesai.",
                            'accommodation_expired',
                            ['booking_code' => $booking->booking_code]
                        );
                    } catch (\Throwable $e) {}
                }
            }
            return response()->json(['message' => 'Webhook accommodation handled'], 200);
        }

        $order = Order::where('order_code', $externalId)->first();

        if (!$order) {
            // Return 200 OK even if order is not found so Xendit doesn't keep retrying
            // and so the 'Test and save' button in Xendit dashboard succeeds
            return response()->json(['message' => 'Order not found, but webhook received'], 200);
        }

        // 2. If status is PAID or SETTLED, we mark the order as PAID and set QR expiration
        if (in_array($status, ['PAID', 'SETTLED'])) {
            if ($order->status !== 'PAID') {
                $order->status = 'PAID';
                
                // QR Expiration logic: Valid until 23:59:59 on the visit_date
                // E.g., if visit_date is '2026-08-30', expires at '2026-08-30 23:59:59'
                $expiresAt = Carbon::parse($order->visit_date)->endOfDay();
                $order->qr_expires_at = $expiresAt;
                
                $order->save();
                try { \App\Services\NotificationService::sendOrderPaid($order); } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning('Notif paid failed: '.$e->getMessage()); }
            }
        } elseif (in_array($status, ['EXPIRED', 'FAILED'])) {
            if ($order->status === 'PENDING') {
                $statusToSet = $status === 'EXPIRED' ? 'EXPIRED' : 'FAILED';
                $order->status = $statusToSet;
                $order->save();
                try { \App\Services\NotificationService::sendOrderExpired($order); } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning('Notif expired failed: '.$e->getMessage()); }
            }
        }

        return response()->json(['message' => 'Webhook received successfully'], 200);
    }
}
