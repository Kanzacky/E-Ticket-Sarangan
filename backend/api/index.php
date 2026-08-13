<?php

// Vercel PHP entrypoint. Keep application logic in Laravel; this file only
// forwards the serverless request to Laravel's standard public/index.php.
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../public/index.php';
$_SERVER['DOCUMENT_ROOT'] = __DIR__.'/../public';

chdir(__DIR__.'/..');

require __DIR__.'/../public/index.php';
