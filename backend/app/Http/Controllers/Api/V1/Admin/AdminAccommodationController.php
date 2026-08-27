<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Http\Requests\Admin\AccommodationRequest;
use Illuminate\Http\JsonResponse;

class AdminAccommodationController extends Controller
{
    public function index(): JsonResponse
    {
        $accommodations = Accommodation::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar penginapan berhasil diambil',
            'data' => $accommodations,
        ]);
    }

    public function show($id): JsonResponse
    {
        $accommodation = Accommodation::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Detail penginapan berhasil diambil',
            'data' => $accommodation,
        ]);
    }

    public function store(AccommodationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        // Ensure facilities is properly handled if not provided
        if (!isset($validated['facilities'])) {
            $validated['facilities'] = [];
        }

        $accommodation = Accommodation::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Penginapan berhasil dibuat',
            'data' => $accommodation,
        ], 201);
    }

    public function update(AccommodationRequest $request, $id): JsonResponse
    {
        $accommodation = Accommodation::findOrFail($id);
        $validated = $request->validated();
        
        if (!isset($validated['facilities'])) {
            $validated['facilities'] = [];
        }

        $accommodation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Penginapan berhasil diperbarui',
            'data' => $accommodation,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $accommodation = Accommodation::findOrFail($id);
        $accommodation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Penginapan berhasil dihapus',
        ]);
    }
}
