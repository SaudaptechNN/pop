<?php
require __DIR__ . '/includes/auth.php';
require_admin();

$pageOptions = gallery_page_options();
$filter = (string) ($_GET['page_key'] ?? '');
if ($filter !== '' && !isset($pageOptions[$filter])) {
    $filter = '';
}
$self = admin_url('gallery.php' . ($filter !== '' ? '?page_key=' . rawurlencode($filter) : ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf($self);
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'upload') {
        $pageKey = (string) ($_POST['page_key'] ?? 'home');
        if (!isset($pageOptions[$pageKey])) {
            $pageKey = 'home';
        }
        $caption = mb_substr(trim((string) ($_POST['caption'] ?? '')), 0, 255);
        $files = $_FILES['photos'] ?? null;
        $saved = 0;
        $failed = [];
        if ($files && is_array($files['name'])) {
            $next = db()->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM gallery_images WHERE page_key = ?');
            $next->execute([$pageKey]);
            $order = (int) $next->fetchColumn();
            $insert = db()->prepare('INSERT INTO gallery_images (page_key, image, caption, sort_order) VALUES (?, ?, ?, ?)');
            foreach ($files['name'] as $i => $name) {
                // Feed each file through the single-file upload helper.
                $_FILES['_one'] = ['name' => $name, 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
                try {
                    if ($path = handle_upload('_one')) {
                        $insert->execute([$pageKey, $path, $caption, ++$order]);
                        $saved++;
                    }
                } catch (RuntimeException $ex) {
                    $failed[] = $name . ': ' . $ex->getMessage();
                }
            }
        }
        if ($saved) flash('success', $saved . ' photo' . ($saved === 1 ? '' : 's') . ' added to ' . $pageOptions[$pageKey] . '.');
        foreach ($failed as $msg) flash('error', $msg);
        if (!$saved && !$failed) flash('info', 'Choose at least one photo to upload.');
        redirect(admin_url('gallery.php?page_key=' . rawurlencode($pageKey)));
    }

    $id = (int) ($_POST['id'] ?? 0);
    $stmt = db()->prepare('SELECT * FROM gallery_images WHERE id = ?');
    $stmt->execute([$id]);
    $photo = $stmt->fetch();
    if (!$photo) {
        flash('error', 'That photo no longer exists.');
        redirect($self);
    }

    if ($action === 'update') {
        $pageKey = (string) ($_POST['page_key'] ?? $photo['page_key']);
        if (!isset($pageOptions[$pageKey])) {
            $pageKey = $photo['page_key'];
        }
        db()->prepare('UPDATE gallery_images SET caption = ?, page_key = ?, sort_order = ?, is_active = ? WHERE id = ?')->execute([
            mb_substr(trim((string) ($_POST['caption'] ?? '')), 0, 255),
            $pageKey,
            (int) ($_POST['sort_order'] ?? 0),
            empty($_POST['is_active']) ? 0 : 1,
            $id,
        ]);
        flash('success', 'Photo updated.');
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM gallery_images WHERE id = ?')->execute([$id]);
        delete_upload_if_unused($photo['image']);
        flash('success', 'Photo removed.');
    }
    redirect($self);
}

if ($filter !== '') {
    $stmt = db()->prepare('SELECT * FROM gallery_images WHERE page_key = ? ORDER BY sort_order, id');
    $stmt->execute([$filter]);
} else {
    $stmt = db()->query('SELECT * FROM gallery_images ORDER BY page_key = \'home\' DESC, page_key, sort_order, id');
}
$photos = $stmt->fetchAll();
$counts = db()->query('SELECT page_key, COUNT(*) FROM gallery_images GROUP BY page_key')->fetchAll(PDO::FETCH_KEY_PAIR);

$adminTitle = 'Gallery';
$adminPage = 'gallery';
require __DIR__ . '/includes/layout-top.php';
?>
<section class="panel upload-panel">
    <div class="panel-head"><h3>Upload photos</h3><span class="muted small">JPG, PNG or WebP, up to <?= (int) config('max_image_mb', 8) ?> MB each</span></div>
    <form method="post" enctype="multipart/form-data" class="upload-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="upload">
        <label class="dropzone">
            <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required data-dropzone>
            <strong>Choose photos</strong><span data-dropzone-label>or drag them here</span>
        </label>
        <div class="form-grid">
            <label class="field"><span>Show on</span>
                <select name="page_key">
                    <?php foreach ($pageOptions as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= $filter === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field"><span>Caption (optional)</span><input type="text" name="caption" maxlength="255" placeholder="Describes the photo for visitors and search engines"></label>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</section>

<div class="toolbar">
    <div class="tabs">
        <a href="<?= e(admin_url('gallery.php')) ?>" class="<?= $filter === '' ? 'active' : '' ?>">All <em><?= array_sum($counts) ?></em></a>
        <?php foreach ($pageOptions as $key => $label): ?>
            <a href="<?= e(admin_url('gallery.php?page_key=' . rawurlencode($key))) ?>" class="<?= $filter === $key ? 'active' : '' ?>"><?= e($label) ?> <em><?= (int) ($counts[$key] ?? 0) ?></em></a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (!$photos): ?>
    <section class="panel"><p class="empty">No photos here yet. Upload some above.</p></section>
<?php else: ?>
    <div class="photo-grid">
        <?php foreach ($photos as $p): ?>
            <article class="photo-card <?= $p['is_active'] ? '' : 'is-hidden' ?>">
                <div class="photo-thumb">
                    <img src="<?= e(asset($p['image'])) ?>" alt="<?= e($p['caption']) ?>" loading="lazy">
                    <span class="photo-page"><?= e($pageOptions[$p['page_key']] ?? $p['page_key']) ?></span>
                    <?php if (!$p['is_active']): ?><span class="photo-hidden">Hidden</span><?php endif; ?>
                </div>
                <form method="post" class="photo-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                    <label class="field"><span>Caption</span><input type="text" name="caption" maxlength="255" value="<?= e($p['caption']) ?>"></label>
                    <div class="form-grid tight">
                        <label class="field"><span>Page</span>
                            <select name="page_key">
                                <?php foreach ($pageOptions as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= $p['page_key'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="field"><span>Order</span><input type="number" name="sort_order" value="<?= (int) $p['sort_order'] ?>"></label>
                    </div>
                    <label class="switch"><input type="checkbox" name="is_active" value="1" <?= $p['is_active'] ? 'checked' : '' ?>><span>Visible</span></label>
                    <div class="photo-actions">
                        <button type="submit" class="btn btn-secondary btn-sm">Save</button>
                        <button type="submit" class="btn btn-danger btn-sm" form="del-<?= (int) $p['id'] ?>">Delete</button>
                    </div>
                </form>
                <form method="post" id="del-<?= (int) $p['id'] ?>" data-confirm="Remove this photo from the gallery?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                </form>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
