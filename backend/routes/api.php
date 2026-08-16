<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

// Prefix grup: `api` (dari withRouting apiPrefix) → /api/auth/*.
// Health endpoints (/api/health, /api/health/database) didaftarkan di
// routes/health.php (tanpa middleware grup web/api).
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});