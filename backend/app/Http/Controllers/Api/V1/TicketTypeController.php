<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TicketType;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class TicketTypeController extends Controller
{
    /**
     * Get all active ticket types.
     */
    public function index(): JsonResponse
    {
        $ticketTypes = TicketType::where('status', 'ACTIVE')
            ->orderBy('id', 'asc')
            ->get();

        return ApiResponse::success('Daftar jenis tiket berhasil diambil', $ticketTypes);
    }
}
