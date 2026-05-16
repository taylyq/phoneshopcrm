<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$snapshot = diagnostics_snapshot($GLOBALS['config'], $GLOBALS['env_path'] ?? null, $GLOBALS['public_root'], $GLOBALS['pdo_error'] ?? 'OK');
$folders = [];

foreach ((array) config('uploads.types', []) as $type) {
    $path = upload_base_path() . $type;
    $folders[$type] = [
        'path' => $path,
        'exists' => is_dir($path) ? 'Yes' : 'No',
        'writable' => is_dir($path) && is_writable($path) ? 'Yes' : 'No',
    ];
}

view('diagnostics', [
    'title' => 'Deployment diagnostics',
    'message' => 'Safe configuration snapshot. Password values are never displayed.',
    'details' => array_merge($snapshot, ['db_connected' => isset($GLOBALS['pdo']) ? 'Yes' : 'No']),
    'folders' => $folders,
]);
