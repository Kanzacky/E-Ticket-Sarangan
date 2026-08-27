<?php

namespace App\Http\Controllers\Api\V1\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ScanHistory; // if exists, otherwise we'll check how scanner is implemented
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class PetugasController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $today = Carbon::today();

        // Kunjungan Hari Ini (orders with visit_date = today)
        $kunjunganHariIni = Order::whereDate('visit_date', $today)
            ->whereIn('status', ['PAID', 'COMPLETED'])
            ->count();

        // Tiket Diverifikasi (orders where status is COMPLETED or has check-in today)
        // Wait, e-Ticket Sarangan sets order status to COMPLETED when scanned.
        $diverifikasi = Order::whereDate('visit_date', $today)
            ->where('status', 'COMPLETED')
            ->count();

        // Menunggu Kedatangan
        $menunggu = Order::whereDate('visit_date', $today)
            ->where('status', 'PAID')
            ->count();

        // Recent Orders (Only for today's valid visits to match statistics)
        $recentOrders = Order::with('user:id,name,email')
            ->whereDate('visit_date', $today)
            ->whereIn('status', ['PAID', 'COMPLETED'])
            ->latest()
            ->take(5)
            ->get(['id', 'user_id', 'order_code', 'visit_date', 'customer_name', 'total_amount', 'status', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'kunjungan_hari_ini' => $kunjunganHariIni,
                    'diverifikasi' => $diverifikasi,
                    'menunggu' => $menunggu,
                    'bermasalah' => 0, // Mock for now
                ],
                'recent_visits' => $recentOrders,
            ],
        ]);
    }

    public function visits(): JsonResponse
    {
        $today = Carbon::today();
        $visits = Order::with('user:id,name,email')
            ->whereDate('visit_date', $today)
            ->whereIn('status', ['PAID', 'COMPLETED'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $visits,
        ]);
    }

    public function bookings(): JsonResponse
    {
        $bookings = Order::with(['user:id,name,email,phone', 'items.ticketType'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    public function users(): JsonResponse
    {
        $users = \App\Models\User::where('role', 'wisatawan')
            ->latest()
            ->get(['id', 'name', 'email', 'phone', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }
}
