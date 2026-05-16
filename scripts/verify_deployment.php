<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failed = false;

function check_line(bool $ok, string $message): void
{
    global $failed;
    echo ($ok ? '[OK] ' : '[FAIL] ') . $message . PHP_EOL;
    if (!$ok) {
        $failed = true;
    }
}

$phpFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($phpFiles as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    $command = 'php -l ' . escapeshellarg($file->getPathname());
    exec($command, $output, $code);
    check_line($code === 0, 'PHP syntax: ' . str_replace($root . '/', '', $file->getPathname()));
}

require $root . '/app/bootstrap.php';

$publicUploadPaths = [
    $root . '/uploads',
    $root . '/assets/uploads',
    $root . '/assets/img/uploads',
    $root . '/public_html',
];

foreach ($publicUploadPaths as $path) {
    check_line(!is_dir($path), 'No public upload directory: ' . str_replace($root . '/', '', $path));
}

foreach ((array) config('uploads.types', []) as $type) {
    $path = upload_base_path() . $type;
    check_line(is_dir($path), 'External upload folder exists: ' . $path);
    check_line(is_dir($path) && is_writable($path), 'External upload folder writable: ' . $path);
}

$uploadService = file_get_contents($root . '/app/UploadService.php') ?: '';
$serveFile = file_get_contents($root . '/serve-file.php') ?: '';

check_line(str_contains($uploadService, 'move_uploaded_file('), 'Uploads use move_uploaded_file().');
check_line(str_contains($serveFile, 'readfile(') && str_contains($serveFile, 'realpath('), 'serve-file.php streams files with path whitelist checks.');
check_line(!is_file($root . '/.env'), 'Root .env is not present for Git deployment.');

exit($failed ? 1 : 0);
