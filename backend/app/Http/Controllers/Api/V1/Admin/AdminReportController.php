<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminReportController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        // Simple period filter: default to this month
        $period = $request->query('period', 'month'); // 'today', 'week', 'month', 'year', 'all'
        
        $query = Order::whereIn('status', ['PAID', 'COMPLETED']);
        
        if ($period === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($period === 'week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
        } elseif ($period === 'year') {
            $query->whereYear('created_at', Carbon::now()->year);
        }

        // Summary Cards
        $totalRevenue = $query->sum('total_amount');
        $totalOrders = $query->count();
        $totalTicketsSold = $query->sum('total_quantity');
        
        // Revenue trend (last 7 days if not 'year'/'all')
        // For simplicity, we'll just group by date for the filtered period
        $trend = $query->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('COUNT(id) as orders_count')
        )
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();

        // Top ticket types
        $topTickets = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('ticket_types', 'order_items.ticket_type_id', '=', 'ticket_types.id')
            ->whereIn('orders.status', ['PAID', 'COMPLETED'])
            ->select('ticket_types.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('ticket_types.id', 'ticket_types.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diambil',
            'data' => [
                'summary' => [
                    'revenue' => (float) $totalRevenue,
                    'orders' => $totalOrders,
                    'tickets_sold' => (int) $totalTicketsSold,
                ],
                'trend' => $trend,
                'top_tickets' => $topTickets,
            ]
        ]);
    }
}
