<?php

use Illuminate\Support\Facades\Route;

// Route::view (bukan closure) agar kompatibel dengan `php artisan route:cache`.
Route::view('/', 'welcome');
