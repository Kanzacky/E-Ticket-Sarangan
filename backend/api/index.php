<?php

// Vercel PHP entrypoint. Keep application logic in Laravel; this file only
// forwards the serverless request to Laravel's standard public/index.php.

// Intercept /api/health before Laravel bootstrap to avoid DB/cache issues.
// Vercel may send a trailing slash or query string, so normalize the path.
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$healthPath = parse_url($requestUri, PHP_URL_PATH) ?? $requestUri;
if (preg_match('#^/api/health/?$#', $healthPath) === 1) {
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode([
        'success' => true,
        'message' => 'API e-Ticket Sarangan aktif'
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../public/index.php';
$_SERVER['DOCUMENT_ROOT'] = __DIR__.'/../public';

chdir(__DIR__.'/..');

require __DIR__.'/../public/index.php';
