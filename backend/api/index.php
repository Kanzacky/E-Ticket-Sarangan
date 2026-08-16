<?php

// Vercel PHP entrypoint. Keep application logic in Laravel; this file only
// forwards the serverless request to Laravel's standard public/index.php.
//
// Catatan penting: TIDAK ada intercept khusus path di sini. Semua request
// (termasuk /api/health, /api/health/database, /api/auth/*, dll.) diteruskan
// ke Laravel agar middleware global HandleCors menerapkan header CORS dan
// menangani preflight OPTIONS secara konsisten di semua endpoint.
// Intercept lama di sini menyebabkan /api/health di-echo mentah TANPA
// Access-Control-Allow-Origin sehingga browser memblokir response (CORS error).

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../public/index.php';
$_SERVER['DOCUMENT_ROOT'] = __DIR__.'/../public';

chdir(__DIR__.'/..');

require __DIR__.'/../public/index.php';