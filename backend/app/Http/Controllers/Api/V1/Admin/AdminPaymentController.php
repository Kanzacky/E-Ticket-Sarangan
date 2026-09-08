<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminPaymentController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = Order::with('user')->orderBy('id', 'desc');
        if ($s=$request->query('search')) {
            $query->where(function($q) use ($s){
                $q->where('order_code','ilike',"%{$s}%")->orWhere('customer_name','ilike',"%{$s}%");
            });
        }
        if ($status=$request->query('status')) $query->where('status',$status);
        $perPage=$request->query('per_page');
        $orders = $perPage ? $query->paginate((int)$perPage) : $query->get();
        $items = ($orders instanceof \Illuminate\Pagination\LengthAwarePaginator ? $orders->items() : $orders);
        $payments = collect($items)->map(function ($order) {
            return [
                'id' => $order->id,
                'transaction_id' => 'TRX-' . $order->order_code,
                'customer_name' => $order->customer_name ?? $order->user?->name ?? 'Tamu',
                'payment_method' => 'Xendit Invoice',
                'amount' => $order->total_amount,
                'status' => $order->status,
                'paid_at' => in_array($order->status, ['PAID', 'COMPLETED']) ? $order->updated_at : null,
                'created_at' => $order->created_at,
            ];
        });
        if ($orders instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return response()->json(['success'=>true,'message'=>'Data pembayaran berhasil diambil','data'=>$payments,'meta'=>['current_page'=>$orders->currentPage(),'last_page'=>$orders->lastPage(),'per_page'=>$orders->perPage(),'total'=>$orders->total()]]);
        }
        return response()->json(['success'=>true,'message'=>'Data pembayaran berhasil diambil','data'=>$payments]);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:PAID,PENDING,COMPLETED,FAILED,CANCELLED'
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }

        $old=$order->status;
        $order->status = $request->status;
        $order->save();
        \App\Services\AuditService::log($request,'update_payment_status',Order::class,$order->id,['status'=>$old],['status'=>$order->status]);
        return response()->json(['success'=>true,'message'=>'Status pembayaran berhasil diupdate','data'=>['id'=>$order->id,'status'=>$order->status,'paid_at'=>in_array($order->status,['PAID','COMPLETED'])?$order->updated_at:null]]);
    }
}
