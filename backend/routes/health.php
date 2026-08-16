<?php

use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

// Public endpoints — registered OUTSIDE the web/api middleware groups
// (via withRouting then:), so they never start a session, encrypt cookies,
// or depend on APP_KEY. This makes them reliable even if the web group
// (Blade + session) is unavailable.

// Root endpoint — returns JSON instead of the Blade welcome page, which
// previously rendered inside the `web` middleware group and produced HTTP 500.
Route::get('/', [HealthController::class, 'root']);

Route::get('/api/health', [HealthController::class, 'app']);

Route::get('/api/health/database', [HealthController::class, 'database']);
