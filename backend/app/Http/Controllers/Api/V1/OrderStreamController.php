<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderStreamController extends Controller
{
    /**
     * Server-Sent Events stream status pesanan.
     * Wisatawan membuka stream ini supaya perubahan status tiket (misalnya
     * menjadi COMPLETED setelah dipindai petugas) ter-update tanpa reload.
     */
    public function stream(Request $request, string $order_code): StreamedResponse|JsonResponse
    {
        $order = Order::where('order_code', $order_code)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $order) {
            return ApiResponse::error('Data booking tidak ditemukan', 404);
        }

        $response = new StreamedResponse(function () use ($order_code, $request) {
            $terminal = ['COMPLETED', 'EXPIRED', 'CANCELLED'];
            $maxTicks = 120; // ~4 menit, klien biasanya berhenti lebih dulu

            for ($i = 0; $i < $maxTicks; $i++) {
                $order = Order::where('order_code', $order_code)
                    ->where('user_id', $request->user()->id)
                    ->with(['items.ticketType'])
                    ->first();

                if (! $order) {
                    echo "data: {\"error\":\"not_found\"}\n\n";
                    ob_flush();
                    flush();
                    break;
                }

                echo 'data: '.json_encode([
                    'status' => $order->status,
                    'scanned_at' => $order->scanned_at,
                    'order' => $order->toArray(),
                ])."\n\n";
                ob_flush();
                flush();

                if (in_array($order->status, $terminal, true) || connection_aborted()) {
                    break;
                }

                sleep(2);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
