<?php

declare(strict_types=1);

?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'Dashboard') . ' - ' . config('app_name')) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="/assets/js/app.js" defer></script>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="/">phoneshop</a>
        <nav class="primary-nav">
            <a href="/tickets">Dashboard</a>
            <a href="/tickets/new">Intake</a>
            <a href="/diagnostics.php">Diagnostics</a>
        </nav>
        <div class="nav-actions">
            <span>Repair desk</span>
            <a class="nav-pill" href="/tickets/new">New ticket</a>
        </div>
    </header>
    <main class="shell">
        <?php require $viewPath; ?>
    </main>
</body>
</html>
