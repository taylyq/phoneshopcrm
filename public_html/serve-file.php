<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\UploadService;

$type = UploadService::sanitizeType((string) ($_GET['type'] ?? ''));
$file = UploadService::sanitizeFilename((string) ($_GET['file'] ?? ''));

if (!$type || !$file || !in_array($type, (array) config('uploads.types', []), true)) {
    http_response_code(403);
    exit;
}

$base = realpath(upload_base_path());
if (!$base) {
    http_response_code(404);
    exit;
}

$candidate = $base . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $file;
$resolved = realpath($candidate);
$basePrefix = rtrim(str_replace('\\', '/', $base), '/') . '/';
$resolvedPath = $resolved ? str_replace('\\', '/', $resolved) : '';

if (!$resolved || !str_starts_with($resolvedPath, $basePrefix)) {
    http_response_code(403);
    exit;
}

if (!is_file($resolved) || !is_readable($resolved)) {
    http_response_code(404);
    exit;
}

$mime = mime_content_type($resolved) ?: 'application/octet-stream';
$allowed = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif',
    'application/pdf',
    'text/plain',
];

if (!in_array($mime, $allowed, true)) {
    http_response_code(403);
    exit;
}

header('X-Content-Type-Options: nosniff');
header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) filesize($resolved));
header('Cache-Control: private, max-age=3600');
readfile($resolved);
