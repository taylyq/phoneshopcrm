<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
header('X-Content-Type-Options: nosniff');

echo 'PHP_VERSION=' . PHP_VERSION . PHP_EOL;
echo 'PHP_SAPI=' . PHP_SAPI . PHP_EOL;
echo 'PDO=' . (extension_loaded('pdo') ? 'loaded' : 'missing') . PHP_EOL;
echo 'PDO_MYSQL=' . (extension_loaded('pdo_mysql') ? 'loaded' : 'missing') . PHP_EOL;
echo 'PDO_SQLITE=' . (extension_loaded('pdo_sqlite') ? 'loaded' : 'missing') . PHP_EOL;
echo 'DOCUMENT_ROOT=' . ($_SERVER['DOCUMENT_ROOT'] ?? '') . PHP_EOL;
echo 'SCRIPT_FILENAME=' . ($_SERVER['SCRIPT_FILENAME'] ?? '') . PHP_EOL;
