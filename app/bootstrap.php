<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $path = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

require __DIR__ . '/helpers.php';

$rootPath = dirname(__DIR__);
$publicRoot = $rootPath;
$domainRoot = dirname($publicRoot);
$accountRoot = dirname($rootPath);

$envCandidates = array_filter([
    getenv('PHONESHOPCRM_ENV_PATH') ?: null,
    $accountRoot . '/phoneshopcrm.env',
    $accountRoot . '/.env',
    $domainRoot . '/phoneshopcrm.env',
    $domainRoot . '/.env',
    $rootPath . '/.env',
]);

$envPath = null;
foreach ($envCandidates as $candidate) {
    if (is_file($candidate)) {
        $envPath = $candidate;
        load_env($candidate);
        break;
    }
}

$config = require $rootPath . '/config.php';
$GLOBALS['config'] = $config;
$GLOBALS['env_path'] = $envPath;
$GLOBALS['root_path'] = $rootPath;
$GLOBALS['public_root'] = $publicRoot;

try {
    $GLOBALS['pdo'] = App\Database::connect($config['db']);
} catch (Throwable $exception) {
    $GLOBALS['pdo_error'] = safe_db_error($exception);
    $GLOBALS['pdo_setup'] = diagnostics_snapshot($config, $envPath, $publicRoot, $GLOBALS['pdo_error']);
    safe_log('database-error.log', $exception->getMessage());
}
