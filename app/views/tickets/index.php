<section class="hero">
    <div>
        <p class="eyebrow">Repair intake</p>
        <h1>Phone repair workbench</h1>
        <p>Track customers, devices, intake notes, and private upload-backed attachments from one small Hostinger-friendly PHP app.</p>
    </div>
    <a class="button" href="/tickets/new">Create ticket</a>
</section>

<section class="panel">
    <div class="section-title">
        <h2>Open tickets</h2>
        <span><?= count($tickets) ?> total</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Device</th>
                    <th>Issue</th>
                    <th>Status</th>
                    <th>Files</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>
                            <strong><?= e($ticket['customer_name']) ?></strong>
                            <span><?= e($ticket['phone']) ?></span>
                        </td>
                        <td>
                            <?= e($ticket['device_model']) ?>
                            <?php if (!empty($ticket['serial_number'])): ?><span><?= e($ticket['serial_number']) ?></span><?php endif; ?>
                        </td>
                        <td><?= e($ticket['issue_summary']) ?></td>
                        <td><span class="status"><?= e($ticket['status']) ?></span></td>
                        <td><?= (int) $ticket['attachment_count'] ?></td>
                        <td><?= e($ticket['created_at']) ?></td>
                    </tr>
                    <?php
                    $attachments = db()->prepare('SELECT * FROM attachments WHERE ticket_id = ? ORDER BY id');
                    $attachments->execute([$ticket['id']]);
                    $files = $attachments->fetchAll();
                    ?>
                    <?php if ($files): ?>
                        <tr class="files-row">
                            <td colspan="6">
                                <?php foreach ($files as $file): ?>
                                    <?php $url = upload_relative_url($file['relative_path']); ?>
                                    <a class="file-link" href="<?= e($url) ?>" target="_blank" rel="noopener"><?= e($file['label']) ?>: <?= e($file['original_name']) ?></a>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (!$tickets): ?>
                    <tr><td colspan="6" class="empty">No repair tickets yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
