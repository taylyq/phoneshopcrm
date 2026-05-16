<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

if (!isset($GLOBALS['pdo'])) {
    fwrite(STDERR, 'Database is not connected: ' . ($GLOBALS['pdo_error'] ?? 'unknown error') . PHP_EOL);
    exit(1);
}

$schema = db_driver() === 'mysql'
    ? dirname(__DIR__) . '/database/schema.mysql.sql'
    : dirname(__DIR__) . '/database/schema.sqlite.sql';

$schemaSql = file_get_contents($schema);
if ($schemaSql === false) {
    fwrite(STDERR, 'Could not read schema file.' . PHP_EOL);
    exit(1);
}

db()->exec($schemaSql);

foreach ((array) config('uploads.types', []) as $type) {
    $path = upload_base_path() . $type;
    if (!is_dir($path) && !mkdir($path, 0775, true)) {
        fwrite(STDERR, 'Could not create upload folder: ' . $path . PHP_EOL);
        exit(1);
    }
    if (!is_writable($path)) {
        fwrite(STDERR, 'Upload folder is not writable: ' . $path . PHP_EOL);
        exit(1);
    }
}

$demoImage = upload_base_path() . 'images/demo-iphone-screen.png';
$demoImage2 = upload_base_path() . 'images/demo-samsung-camera.png';
$demoDoc = upload_base_path() . 'documents/demo-intake-checklist.txt';
$demoDoc2 = upload_base_path() . 'documents/demo-warranty-note.txt';

write_demo_png($demoImage);
write_demo_png($demoImage2);
file_put_contents($demoDoc, "Demo intake checklist\n- Confirm customer phone number\n- Photograph device\n- Record lock code separately\n");
file_put_contents($demoDoc2, "Demo warranty note\nBattery repair includes 90-day workmanship warranty.\n");

db()->beginTransaction();

try {
    $demoPhones = ['demo-555-0101', 'demo-555-0102', 'demo-555-0103', 'demo-555-0104'];
    $placeholders = implode(',', array_fill(0, count($demoPhones), '?'));
    $deleteTickets = db()->prepare("DELETE FROM repair_tickets WHERE phone IN ($placeholders)");
    $deleteTickets->execute($demoPhones);

    $tickets = [
        [
            'customer_name' => 'Ava Nguyen',
            'phone' => 'demo-555-0101',
            'device_model' => 'iPhone 14 Pro',
            'serial_number' => 'DEMO-IP14-001',
            'issue_summary' => 'Front glass cracked after a drop. Touch input still works.',
            'status' => 'intake',
            'attachments' => [
                ['Device image', 'images', 'images/demo-iphone-screen.png', 'demo-iphone-screen.png'],
                ['Customer document', 'documents', 'documents/demo-intake-checklist.txt', 'demo-intake-checklist.txt'],
            ],
        ],
        [
            'customer_name' => 'Marcus Lee',
            'phone' => 'demo-555-0102',
            'device_model' => 'Samsung Galaxy S23',
            'serial_number' => 'DEMO-S23-002',
            'issue_summary' => 'Rear camera lens is foggy and autofocus fails intermittently.',
            'status' => 'diagnosing',
            'attachments' => [
                ['Device image', 'images', 'images/demo-samsung-camera.png', 'demo-samsung-camera.png'],
            ],
        ],
        [
            'customer_name' => 'Priya Shah',
            'phone' => 'demo-555-0103',
            'device_model' => 'Google Pixel 8',
            'serial_number' => 'DEMO-PX8-003',
            'issue_summary' => 'Battery drains from 80% to 20% within two hours.',
            'status' => 'waiting_parts',
            'attachments' => [
                ['Warranty note', 'documents', 'documents/demo-warranty-note.txt', 'demo-warranty-note.txt'],
            ],
        ],
        [
            'customer_name' => 'Daniel Carter',
            'phone' => 'demo-555-0104',
            'device_model' => 'OnePlus 11',
            'serial_number' => 'DEMO-OP11-004',
            'issue_summary' => 'USB-C port is loose and only charges at a specific angle.',
            'status' => 'ready',
            'attachments' => [],
        ],
    ];

    $ticketStatement = db()->prepare(
        'INSERT INTO repair_tickets
            (customer_name, phone, device_model, serial_number, issue_summary, status, created_at)
         VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)'
    );
    $attachmentStatement = db()->prepare(
        'INSERT INTO attachments (ticket_id, label, type, relative_path, original_name, created_at)
         VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)'
    );

    foreach ($tickets as $ticket) {
        $ticketStatement->execute([
            $ticket['customer_name'],
            $ticket['phone'],
            $ticket['device_model'],
            $ticket['serial_number'],
            $ticket['issue_summary'],
            $ticket['status'],
        ]);
        $ticketId = (int) db()->lastInsertId();

        foreach ($ticket['attachments'] as $attachment) {
            $attachmentStatement->execute([
                $ticketId,
                $attachment[0],
                $attachment[1],
                $attachment[2],
                $attachment[3],
            ]);
        }
    }

    db()->commit();
} catch (Throwable $exception) {
    db()->rollBack();
    fwrite(STDERR, 'Demo seed failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

echo 'Demo data created for ' . db_driver() . ' database.' . PHP_EOL;
echo 'Upload base: ' . upload_base_path() . PHP_EOL;
echo 'Demo tickets: 4' . PHP_EOL;

function write_demo_png(string $path): void
{
    $png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=';
    file_put_contents($path, base64_decode($png, true));
}
