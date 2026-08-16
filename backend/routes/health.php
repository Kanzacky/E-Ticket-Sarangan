<?php

use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

// Public endpoints — registered OUTSIDE the web/api middleware groups
// (via withRouting then:), sehingga tidak bergantung pada session, cookie,
// Blade view, atau APP_KEY. Header CORS tetap dipasang oleh HandleCors
// (global middleware) untuk semua path di bawah `api/*`.

// Root endpoint — JSON sederhana.
Route::get('/', HealthController::class.'@root');

// Health check lengkap (status aplikasi + koneksi database).
// Bentuk response mengikuti kontrak frontend:
//   { success, message, data: { status, app, version, database } }
Route::get('/api/health', HealthController::class);

// Cek koneksi database saja (dipisah agar health check tetap ringan).
Route::get('/api/health/database', HealthController::class.'@database');