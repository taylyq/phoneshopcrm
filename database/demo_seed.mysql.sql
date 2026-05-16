START TRANSACTION;

DELETE FROM repair_tickets
WHERE phone IN ('demo-555-0101', 'demo-555-0102', 'demo-555-0103', 'demo-555-0104');

INSERT INTO repair_tickets
    (customer_name, phone, device_model, serial_number, issue_summary, status, created_at)
VALUES
    ('Ava Nguyen', 'demo-555-0101', 'iPhone 14 Pro', 'DEMO-IP14-001', 'Front glass cracked after a drop. Touch input still works.', 'intake', NOW()),
    ('Marcus Lee', 'demo-555-0102', 'Samsung Galaxy S23', 'DEMO-S23-002', 'Rear camera lens is foggy and autofocus fails intermittently.', 'diagnosing', NOW()),
    ('Priya Shah', 'demo-555-0103', 'Google Pixel 8', 'DEMO-PX8-003', 'Battery drains from 80% to 20% within two hours.', 'waiting_parts', NOW()),
    ('Daniel Carter', 'demo-555-0104', 'OnePlus 11', 'DEMO-OP11-004', 'USB-C port is loose and only charges at a specific angle.', 'ready', NOW());

INSERT INTO attachments (ticket_id, label, type, relative_path, original_name, created_at)
SELECT id, 'Device image', 'images', 'images/demo-iphone-screen.png', 'demo-iphone-screen.png', NOW()
FROM repair_tickets
WHERE phone = 'demo-555-0101';

INSERT INTO attachments (ticket_id, label, type, relative_path, original_name, created_at)
SELECT id, 'Customer document', 'documents', 'documents/demo-intake-checklist.txt', 'demo-intake-checklist.txt', NOW()
FROM repair_tickets
WHERE phone = 'demo-555-0101';

INSERT INTO attachments (ticket_id, label, type, relative_path, original_name, created_at)
SELECT id, 'Device image', 'images', 'images/demo-samsung-camera.png', 'demo-samsung-camera.png', NOW()
FROM repair_tickets
WHERE phone = 'demo-555-0102';

INSERT INTO attachments (ticket_id, label, type, relative_path, original_name, created_at)
SELECT id, 'Warranty note', 'documents', 'documents/demo-warranty-note.txt', 'demo-warranty-note.txt', NOW()
FROM repair_tickets
WHERE phone = 'demo-555-0103';

COMMIT;
