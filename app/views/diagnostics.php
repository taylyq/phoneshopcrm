<section class="panel narrow">
    <h1><?= e($title ?? 'Deployment diagnostics') ?></h1>
    <p><?= e($message ?? '') ?></p>

    <dl class="diagnostics">
        <?php foreach ($details as $key => $value): ?>
            <div><dt><?= e(ucwords(str_replace('_', ' ', (string) $key))) ?></dt><dd><?= e((string) $value) ?></dd></div>
        <?php endforeach; ?>
    </dl>
</section>

<section class="panel narrow diagnostics-panel">
    <h2>Upload folders</h2>
    <dl class="diagnostics">
        <?php foreach ($folders as $type => $data): ?>
            <div>
                <dt><?= e($type) ?></dt>
                <dd><?= e($data['exists'] . ', writable: ' . $data['writable']) ?></dd>
            </div>
        <?php endforeach; ?>
    </dl>
</section>
