<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminPaymentController extends Controller
{
    public function index(): JsonResponse
    {
        // For this system, Orders act as the primary transaction/payment record.
        // We'll fetch orders, ideally those that aren't pending, to represent payments.
        // But for completeness, we return all orders so admin can see FAILED/PENDING too.
        $payments = Order::with('user')->orderBy('id', 'desc')->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'transaction_id' => 'TRX-' . $order->order_code,
                'customer_name' => $order->customer_name ?? $order->user?->name ?? 'Tamu',
                'payment_method' => 'Bank Transfer / E-Wallet', // Mock or derive if stored
                'amount' => $order->total_amount,
                'status' => $order->status,
                'paid_at' => $order->status === 'PAID' ? $order->updated_at : null,
                'created_at' => $order->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data pembayaran berhasil diambil',
            'data' => $payments,
        ]);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:PAID,PENDING,FAILED,CANCELLED'
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Status pembayaran berhasil diupdate',
            'data' => [
                'id' => $order->id,
                'status' => $order->status,
                'paid_at' => $order->status === 'PAID' ? $order->updated_at : null,
            ]
        ]);
    }
}
