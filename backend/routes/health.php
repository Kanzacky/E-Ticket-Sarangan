<?php

use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

// Public health endpoints — registered OUTSIDE the web/api middleware
// groups (via withRouting then:), so they never start a session or hit
// the database implicitly.

Route::get('/api/health', [HealthController::class, 'app']);

Route::get('/api/health/database', [HealthController::class, 'database']);
