CREATE TABLE IF NOT EXISTS repair_tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(160) NOT NULL,
    phone VARCHAR(80) NOT NULL,
    device_model VARCHAR(160) NOT NULL,
    serial_number VARCHAR(160) NULL,
    issue_summary TEXT NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'intake',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attachments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    label VARCHAR(120) NOT NULL,
    type VARCHAR(40) NOT NULL,
    relative_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT attachments_ticket_id_fk FOREIGN KEY (ticket_id)
        REFERENCES repair_tickets(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
