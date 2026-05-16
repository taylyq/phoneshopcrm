<?php

declare(strict_types=1);

namespace App\Controllers;

final class SystemController
{
    public function health(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['ok' => isset($GLOBALS['pdo']), 'app' => \config('app_name')]);
    }
}
