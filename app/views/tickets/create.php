<section class="panel narrow">
    <div class="section-title">
        <h1>New repair ticket</h1>
        <a href="/tickets">Back</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/tickets" enctype="multipart/form-data" class="form-grid">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

        <label>
            Customer name
            <input name="customer_name" value="<?= e($old['customer_name'] ?? '') ?>" required>
        </label>

        <label>
            Phone
            <input name="phone" value="<?= e($old['phone'] ?? '') ?>" required>
        </label>

        <label>
            Device model
            <input name="device_model" value="<?= e($old['device_model'] ?? '') ?>" required>
        </label>

        <label>
            Serial / IMEI
            <input name="serial_number" value="<?= e($old['serial_number'] ?? '') ?>">
        </label>

        <label>
            Status
            <select name="status">
                <?php foreach (['intake', 'diagnosing', 'waiting_parts', 'ready', 'closed'] as $status): ?>
                    <option value="<?= e($status) ?>"><?= e(ucwords(str_replace('_', ' ', $status))) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="span-2">
            Issue summary
            <textarea name="issue_summary" rows="5" required><?= e($old['issue_summary'] ?? '') ?></textarea>
        </label>

        <label>
            Device image
            <input type="file" name="device_image" accept="image/jpeg,image/png,image/webp,image/gif">
        </label>

        <label>
            Document
            <input type="file" name="document" accept="application/pdf,image/jpeg,image/png,image/webp,text/plain">
        </label>

        <button type="submit">Save ticket</button>
    </form>
</section>
