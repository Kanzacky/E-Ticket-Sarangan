<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScannerController extends Controller
{
    /**
     * Verify a scanned ticket QR code.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'order_code' => 'required|string',
        ]);

        $orderCode = $request->input('order_code');
        
        $logScan = function($isValid, $reason = null) use ($orderCode, $request) {
            \App\Models\ScanLog::create([
                'scanned_by' => $request->user()->id ?? null,
                'order_code' => $orderCode,
                'is_valid' => $isValid,
                'reason' => $reason,
            ]);
        };

        $order = Order::where('order_code', $orderCode)->with('items.ticketType')->first();

        if (!$order) {
            $msg = 'Tiket tidak ditemukan. Pastikan QR Code benar.';
            $logScan(false, $msg);
            return ApiResponse::error($msg, 404);
        }

        if ($order->status !== 'PAID') {
            $msg = 'Tiket ditolak: Status pembayaran belum LUNAS.';
            $logScan(false, $msg);
            return ApiResponse::error($msg, 400);
        }

        if ($order->qr_expires_at && Carbon::now()->greaterThan($order->qr_expires_at)) {
            $msg = 'Tiket ditolak: Tiket sudah kedaluwarsa.';
            $logScan(false, $msg);
            return ApiResponse::error($msg, 400);
        }

        if ($order->scanned_at !== null) {
            $scanTime = Carbon::parse($order->scanned_at)->translatedFormat('d F Y H:i');
            $msg = "Tiket ditolak: Sudah digunakan pada {$scanTime}.";
            $logScan(false, $msg);
            return ApiResponse::error($msg, 400);
        }

        // Tiket valid. Update status scan.
        $order->scanned_at = Carbon::now();
        $order->scanned_by = $request->user()->id ?? null;
        $order->status = 'COMPLETED';
        $order->save();

        $logScan(true, 'Tiket Valid. Check-in berhasil.');

        // Build display data for the scanner UI
        $ticketTypes = $order->items->map(function ($item) {
            return $item->ticketType->name . ' (' . $item->quantity . 'x)';
        })->join(', ');

        $data = [
            'code' => $order->order_code,
            'name' => $order->customer_name,
            'date' => Carbon::parse($order->visit_date)->translatedFormat('d F Y'),
            'type' => $ticketTypes,
            'qty' => $order->total_quantity,
        ];

        return ApiResponse::success('Tiket Valid. Check-in berhasil.', $data);
    }

    /**
     * Get scan history for the current officer.
     */
    public function history(Request $request): JsonResponse
    {
        $logs = \App\Models\ScanLog::where('scanned_by', $request->user()->id)
            ->with(['order.items.ticketType'])
            ->orderBy('created_at', 'desc')
            ->get();

        return ApiResponse::success('Riwayat scan berhasil diambil', $logs);
    }
}
