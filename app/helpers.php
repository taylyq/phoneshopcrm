<?php

declare(strict_types=1);

function load_env(string $path): void
{
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        if (str_starts_with($line, 'export ')) {
            $line = trim(substr($line, 7));
        }

        [$key, $value] = explode('=', $line, 2);
        $key = ltrim(trim($key), "\xEF\xBB\xBF");
        $value = trim(trim($value), "\"'");

        if (getenv($key) === false) {
            putenv($key . '=' . $value);
        }
        $_ENV[$key] = $value;
    }
}

function env_value(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    return $_ENV[$key] ?? $default;
}

function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['config'] ?? [];
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function db(): PDO
{
    if (!isset($GLOBALS['pdo'])) {
        throw new RuntimeException($GLOBALS['pdo_error'] ?? 'Database connection unavailable.');
    }

    return $GLOBALS['pdo'];
}

function db_driver(): string
{
    return (string) config('db.driver', 'mysql');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function view(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $viewPath = dirname(__DIR__) . '/app/views/' . $name . '.php';
    require dirname(__DIR__) . '/app/views/layout.php';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = (string) ($_POST['_csrf'] ?? '');
    if ($token === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
        http_response_code(419);
        exit('Security token expired. Refresh and try again.');
    }
}

function require_admin_pin(): void
{
    $configured = (string) config('admin_pin', '');
    if ($configured === '') {
        return;
    }

    if (!empty($_SESSION['admin_ok']) && $_SESSION['admin_ok'] === true) {
        return;
    }

    $pin = (string) ($_POST['admin_pin'] ?? $_GET['admin_pin'] ?? '');
    if ($pin !== '' && hash_equals($configured, $pin)) {
        $_SESSION['admin_ok'] = true;
        return;
    }

    http_response_code(403);
    view('error', [
        'title' => 'Admin PIN required',
        'message' => 'Enter the configured admin PIN before creating or viewing repair records.',
        'showPinForm' => true,
    ]);
    exit;
}

function upload_base_path(): string
{
    return rtrim((string) config('uploads.base_path'), '/\\') . '/';
}

function upload_relative_url(?string $relativePath): ?string
{
    if (!$relativePath || !str_contains($relativePath, '/')) {
        return null;
    }

    [$type, $file] = explode('/', $relativePath, 2);

    return '/serve-file.php?type=' . rawurlencode($type) . '&file=' . rawurlencode($file);
}

function safe_db_error(Throwable $exception): string
{
    $message = $exception->getMessage();

    if (str_contains($message, 'Access denied')) {
        return 'Access denied. Check the database username, password, and database user assignment.';
    }
    if (str_contains($message, 'Unknown database')) {
        return 'Unknown database. Create the database or update DB_DATABASE.';
    }
    if (str_contains($message, 'could not find driver')) {
        return 'Missing database driver. Enable PDO MySQL or PDO SQLite for this PHP version.';
    }
    if (str_contains($message, 'Connection refused') || str_contains($message, 'No such file or directory')) {
        return 'Database host or port is unreachable.';
    }

    return 'Database connection failed.';
}

function env_location_label(?string $envPath, string $publicRoot): string
{
    if (!$envPath) {
        return 'Missing';
    }

    $realEnv = str_replace('\\', '/', realpath($envPath) ?: $envPath);
    $realPublic = rtrim(str_replace('\\', '/', realpath($publicRoot) ?: $publicRoot), '/') . '/';

    return str_starts_with($realEnv, $realPublic) ? 'Found inside public_html fallback' : 'Found outside public_html';
}

function diagnostics_snapshot(array $config, ?string $envPath, string $publicRoot, ?string $reason = null): array
{
    $db = $config['db'] ?? [];

    return [
        'env_file' => env_location_label($envPath, $publicRoot),
        'db_driver' => (string) ($db['driver'] ?? ''),
        'db_host' => (string) ($db['host'] ?? ''),
        'db_name' => (string) ($db['database'] ?? ''),
        'db_username' => (string) ($db['username'] ?? ''),
        'password' => !empty($db['password']) ? 'Set' : 'Missing',
        'safe_reason' => $reason ?: 'OK',
        'upload_base' => upload_base_path(),
    ];
}

function safe_log(string $filename, string $message): void
{
    $dir = dirname(__DIR__) . '/storage';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    error_log('[' . date('c') . '] ' . $message . PHP_EOL, 3, $dir . '/' . $filename);
}
