<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

if (db_driver() !== 'sqlite') {
    fwrite(STDERR, "DB_DRIVER is not sqlite. Refusing to initialize a non-SQLite database.\n");
    exit(1);
}

$schema = file_get_contents(__DIR__ . '/schema.sqlite.sql');
if ($schema === false) {
    fwrite(STDERR, "Could not read SQLite schema.\n");
    exit(1);
}

db()->exec($schema);
echo "SQLite database initialized at " . config('db.path') . PHP_EOL;
