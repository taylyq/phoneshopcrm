<?php

declare(strict_types=1);

return [
    'app_name' => env_value('APP_NAME', 'PhoneShop CRM'),
    'app_env' => env_value('APP_ENV', 'production'),
    'app_url' => env_value('APP_URL', 'https://example.com'),
    'admin_pin' => env_value('ADMIN_PIN', ''),
    'uploads' => [
        'base_path' => env_value('UPLOAD_BASE_PATH', dirname(__DIR__) . '/phoneshopcrm-uploads'),
        'types' => ['images', 'teachers', 'lessons', 'documents'],
        'max_bytes' => 8 * 1024 * 1024,
    ],
    'db' => [
        'driver' => env_value('DB_DRIVER', 'mysql'),
        'path' => __DIR__ . '/' . ltrim((string) env_value('DB_PATH', 'database/local.sqlite'), '/'),
        'host' => env_value('DB_HOST', 'localhost'),
        'port' => env_value('DB_PORT', '3306'),
        'database' => env_value('DB_DATABASE', ''),
        'username' => env_value('DB_USERNAME', ''),
        'password' => env_value('DB_PASSWORD', ''),
        'charset' => env_value('DB_CHARSET', 'utf8mb4'),
    ],
];
