<?php

use Illuminate\Support\Facades\Route;

// Semua route public (termasuk `/` dan health check) didaftarkan di
// routes/health.php (via withRouting `then:`), di luar grup middleware
// web/api, agar tidak bergantung pada session/cookie/Blade view.