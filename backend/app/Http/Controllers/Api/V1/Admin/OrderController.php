<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::with(['user', 'items.ticketType'])
            ->latest()
            ->get(['id', 'order_code', 'visit_date', 'customer_name', 'customer_email', 'customer_phone', 'total_quantity', 'total_amount', 'status', 'created_at']);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pesanan berhasil diambil',
            'data' => $orders,
        ]);
    }

    public function show($order_code): JsonResponse
    {
        $order = Order::with(['user', 'items.ticketType'])
            ->where('order_code', $order_code)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil',
            'data' => $order->only(['id', 'order_code', 'visit_date', 'customer_name', 'customer_email', 'customer_phone', 'total_quantity', 'total_amount', 'status', 'created_at']),
        ]);
    }

    public function updateStatus($order_code, Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:PENDING,PAID,CANCELLED,EXPIRED'],
        ]);

        $order = Order::where('order_code', $order_code)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan berhasil diperbarui',
            'data' => $order->only(['id', 'order_code', 'status']),
        ]);
    }