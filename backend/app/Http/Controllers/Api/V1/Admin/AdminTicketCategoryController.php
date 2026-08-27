<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use App\Http\Requests\Admin\TicketCategoryRequest;
use Illuminate\Http\JsonResponse;

class AdminTicketCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = TicketCategory::orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar paket wisata berhasil diambil',
            'data' => $categories,
        ]);
    }

    public function store(TicketCategoryRequest $request): JsonResponse
    {
        $category = TicketCategory::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Paket wisata berhasil ditambahkan',
            'data' => $category,
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $category = TicketCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Paket wisata tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail paket wisata berhasil diambil',
            'data' => $category,
        ]);
    }

    public function update(TicketCategoryRequest $request, $id): JsonResponse
    {
        $category = TicketCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Paket wisata tidak ditemukan',
            ], 404);
        }

        $category->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Paket wisata berhasil diupdate',
            'data' => $category,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $category = TicketCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Paket wisata tidak ditemukan',
            ], 404);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Paket wisata berhasil dihapus',
        ]);
    }
}
