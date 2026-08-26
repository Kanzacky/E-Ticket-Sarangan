<?php

use App\Http\Controllers\Api\V1\AccommodationBookingController;
use App\Http\Controllers\Api\V1\AccommodationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\TicketTypeController;
use App\Http\Controllers\Api\V1\PaymentCallbackController;
use Illuminate\Support\Facades\Route;

// Prefix grup: `api` (dari withRouting apiPrefix)

// Public Ticket Types
Route::get('/ticket-types', [TicketTypeController::class, 'index']);

// Public Accommodations
Route::get('/accommodations', [AccommodationController::class, 'index']);
Route::get('/accommodations/{id}', [AccommodationController::class, 'show']);

// Auth Endpoints
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Orders Endpoints (Sanctum protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order_code}', [OrderController::class, 'show']);

    // Accommodation Bookings
    Route::get('/accommodation-bookings', [AccommodationBookingController::class, 'index']);
    Route::post('/accommodation-bookings', [AccommodationBookingController::class, 'store']);
});

// Midtrans Webhook
Route::post('/payments/midtrans/notification', [PaymentCallbackController::class, 'handleNotification']);

