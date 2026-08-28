<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Requests\Admin\OrderStatusRequest;
use Illuminate\Http\JsonResponse;

class AdminOrderController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = Order::with(['user', 'items.ticketType']);
        if ($s=$request->query('search')) {
            $query->where(function($q) use ($s){
                $q->where('order_code','ilike',"%{$s}%")->orWhere('customer_name','ilike',"%{$s}%")->orWhereHas('user',fn($u)=>$u->where('name','ilike',"%{$s}%"));
            });
        }
        if ($status=$request->query('status')) $query->where('status',$status);
        if ($perPage=$request->query('per_page')) {
            $p=$query->latest()->paginate((int)$perPage);
            return response()->json(['success'=>true,'message'=>'Daftar pesanan berhasil diambil','data'=>$p->items(),'meta'=>['current_page'=>$p->currentPage(),'last_page'=>$p->lastPage(),'per_page'=>$p->perPage(),'total'=>$p->total()]]);
        }
        $orders = $query->latest()->get();
        return response()->json(['success'=>true,'message'=>'Daftar pesanan berhasil diambil','data'=>$orders]);
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

        $old=$order->status;
        $order->status = $validated['status'];
        $order->save();
        \App\Services\AuditService::log($request,'update_order_status',Order::class,$order->id,['status'=>$old],['status'=>$order->status]);
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
