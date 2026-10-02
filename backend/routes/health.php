<?php

use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\Facades\Route;

// Health routes are registered OUTSIDE the api middleware group (via withRouting then:).
// HandleCors must be added explicitly so CORS headers are present on all responses,
// including error responses. Without this, a 500 here looks like a CORS error in browser.

Route::middleware([HandleCors::class])->group(function () {
    // Root endpoint — simple JSON ping.
    Route::get('/', HealthController::class.'@root');

    // Full health check (app status + database connection).
    Route::get('/api/health', HealthController::class);

    // Database-only check.
    Route::get('/api/health/database', HealthController::class.'@database');
});