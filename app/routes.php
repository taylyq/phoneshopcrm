<?php

declare(strict_types=1);

return [
    'GET' => [
        '/' => ['TicketController', 'index'],
        '/tickets' => ['TicketController', 'index'],
        '/tickets/new' => ['TicketController', 'create'],
        '/health' => ['SystemController', 'health'],
    ],
    'POST' => [
        '/tickets' => ['TicketController', 'store'],
    ],
];
