<section class="panel narrow">
    <h1><?= e($title ?? 'Error') ?></h1>
    <p><?= e($message ?? 'Something went wrong.') ?></p>

    <?php if (!empty($details)): ?>
        <dl class="diagnostics">
            <?php foreach ($details as $key => $value): ?>
                <div><dt><?= e(ucwords(str_replace('_', ' ', (string) $key))) ?></dt><dd><?= e((string) $value) ?></dd></div>
            <?php endforeach; ?>
        </dl>
    <?php endif; ?>

    <?php if (!empty($showPinForm)): ?>
        <form method="get" class="form-grid">
            <label>
                Admin PIN
                <input type="password" name="admin_pin" required>
            </label>
            <button type="submit">Unlock</button>
        </form>
    <?php endif; ?>
</section>
