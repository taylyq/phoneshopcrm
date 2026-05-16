<section class="hero hero-centered">
    <div class="hero-copy">
        <p class="eyebrow">Repair OS</p>
        <h1>Seamlessly connected to your repair workflow</h1>
        <p class="hero-lede">Intake, device notes, private documents, and repair status in one lightweight Hostinger-ready dashboard.</p>
    </div>

    <div class="ecosystem" aria-hidden="true">
        <span class="node node-small">IMEI</span>
        <span class="node">Parts</span>
        <span class="node node-core">CORE - active intake</span>
        <span class="node">Files</span>
        <span class="node node-small">Done</span>
        <span class="node node-dot">+</span>
    </div>

    <div class="hero-meta">
        <strong><?= count($tickets) ?> repair tickets. Secure uploads. Private file delivery.</strong>
        <span>Built for small phone shops that need fast intake without exposing customer files.</span>
    </div>
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
