<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

if (isset($GLOBALS['pdo_error'])) {
    http_response_code(503);
    view('error', [
        'title' => 'Database setup needed',
        'message' => 'The database is not ready. Open diagnostics for safe setup details.',
        'details' => $GLOBALS['pdo_setup'] ?? [],
    ]);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    verify_csrf();
}

$routes = require __DIR__ . '/app/routes.php';
$handler = $routes[$method][$path] ?? null;

if (!$handler) {
    http_response_code(404);
    view('error', ['title' => 'Page not found', 'message' => 'The requested page does not exist.']);
    exit;
}

[$controller, $action] = $handler;
$class = 'App\\Controllers\\' . $controller;

try {
    (new $class())->$action();
} catch (Throwable $exception) {
    safe_log('runtime-error.log', $exception->getMessage());
    http_response_code(500);
    view('error', [
        'title' => 'Application setup needed',
        'message' => 'The app hit a setup issue. Check /diagnostics.php and storage/runtime-error.log for the safe error trail.',
        'details' => [
            'safe_reason' => safe_db_error($exception),
            'db_driver' => db_driver(),
            'db_name' => (string) config('db.database', ''),
            'upload_base' => upload_base_path(),
        ],
    ]);
}
