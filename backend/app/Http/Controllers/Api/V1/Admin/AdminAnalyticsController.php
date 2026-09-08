<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ScanLog;
use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminAnalyticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $period = $request->query('period', 'month');

        $orderQuery = Order::whereIn('status', ['PAID', 'COMPLETED']);
        $scanQuery = ScanLog::query();
        $accomBookingQuery = AccommodationBooking::query();

        if ($period === 'today') {
            $orderQuery->whereDate('created_at', Carbon::today());
            $scanQuery->whereDate('created_at', Carbon::today());
            $accomBookingQuery->whereDate('created_at', Carbon::today());
        } elseif ($period === 'week') {
            $orderQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            $scanQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            $accomBookingQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $orderQuery->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
            $scanQuery->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
            $accomBookingQuery->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
        } elseif ($period === 'year') {
            $orderQuery->whereYear('created_at', Carbon::now()->year);
            $scanQuery->whereYear('created_at', Carbon::now()->year);
            $accomBookingQuery->whereYear('created_at', Carbon::now()->year);
        }

        $revenue = (float) $orderQuery->sum('total_amount');
        $orders = $orderQuery->count();
        $tickets = (int) $orderQuery->sum('total_quantity');
        $pending = Order::where('status', 'PENDING')->count();
        $expired = Order::where('status', 'EXPIRED')->count();

        $scansValid = (clone $scanQuery)->where('is_valid', true)->count();
        $scansInvalid = (clone $scanQuery)->where('is_valid', false)->count();
        $scanSuccessRate = ($scansValid + $scansInvalid) > 0 ? round($scansValid / ($scansValid + $scansInvalid) * 100, 1) : 0;

        $trend = $orderQuery->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(id) as orders_count'))
            ->groupBy('date')->orderBy('date')->get();

        $topTickets = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('ticket_types', 'order_items.ticket_type_id', '=', 'ticket_types.id')
            ->whereIn('orders.status', ['PAID', 'COMPLETED'])
            ->select('ticket_types.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('ticket_types.id', 'ticket_types.name')
            ->orderByDesc('total_sold')->limit(5)->get();

        $accomStats = [
            'total_accommodations' => Accommodation::count(),
            'active_accommodations' => Accommodation::where('is_active', true)->count(),
            'total_bookings' => $accomBookingQuery->count(),
            'occupancy_avg' => Accommodation::avg('available_rooms'),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Analitik berhasil diambil',
            'data' => [
                'summary' => compact('revenue', 'orders', 'tickets', 'pending', 'expired'),
                'scans' => ['valid' => $scansValid, 'invalid' => $scansInvalid, 'success_rate' => $scanSuccessRate],
                'trend' => $trend,
                'top_tickets' => $topTickets,
                'accommodations' => $accomStats,
            ]
        ]);
    }
}
