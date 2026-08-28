<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Requests\Admin\OrderStatusRequest;
use Illuminate\Http\JsonResponse;

class AdminOrderController extends Controller
{
    public function index(): JsonResponse
    {
        // Load user and items to provide detailed info in the list
        $orders = Order::with(['user', 'items.ticketType'])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar pesanan berhasil diambil',
            'data' => $orders,
        ]);
    }

    public function show($order_code): JsonResponse
    {
        $order = Order::with(['user', 'items.ticketType'])->where('order_code', $order_code)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil',
            'data' => $order,
        ]);
    }

    public function updateStatus(OrderStatusRequest $request, $order_code): JsonResponse
    {
        $validated = $request->validated();

        $order = Order::where('order_code', $order_code)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        $order->status = $validated['status'];
        $order->save();
        // Notifikasi status manual (PAID/EXPIRED/COMPLETED)
        try {
            if ($order->status === 'PAID') \App\Services\NotificationService::sendOrderPaid($order);
            elseif ($order->status === 'EXPIRED') \App\Services\NotificationService::sendOrderExpired($order);
            elseif ($order->status === 'COMPLETED') \App\Services\NotificationService::sendOrderScanned($order);
        } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning('Notif admin status failed: '.$e->getMessage()); }

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan berhasil diupdate',
            'data' => $order->only(['id', 'order_code', 'status']),
        ]);
    }
}
