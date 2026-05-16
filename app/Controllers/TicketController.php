<?php

declare(strict_types=1);

namespace App\Controllers;

use App\UploadService;
use Throwable;

final class TicketController
{
    public function index(): void
    {
        \require_admin_pin();
        \ensure_app_schema();

        $tickets = \db()->query(
            'SELECT t.*, COUNT(a.id) AS attachment_count
             FROM repair_tickets t
             LEFT JOIN attachments a ON a.ticket_id = t.id
             GROUP BY t.id
             ORDER BY t.created_at DESC'
        )->fetchAll();

        \view('tickets/index', ['title' => 'Repair tickets', 'tickets' => $tickets]);
    }

    public function create(array $errors = [], array $old = []): void
    {
        \require_admin_pin();
        \ensure_app_schema();

        \view('tickets/create', ['title' => 'New repair ticket', 'errors' => $errors, 'old' => $old]);
    }

    public function store(): void
    {
        \require_admin_pin();
        \ensure_app_schema();

        $data = [
            'customer_name' => trim((string) ($_POST['customer_name'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'device_model' => trim((string) ($_POST['device_model'] ?? '')),
            'serial_number' => trim((string) ($_POST['serial_number'] ?? '')),
            'issue_summary' => trim((string) ($_POST['issue_summary'] ?? '')),
            'status' => trim((string) ($_POST['status'] ?? 'intake')),
        ];

        $errors = [];
        foreach (['customer_name', 'phone', 'device_model', 'issue_summary'] as $field) {
            if ($data[$field] === '') {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }

        if ($errors) {
            $this->create($errors, $data);
            return;
        }

        try {
            \db()->beginTransaction();
            $statement = \db()->prepare(
                'INSERT INTO repair_tickets
                    (customer_name, phone, device_model, serial_number, issue_summary, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)'
            );
            $statement->execute([
                $data['customer_name'],
                $data['phone'],
                $data['device_model'],
                $data['serial_number'],
                $data['issue_summary'],
                $data['status'],
            ]);
            $ticketId = (int) \db()->lastInsertId();

            $uploader = new UploadService();
            $this->storeAttachment($ticketId, $uploader, $_FILES['device_image'] ?? null, 'images', 'Device image');
            $this->storeAttachment($ticketId, $uploader, $_FILES['document'] ?? null, 'documents', 'Customer document');

            \db()->commit();
        } catch (Throwable $exception) {
            if (\db()->inTransaction()) {
                \db()->rollBack();
            }
            \safe_log('upload-error.log', $exception->getMessage());
            $this->create(['Ticket could not be saved: ' . $exception->getMessage()], $data);
            return;
        }

        \redirect('/tickets');
    }

    private function storeAttachment(int $ticketId, UploadService $uploader, ?array $file, string $type, string $label): void
    {
        if (!$file) {
            return;
        }

        $relativePath = $uploader->store($file, $type);
        if (!$relativePath) {
            return;
        }

        $statement = \db()->prepare(
            'INSERT INTO attachments (ticket_id, label, type, relative_path, original_name, created_at)
             VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)'
        );
        $statement->execute([
            $ticketId,
            $label,
            $type,
            $relativePath,
            (string) ($file['name'] ?? ''),
        ]);
    }
}
