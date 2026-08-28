<?php

use App\Http\Controllers\Api\V1\AccommodationBookingController;
use App\Http\Controllers\Api\V1\AccommodationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\TicketTypeController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\AdminTicketTypeController;
use App\Http\Controllers\Api\V1\Admin\AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\AdminAccommodationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\XenditWebhookController;

// Public Routes
Route::get('/ticket-types', [TicketTypeController::class, 'index']);

// Public Accommodations
Route::get('/accommodations', [AccommodationController::class, 'index']);
Route::get('/accommodations/{id}', [AccommodationController::class, 'show']);

// Admin Routes (prefix: admin, middleware: auth sanctum)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // Users CRUD
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::patch('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    
    // Ticket Types CRUD
    Route::get('/ticket-types', [AdminTicketTypeController::class, 'index']);
    Route::post('/ticket-types', [AdminTicketTypeController::class, 'store']);
    Route::get('/ticket-types/{id}', [AdminTicketTypeController::class, 'show']);
    Route::patch('/ticket-types/{id}', [AdminTicketTypeController::class, 'update']);
    Route::delete('/ticket-types/{id}', [AdminTicketTypeController::class, 'destroy']);
    // Ticket Categories CRUD
    Route::get('/ticket-categories', [\App\Http\Controllers\Api\V1\Admin\AdminTicketCategoryController::class, 'index']);
    Route::post('/ticket-categories', [\App\Http\Controllers\Api\V1\Admin\AdminTicketCategoryController::class, 'store']);
    Route::get('/ticket-categories/{id}', [\App\Http\Controllers\Api\V1\Admin\AdminTicketCategoryController::class, 'show']);
    Route::patch('/ticket-categories/{id}', [\App\Http\Controllers\Api\V1\Admin\AdminTicketCategoryController::class, 'update']);
    Route::delete('/ticket-categories/{id}', [\App\Http\Controllers\Api\V1\Admin\AdminTicketCategoryController::class, 'destroy']);
    
    // Orders CRUD
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{order_code}', [AdminOrderController::class, 'show']);
    Route::patch('/orders/{order_code}/status', [AdminOrderController::class, 'updateStatus']);
    
    // Payments (derived from Orders/Transactions)
    Route::get('/payments', [\App\Http\Controllers\Api\V1\Admin\AdminPaymentController::class, 'index']);
    Route::patch('/payments/{id}/status', [\App\Http\Controllers\Api\V1\Admin\AdminPaymentController::class, 'updateStatus']);

    // Reports
    Route::get('/reports/summary', [\App\Http\Controllers\Api\V1\Admin\AdminReportController::class, 'summary']);
    Route::get('/analytics', [\App\Http\Controllers\Api\V1\Admin\AdminAnalyticsController::class, 'index']);
    Route::get('/audit-logs', [\App\Http\Controllers\Api\V1\Admin\AdminAuditLogController::class, 'index']);
    Route::get('/checkins', [\App\Http\Controllers\Api\V1\Admin\AdminCheckinController::class, 'index']);
    Route::get('/upgrades', [\App\Http\Controllers\Api\V1\Admin\AdminTicketUpgradeController::class, 'index']);
    Route::get('/settings', [\App\Http\Controllers\Api\V1\Admin\AdminSettingsController::class, 'index']);
    Route::patch('/settings', [\App\Http\Controllers\Api\V1\Admin\AdminSettingsController::class, 'update']);

    // Dashboard Ringkas
    Route::get('/dashboard', [UserController::class, 'dashboard']);

    // Accommodations CRUD
    // Accommodations CRUD
    Route::get('/accommodations', [AdminAccommodationController::class, 'index']);
    Route::post('/accommodations', [AdminAccommodationController::class, 'store']);
    Route::get('/accommodations/{id}', [AdminAccommodationController::class, 'show']);
    Route::patch('/accommodations/{id}', [AdminAccommodationController::class, 'update']);
    Route::delete('/accommodations/{id}', [AdminAccommodationController::class, 'destroy']);
});

// Petugas Routes (prefix: petugas, middleware: auth sanctum)
Route::middleware(['auth:sanctum', 'role:petugas'])->prefix('petugas')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Api\V1\Petugas\PetugasController::class, 'dashboard']);
    Route::get('/visits', [\App\Http\Controllers\Api\V1\Petugas\PetugasController::class, 'visits']);
    Route::get('/bookings', [\App\Http\Controllers\Api\V1\Petugas\PetugasController::class, 'bookings']);
    Route::get('/users', [\App\Http\Controllers\Api\V1\Petugas\PetugasController::class, 'users']);
});

// Auth Endpoints
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::patch('/me', [AuthController::class, 'updateProfile']);
    });
});

// Orders Endpoints (Sanctum protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order_code}', [OrderController::class, 'show']);

    Route::middleware('role:petugas')->group(function () {
        Route::post('/scan', [\App\Http\Controllers\Api\V1\ScannerController::class, 'verify']);
        Route::get('/scan/history', [\App\Http\Controllers\Api\V1\ScannerController::class, 'history']);
    });

    // Accommodation Bookings
    Route::get('/accommodation-bookings', [AccommodationBookingController::class, 'index']);
    Route::post('/accommodation-bookings', [AccommodationBookingController::class, 'store']);

    // Notifications (all authenticated roles)
    Route::get('/notifications', [\App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\V1\NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/read-all', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markAllAsRead']);
    Route::patch('/notifications/{id}/read', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [\App\Http\Controllers\Api\V1\NotificationController::class, 'destroy']);
});

// Xendit Webhook
Route::post('/payments/xendit/webhook', [App\Http\Controllers\Api\V1\XenditWebhookController::class, 'handleWebhook']);
Route::post('/webhook', [App\Http\Controllers\Api\V1\XenditWebhookController::class, 'handleWebhook']); // Fallback alias
