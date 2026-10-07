<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use Illuminate\Http\JsonResponse;

class AccommodationController extends Controller
{
    /**
     * GET /api/accommodations — daftar penginapan aktif, diurutkan by rating.
     * Menggunakan Overpass/OSM data yang sudah di-sync ke database (gratis, tanpa API key).
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 12), 50);
        $sort    = $request->input('sort', 'rating'); // default rating tertinggi

        $query = Accommodation::where('is_active', true);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('address', 'ilike', "%{$search}%");
            });
        }

        // Sorting
        match ($sort) {
            'distance'   => $query->orderBy('distance_km', 'asc'),
            'price_asc'  => $query->orderBy('price_per_night', 'asc'),
            'price_desc' => $query->orderBy('price_per_night', 'desc'),
            default      => $query->orderByDesc('rating'),
        };

        $paginated = $query->paginate($perPage);

        // Tambahkan google_maps_link (accessor) ke setiap item
        $items = collect($paginated->items())->map(function (Accommodation $a) {
            $arr = $a->toArray();
            $arr['google_maps_link'] = $a->google_maps_link;
            return $arr;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar rekomendasi penginapan berhasil dimuat.',
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    /**
     * GET /api/accommodations/{id} — detail penginapan.
     */
    public function show(int $id): JsonResponse
    {
        $accommodation = Accommodation::where('is_active', true)->findOrFail($id);

        $arr = $accommodation->toArray();
        $arr['google_maps_link'] = $accommodation->google_maps_link;

        return response()->json([
            'success' => true,
            'data'    => $arr,
        ]);
    }
}
