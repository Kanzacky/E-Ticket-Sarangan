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
        // 1. In a real-world scenario, you should verify the Xendit Callback Token here:
        // $callbackToken = $request->header('x-callback-token');
        // if ($callbackToken !== env('XENDIT_CALLBACK_TOKEN')) {
        //     return response()->json(['message' => 'Unauthorized'], 401);
        // }

        $externalId = $request->input('external_id');
        $status = $request->input('status');

        if (!$externalId || !$status) {
            return response()->json(['message' => 'Invalid payload'], 400);
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
        } elseif ($status === 'EXPIRED') {
            if ($order->status === 'PENDING') {
                $order->status = 'EXPIRED';
                $order->save();
                try { \App\Services\NotificationService::sendOrderExpired($order); } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning('Notif expired failed: '.$e->getMessage()); }
            }
        }

        return response()->json(['message' => 'Webhook received successfully'], 200);
    }
}
