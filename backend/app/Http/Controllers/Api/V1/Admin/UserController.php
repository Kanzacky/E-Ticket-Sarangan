<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Admin\UserUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::latest()->get(['id', 'name', 'email', 'role', 'phone', 'created_at']);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengguna berhasil diambil',
            'data' => $users,
        ]);
    }

    public function dashboard(): JsonResponse
    {
        $revenue = Order::where('status', 'PAID')
            ->sum('total_amount');

        $orders = Order::latest()->take(5)->get(['id', 'order_code', 'visit_date', 'customer_name', 'total_amount', 'status']);

        $totalTickets = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'PAID')
            ->sum('quantity');

        $totalVisitors = Order::where('status', 'PAID')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'revenue' => $revenue,
                    'orders' => Order::count(),
                    'tickets' => $totalTickets,
                    'visitors' => $totalVisitors,
                ],
                'recent_orders' => $orders,
            ],
        ]);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail pengguna berhasil diambil',
            'data' => $user->only(['id', 'name', 'email', 'role', 'phone', 'created_at']),
        ]);
    }

    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['role'])) {
            $validated['role'] = strtolower($validated['role']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil diperbarui',
            'data' => $user->only(['id', 'name', 'email', 'role', 'phone']),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil dihapus',
        ]);
    }
}