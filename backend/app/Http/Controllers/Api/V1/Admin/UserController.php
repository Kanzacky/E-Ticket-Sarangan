<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Http\Requests\Admin\UserStoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = User::query();
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")->orWhere('email', 'ilike', "%{$search}%");
            });
        }
        $perPage = $request->query('per_page');
        if ($perPage) {
            $paginated = $query->latest()->paginate((int) $perPage);
            return response()->json(['success' => true, 'message' => 'Daftar pengguna berhasil diambil', 'data' => $paginated->items(), 'meta' => ['current_page' => $paginated->currentPage(), 'last_page' => $paginated->lastPage(), 'per_page' => $paginated->perPage(), 'total' => $paginated->total()]]);
        }
        $users = $query->latest()->get(['id', 'name', 'email', 'role', 'phone', 'created_at']);
        return response()->json(['success' => true, 'message' => 'Daftar pengguna berhasil diambil', 'data' => $users]);
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['role'] = strtolower($validated['role']);
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        \App\Services\AuditService::log($request, 'create_user', User::class, $user->id, null, $validated);
        return response()->json(['success' => true, 'message' => 'Pengguna berhasil ditambahkan', 'data' => $user->only(['id', 'name', 'email', 'role', 'phone', 'created_at'])], 201);
    }

    public function dashboard(): JsonResponse
    {
        $revenue = Order::whereIn('status', ['PAID', 'COMPLETED'])
            ->sum('total_amount');

        $orders = Order::with('user:id,name,email')
            ->latest()
            ->take(5)
            ->get(['id', 'user_id', 'order_code', 'visit_date', 'customer_name', 'total_amount', 'status', 'created_at']);

        $totalTickets = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['PAID', 'COMPLETED'])
            ->sum('quantity');

        $totalVisitors = Order::whereIn('status', ['PAID', 'COMPLETED'])->count();

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
        $old = $user->toArray();
        if (isset($validated['role'])) $validated['role'] = strtolower($validated['role']);
        $user->update($validated);
        \App\Services\AuditService::log($request, 'update_user', User::class, $user->id, $old, $validated);
        return response()->json(['success' => true, 'message' => 'Pengguna berhasil diperbarui', 'data' => $user->only(['id', 'name', 'email', 'role', 'phone'])]);
    }

    public function destroy(User $user): JsonResponse
    {
        $old = $user->toArray();
        $user->delete();
        \App\Services\AuditService::log(request(), 'delete_user', User::class, $old['id'] ?? null, $old, null);
        return response()->json(['success' => true, 'message' => 'Pengguna berhasil dihapus']);
    }
}