<?php
require __DIR__ . '/includes/auth.php';
require_admin();

$schema = settings_schema();
$group = (string) ($_GET['group'] ?? array_key_first($schema));
if (!isset($schema[$group])) {
    $group = array_key_first($schema);
}
$fields = $schema[$group]['fields'];
$self = admin_url('settings.php?group=' . rawurlencode($group));

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf($self);
    $current = settings_all();
    $updates = [];
    $replaced = [];

    foreach ($fields as $key => $field) {
        $type = $field['type'] ?? 'text';
        if ($type === 'image' || $type === 'video') {
            if (!empty($_POST['reset'][$key])) {
                $updates[$key] = $field['default'];
                $replaced[] = $current[$key];
                continue;
            }
            try {
                if ($path = handle_upload('file_' . $key, $type)) {
                    $updates[$key] = $path;
                    $replaced[] = $current[$key];
                }
            } catch (RuntimeException $ex) {
                $errors[] = $field['label'] . ': ' . $ex->getMessage();
            }
            continue;
        }
        $value = trim((string) ($_POST['s'][$key] ?? ''));
        if ($type === 'rich') {
            $value = rich($value);
        }
        if ($type === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = $field['label'] . ': please enter a valid email address.';
            continue;
        }
        $updates[$key] = $value;
    }

    if ($errors) {
        // Keep nothing from a failed save, including files that did upload.
        foreach ($fields as $key => $field) {
            if (in_array($field['type'] ?? 'text', ['image', 'video'], true) && isset($updates[$key]) && $updates[$key] !== $field['default']) {
                delete_upload_if_unused($updates[$key]);
            }
        }
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        foreach ($updates as $key => $value) {
            save_setting($key, $value);
        }
        $pdo->commit();
        foreach ($replaced as $old) {
            delete_upload_if_unused($old);
        }
        flash('success', $schema[$group]['label'] . ' saved.');
        redirect($self);
    }
}

$values = settings_all(true);
if ($errors) {
    foreach ($fields as $key => $field) {
        if (isset($_POST['s'][$key])) {
            $values[$key] = (string) $_POST['s'][$key];
        }
    }
}

$adminTitle = 'Site Settings';
$adminPage = 'settings';
require __DIR__ . '/includes/layout-top.php';
?>
<div class="toolbar">
    <div class="tabs">
        <?php foreach ($schema as $key => $g): ?>
            <a href="<?= e(admin_url('settings.php?group=' . rawurlencode($key))) ?>" class="<?= $group === $key ? 'active' : '' ?>"><?= e($g['label']) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-error" role="alert"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="panel settings-form">
    <?= csrf_field() ?>
    <div class="panel-head">
        <h3><?= e($schema[$group]['label']) ?></h3>
        <?php if (array_filter($fields, function ($f) { return ($f['type'] ?? '') === 'rich'; })): ?>
            <span class="muted small">Headings can use &lt;em&gt;italic gold&lt;/em&gt; and &lt;br&gt; for a line break.</span>
        <?php endif; ?>
    </div>
    <div class="settings-grid">
        <?php foreach ($fields as $key => $field):
            $type = $field['type'] ?? 'text';
            $value = $values[$key] ?? '';
            $wide = in_array($type, ['textarea', 'image', 'video'], true);
            ?>
            <?php if ($type === 'image' || $type === 'video'): ?>
                <div class="field image-field <?= $wide ? 'span-2' : '' ?>">
                    <span><?= e($field['label']) ?></span>
                    <div class="image-picker">
                        <?php if ($type === 'image'): ?>
                            <img src="<?= e(asset($value)) ?>" alt="" class="preview <?= $value === '' ? 'is-empty' : '' ?>" data-preview="file_<?= e($key) ?>">
                        <?php elseif ($value !== ''): ?>
                            <video src="<?= e(asset($value)) ?>" class="preview" muted controls preload="metadata"></video>
                        <?php endif; ?>
                        <div class="picker-controls">
                            <input type="file" name="file_<?= e($key) ?>" accept="<?= $type === 'video' ? 'video/mp4,video/webm' : 'image/jpeg,image/png,image/webp,image/gif' ?>">
                            <?php if ($value !== $field['default']): ?>
                                <label class="switch small"><input type="checkbox" name="reset[<?= e($key) ?>]" value="1"><span>Restore original</span></label>
                            <?php endif; ?>
                            <?php if ($type === 'video'): ?><small class="help">MP4, up to <?= (int) config('max_video_mb', 60) ?> MB (server limit: <?= e(ini_get('upload_max_filesize')) ?>).</small><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <label class="field <?= $wide ? 'span-2' : '' ?>">
                    <span><?= e($field['label']) ?></span>
                    <?php if ($type === 'textarea'): ?>
                        <textarea name="s[<?= e($key) ?>]" rows="<?= substr_count($value, "\n") > 2 ? 8 : 3 ?>"><?= e($value) ?></textarea>
                    <?php else: ?>
                        <input type="<?= $type === 'email' ? 'email' : 'text' ?>" name="s[<?= e($key) ?>]" value="<?= e($value) ?>">
                    <?php endif; ?>
                    <?php if (!empty($field['help'])): ?><small class="help"><?= e($field['help']) ?></small><?php endif; ?>
                </label>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div class="actions-row sticky-actions">
        <button type="submit" class="btn btn-primary">Save <?= e(strtolower($schema[$group]['label'])) ?></button>
        <a href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener" class="btn btn-secondary">Preview site &#8599;</a>
    </div>
</form>
<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
