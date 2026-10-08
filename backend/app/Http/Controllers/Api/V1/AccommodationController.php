<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AccommodationController extends Controller
{
    /**
     * GET /api/accommodations — daftar penginapan aktif, diurutkan by rating.
     *
     * Data akomodasi di-sync dari OSM/Overpass dan jarang berubah.
     * Request tanpa filter (default homepage) di-cache 10 menit.
     * Request dengan search/sort/page khusus tetap fresh dari DB.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 12), 50);
        $sort    = $request->input('sort', 'rating');
        $search  = $request->input('search', '');
        $page    = (int) $request->input('page', 1);

        // Cache hanya untuk request default (homepage preview) tanpa filter
        $useCache = empty($search) && $sort === 'rating' && $page === 1 && $perPage <= 12;

        if ($useCache) {
            $cacheKey = "accommodations_default_{$perPage}";
            $result   = Cache::remember($cacheKey, 600, fn () => $this->buildQuery($request, $perPage, $sort, $search));
        } else {
            $result = $this->buildQuery($request, $perPage, $sort, $search);
        }

        return response()->json($result)->withHeaders([
            'Cache-Control' => $useCache ? 'public, max-age=300, stale-while-revalidate=60' : 'private, no-cache',
        ]);
    }

    private function buildQuery(Request $request, int $perPage, string $sort, string $search): array
    {
        $query = Accommodation::where('is_active', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('address', 'ilike', "%{$search}%");
            });
        }

        match ($sort) {
            'distance'   => $query->orderBy('distance_km', 'asc'),
            'price_asc'  => $query->orderBy('price_per_night', 'asc'),
            'price_desc' => $query->orderBy('price_per_night', 'desc'),
            default      => $query->orderByDesc('rating'),
        };

        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())->map(function (Accommodation $a) {
            $arr = $a->toArray();
            $arr['google_maps_link'] = $a->google_maps_link;
            return $arr;
        });

        return [
            'success' => true,
            'message' => 'Daftar rekomendasi penginapan berhasil dimuat.',
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ];
    }

    /**
     * GET /api/accommodations/{id} — detail penginapan.
     */
    public function show(int $id): JsonResponse
    {
        $cacheKey = "accommodation_{$id}";
        $arr      = Cache::remember($cacheKey, 600, function () use ($id) {
            $accommodation = Accommodation::where('is_active', true)->findOrFail($id);
            $arr = $accommodation->toArray();
            $arr['google_maps_link'] = $accommodation->google_maps_link;
            return $arr;
        });

        return response()->json([
            'success' => true,
            'data'    => $arr,
        ])->withHeaders([
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
