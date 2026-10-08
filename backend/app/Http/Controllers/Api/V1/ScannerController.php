<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ScannerController extends Controller
{
    /**
     * Verify a scanned ticket QR code.
     *
     * Optimasi performa:
     * - select() hanya kolom yang dibutuhkan (tidak load semua kolom order)
     * - with('items.ticketType') eager load sekali — menghindari N+1 query
     * - Semua pengecekan validasi dilakukan tanpa query tambahan
     * - $order->save() diganti DB::table()->update() agar lebih ringan
     *   (skip Eloquent dirty checking, timestamps, event broadcasting)
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'order_code' => 'required|string|max:50',
        ]);

        $orderCode = $request->input('order_code');

        // SELECT hanya kolom yang dibutuhkan — jauh lebih ringan dari SELECT *
        $order = Order::where('order_code', $orderCode)
            ->select([
                'id', 'order_code', 'status',
                'customer_name', 'visit_date', 'total_quantity',
                'qr_expires_at', 'scanned_at', 'scanned_by',
            ])
            ->with(['items' => fn ($q) => $q->select(['id', 'order_id', 'quantity', 'ticket_type_id']),
                    'items.ticketType' => fn ($q) => $q->select(['id', 'name'])])
            ->first()
        ;

        $scanBy = $request->user()->id ?? null;

        if (!$order) {
            $msg = 'Tiket tidak ditemukan. Pastikan QR Code benar.';
            $this->logScan($orderCode, $scanBy, false, $msg);
            return ApiResponse::error($msg, 404);
        }

        if ($order->status !== 'PAID') {
            $msg = 'Tiket ditolak: Status pembayaran belum LUNAS.';
            $this->logScan($orderCode, $scanBy, false, $msg);
            return ApiResponse::error($msg, 400);
        }

        if ($order->qr_expires_at && Carbon::now()->greaterThan($order->qr_expires_at)) {
            $msg = 'Tiket ditolak: Tiket sudah kedaluwarsa.';
            $this->logScan($orderCode, $scanBy, false, $msg);
            return ApiResponse::error($msg, 400);
        }

        if ($order->scanned_at !== null) {
            $scanTime = Carbon::parse($order->scanned_at)->translatedFormat('d F Y H:i');
            $msg = "Tiket ditolak: Sudah digunakan pada {$scanTime}.";
            $this->logScan($orderCode, $scanBy, false, $msg);
            return ApiResponse::error($msg, 400);
        }

        // Tiket valid: update secara atomic dengan raw query (lebih cepat dari $model->save())
        $now = Carbon::now();
        DB::table('orders')->where('id', $order->id)->update([
            'scanned_at' => $now,
            'scanned_by' => $scanBy,
            'status'     => 'COMPLETED',
            'updated_at' => $now,
        ]);

        $this->logScan($orderCode, $scanBy, true, 'Tiket Valid. Check-in berhasil.');

        // Kirim notifikasi secara non-blocking (jika gagal tidak mempengaruhi response)
        try {
            $order->scanned_at = $now;
            $order->status     = 'COMPLETED';
            \App\Services\NotificationService::sendOrderScanned($order);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Notif scanned failed: ' . $e->getMessage());
        }

        // Build display data
        $ticketTypes = $order->items->map(
            fn ($item) => $item->ticketType->name . ' (' . $item->quantity . 'x)'
        )->join(', ');

        return ApiResponse::success('Tiket Valid. Check-in berhasil.', [
            'code' => $order->order_code,
            'name' => $order->customer_name,
            'date' => Carbon::parse($order->visit_date)->translatedFormat('d F Y'),
            'type' => $ticketTypes,
            'qty'  => $order->total_quantity,
        ]);
    }

    /**
     * Get scan history for the current officer.
     */
    public function history(Request $request): JsonResponse
    {
        $logs = \App\Models\ScanLog::where('scanned_by', $request->user()->id)
            ->with(['order' => fn ($q) => $q->select(['id', 'order_code', 'customer_name', 'visit_date', 'total_quantity']),
                    'order.items' => fn ($q) => $q->select(['id', 'order_id', 'quantity', 'ticket_type_id']),
                    'order.items.ticketType' => fn ($q) => $q->select(['id', 'name'])])
            ->orderBy('created_at', 'desc')
            ->limit(50) // Batasi ke 50 scan terakhir — tidak perlu load semua
            ->get()
        ;

        return ApiResponse::success('Riwayat scan berhasil diambil', $logs);
    }

    private function logScan(string $orderCode, ?int $scannedBy, bool $isValid, string $reason): void
    {
        try {
            \App\Models\ScanLog::create([
                'scanned_by' => $scannedBy,
                'order_code' => $orderCode,
                'is_valid'   => $isValid,
                'reason'     => $reason,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ScanLog failed: ' . $e->getMessage());
        }
    }
}
