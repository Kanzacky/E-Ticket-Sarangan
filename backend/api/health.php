<?php
// Minimal health endpoint to verify PHP runtime on Vercel
http_response_code(200);
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'message' => 'Runtime OK'
]);
