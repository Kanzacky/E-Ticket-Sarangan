<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    /**
     * Handle Midtrans Webhook Notification
     */
    public function handleNotification(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Midtrans Webhook Received:', $payload);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        if (!$orderId || !$signatureKey) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // Verify signature
        $serverKey = config('midtrans.server_key');
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($expectedSignature !== $signatureKey) {
            Log::warning('Midtrans Invalid Signature', ['expected' => $expectedSignature, 'received' => $signatureKey]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Extract real booking_code from order_id (format: BOOKINGCODE-TIMESTAMP)
        $bookingCode = explode('-', $orderId)[0];
        
        $booking = Booking::where('booking_code', $bookingCode)->first();
        if (!$booking) {
            Log::error('Midtrans Webhook: Booking not found', ['booking_code' => $bookingCode]);
            return response()->json(['message' => 'Booking not found'], 404);
        }

        // Find or create payment record
        $payment = Payment::firstOrCreate(
            ['booking_id' => $booking->id],
            [
                'amount' => $grossAmount,
                'status' => 'pending'
            ]
        );

        $payment->payment_method = $payload['payment_type'] ?? null;
        $payment->transaction_id = $payload['transaction_id'] ?? null;

        // Determine status
        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            $payment->status = 'success';
            $payment->paid_at = now();
            
            // Update booking status
            $booking->status = 'paid';
            $booking->save();
        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $payment->status = $transactionStatus === 'expire' ? 'expired' : 'failed';
            
            // Update booking status
            $booking->status = 'cancelled';
            $booking->save();
        } else if ($transactionStatus == 'pending') {
            $payment->status = 'pending';
        }

        $payment->save();

        return response()->json(['message' => 'Notification handled']);
    }
}
