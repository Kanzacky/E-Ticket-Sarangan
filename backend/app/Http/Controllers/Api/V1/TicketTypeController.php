<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TicketType;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class TicketTypeController extends Controller
{
    /**
     * GET /api/ticket-types — daftar jenis tiket aktif.
     *
     * Data tiket jarang berubah (hanya saat admin ubah di dashboard).
     * Di-cache selama 10 menit untuk menghindari DB query berulang,
     * terutama saat Railway cold start dengan banyak request serentak.
     */
    public function index(): JsonResponse
    {
        $ticketTypes = Cache::remember('ticket_types_active', 600, function () {
            return TicketType::where('status', 'ACTIVE')
                ->orderBy('id', 'asc')
                ->get();
        });

        return ApiResponse::success('Daftar jenis tiket berhasil diambil', $ticketTypes);
    }
}
