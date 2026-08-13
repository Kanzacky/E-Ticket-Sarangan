<?php

// Vercel PHP entrypoint. Keep application logic in Laravel; this file only
// forwards the serverless request to Laravel's standard public/index.php.

// Intercept /api/health before Laravel bootstrap to avoid DB/cache issues
if ($_SERVER['REQUEST_URI'] === '/api/health' || $_SERVER['REQUEST_URI'] === '/api/health/') {
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => 'API e-Ticket Sarangan aktif'
    ]);
    exit;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../public/index.php';
$_SERVER['DOCUMENT_ROOT'] = __DIR__.'/../public';

chdir(__DIR__.'/..');

require __DIR__.'/../public/index.php';
