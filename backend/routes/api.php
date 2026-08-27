<?php

use App\Http\Controllers\Api\V1\AccommodationBookingController;
use App\Http\Controllers\Api\V1\AccommodationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\TicketTypeController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\AdminTicketTypeController;
use App\Http\Controllers\Api\V1\Admin\AdminOrderController;
use App\Http\Controllers\Api\V1\PaymentCallbackController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\XenditWebhookController;

// Public Routes
Route::get('/ticket-types', [TicketTypeController::class, 'index']);

// Public Accommodations
Route::get('/accommodations', [AccommodationController::class, 'index']);
Route::get('/accommodations/{id}', [AccommodationController::class, 'show']);

// Admin Routes (prefix: admin, middleware: auth sanctum)
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // Users CRUD
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::patch('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    
    // Ticket Types CRUD
    Route::get('/ticket-types', [AdminTicketTypeController::class, 'index']);
    Route::post('/ticket-types', [AdminTicketTypeController::class, 'store']);
    Route::get('/ticket-types/{id}', [AdminTicketTypeController::class, 'show']);
    Route::patch('/ticket-types/{id}', [AdminTicketTypeController::class, 'update']);
    Route::delete('/ticket-types/{id}', [AdminTicketTypeController::class, 'destroy']);
    
    // Orders CRUD
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{order_code}', [AdminOrderController::class, 'show']);
    Route::patch('/orders/{order_code}/status', [AdminOrderController::class, 'updateStatus']);
    
    // Dashboard Ringkas
    Route::get('/dashboard', [UserController::class, 'dashboard']);
});

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

    // Scanner API
    Route::post('/scan', [\App\Http\Controllers\Api\V1\ScannerController::class, 'verify']);
    Route::get('/scan/history', [\App\Http\Controllers\Api\V1\ScannerController::class, 'history']);

    // Accommodation Bookings
    Route::get('/accommodation-bookings', [AccommodationBookingController::class, 'index']);
    Route::post('/accommodation-bookings', [AccommodationBookingController::class, 'store']);
});

// Midtrans Webhook
Route::post('/payments/midtrans/notification', [PaymentCallbackController::class, 'handleNotification']);

// Xendit Webhook
Route::post('/payments/xendit/webhook', [App\Http\Controllers\Api\V1\XenditWebhookController::class, 'handleWebhook']);
Route::post('/webhook', [App\Http\Controllers\Api\V1\XenditWebhookController::class, 'handleWebhook']); // Fallback alias
