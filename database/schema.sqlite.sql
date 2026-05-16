PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS repair_tickets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_name TEXT NOT NULL,
    phone TEXT NOT NULL,
    device_model TEXT NOT NULL,
    serial_number TEXT NULL,
    issue_summary TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'intake',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS attachments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ticket_id INTEGER NOT NULL,
    label TEXT NOT NULL,
    type TEXT NOT NULL,
    relative_path TEXT NOT NULL,
    original_name TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES repair_tickets(id) ON DELETE CASCADE
);
