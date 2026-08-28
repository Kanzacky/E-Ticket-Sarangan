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
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 12), 50);

        $query = Accommodation::where('is_active', true)
            ->orderByDesc('rating');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('address', 'ilike', "%{$search}%");
            });
        }

        $paginated = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar penginapan berhasil dimuat.',
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
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
