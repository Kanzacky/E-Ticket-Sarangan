<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Paths & Origins
    |--------------------------------------------------------------------------
    |
    | `allowed_origins` menerima origin spesifik (BUKAN `*`) karena
    | `supports_credentials` aktif. Origin frontend production ditambahkan
    | secara eksplisit; dapat juga dikonfigurasi via env:
    |
    |   CORS_ALLOWED_ORIGINS=https://a.vercel.app,https://b.vercel.app
    |   FRONTEND_URL=https://a.vercel.app
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_values(array_unique(array_filter(array_merge([
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'https://e-ticket-sarangan-anx4.vercel.app',
    ], preg_split('/\s*,\s*/', (string) env('CORS_ALLOWED_ORIGINS', (string) env('FRONTEND_URL', ''))) ?: [])))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'Accept', 'X-CSRF-TOKEN'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
