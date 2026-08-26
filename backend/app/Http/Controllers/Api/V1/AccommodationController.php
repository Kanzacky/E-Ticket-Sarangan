<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use Illuminate\Http\JsonResponse;

class AccommodationController extends Controller
{
    /**
     * GET /api/accommodations — daftar penginapan aktif.
     */
    public function index(): JsonResponse
    {
        $accommodations = Accommodation::where('is_active', true)
            ->orderByDesc('rating')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar penginapan berhasil dimuat.',
            'data' => $accommodations,
        ]);
    }

    /**
     * GET /api/accommodations/{id} — detail penginapan.
     */
    public function show(int $id): JsonResponse
    {
        $accommodation = Accommodation::where('is_active', true)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $accommodation,
        ]);
    }
}
