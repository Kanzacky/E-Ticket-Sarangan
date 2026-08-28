<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScanLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCheckinController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $logs = ScanLog::with(['order.items.ticketType'])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'order_code' => $log->order_code,
                    'is_valid' => (bool) $log->is_valid,
                    'reason' => $log->reason,
                    'scanned_by' => $log->scanned_by,
                    'created_at' => $log->created_at,
                    'order' => $log->order ? [
                        'order_code' => $log->order->order_code,
                        'customer_name' => $log->order->customer_name,
                        'visit_date' => $log->order->visit_date,
                        'total_quantity' => $log->order->total_quantity,
                        'status' => $log->order->status,
                        'items' => $log->order->items->map(fn($i) => $i->ticketType->name . ' x' . $i->quantity),
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Monitoring check-in berhasil diambil',
            'data' => $logs,
        ]);
    }
}
