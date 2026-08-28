<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AccommodationBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccommodationBookingController extends Controller
{
    /**
     * GET /api/accommodation-bookings — riwayat booking penginapan user.
     */
    public function index(Request $request): JsonResponse
    {
        $bookings = AccommodationBooking::where('user_id', $request->user()->id)
            ->with('accommodation')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    /**
     * POST /api/accommodation-bookings — buat booking penginapan baru.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'rooms' => 'required|integer|min:1',
            'guests' => 'required|integer|min:1',
            'guest_name' => 'required|string|max:255',
            'guest_phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        $accommodation = \App\Models\Accommodation::where('is_active', true)
            ->findOrFail($validated['accommodation_id']);

        if ($validated['rooms'] > $accommodation->available_rooms) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah kamar yang diminta melebihi ketersediaan (' . $accommodation->available_rooms . ' kamar tersedia).',
            ], 409);
        }

        $checkIn = \Carbon\Carbon::parse($validated['check_in']);
        $checkOut = \Carbon\Carbon::parse($validated['check_out']);
        $nights = $checkIn->diffInDays($checkOut);
        $totalPrice = $accommodation->price_per_night * $validated['rooms'] * $nights;

        $booking = AccommodationBooking::create([
            'booking_code' => 'ACC-' . strtoupper(Str::random(8)),
            'user_id' => $request->user()->id,
            'accommodation_id' => $accommodation->id,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'rooms' => $validated['rooms'],
            'guests' => $validated['guests'],
            'total_price' => $totalPrice,
            'guest_name' => $validated['guest_name'],
            'guest_phone' => $validated['guest_phone'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        $accommodation->available_rooms -= $validated['rooms'];
        $accommodation->save();

        $booking->load('accommodation');
        try { \App\Services\NotificationService::sendAccommodationBooked($booking); } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning('Notif accommodation failed: '.$e->getMessage()); }

        return response()->json([
            'success' => true,
            'message' => 'Booking penginapan berhasil dibuat.',
            'data' => $booking,
        ], 201);
    }
}
