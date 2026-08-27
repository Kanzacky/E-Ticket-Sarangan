<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketType;
use App\Http\Requests\Admin\TicketTypeRequest;
use Illuminate\Http\JsonResponse;

class AdminTicketTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $ticketTypes = TicketType::latest()->get(['id', 'name', 'description', 'price', 'quota', 'status', 'created_at']);

        return response()->json([
            'success' => true,
            'message' => 'Daftar jenis tiket berhasil diambil',
            'data' => $ticketTypes,
        ]);
    }

    public function show(TicketType $ticketType): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail jenis tiket berhasil diambil',
            'data' => $ticketType->only(['id', 'name', 'description', 'price', 'quota', 'status']),
        ]);
    }

    public function store(TicketTypeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $ticketType = TicketType::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jenis tiket berhasil dibuat',
            'data' => $ticketType->only(['id', 'name', 'description', 'price', 'quota', 'status']),
        ], 201);
    }

    public function update(TicketTypeRequest $request, TicketType $ticketType): JsonResponse
    {
        $validated = $request->validated();

        $ticketType->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jenis tiket berhasil diperbarui',
            'data' => $ticketType->only(['id', 'name', 'description', 'price', 'quota', 'status']),
        ]);
    }

    public function destroy(TicketType $ticketType): JsonResponse
    {
        $ticketType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jenis tiket berhasil dihapus',
        ]);
    }
}
