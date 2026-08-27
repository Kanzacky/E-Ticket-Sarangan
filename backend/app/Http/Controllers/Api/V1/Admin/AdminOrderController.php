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
        $orders = Order::latest()->get(['id', 'order_code', 'visit_date', 'customer_name', 'total_amount', 'status']);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pesanan berhasil diambil',
            'data' => $orders,
        ]);
    }

    public function show($order_code): JsonResponse
    {
        $order = Order::where('order_code', $order_code)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil',
            'data' => $order->only(['id', 'order_code', 'visit_date', 'customer_name', 'total_amount', 'status']),
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

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan berhasil diupdate',
            'data' => $order->only(['id', 'order_code', 'status']),
        ]);
    }
}
