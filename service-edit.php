<?php
require __DIR__ . '/includes/auth.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$service = [
    'id' => 0, 'slug' => '', 'card_title' => '', 'card_text' => '', 'card_image' => '',
    'eyebrow' => '', 'page_title' => '', 'page_description' => '', 'hero_image' => '',
    'points' => '', 'layout' => 'detail', 'in_footer' => 0, 'sort_order' => 0, 'is_active' => 1,
];
if ($id) {
    $stmt = db()->prepare('SELECT * FROM services WHERE id = ?');
    $stmt->execute([$id]);
    $service = $stmt->fetch();
    if (!$service) {
        flash('error', 'That service no longer exists.');
        redirect(admin_url('services.php'));
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf(admin_url('service-edit.php' . ($id ? '?id=' . $id : '')));

    $oldImages = [$service['card_image'], $service['hero_image']];
    $oldSlug = $service['slug'];
    $data = [
        'card_title'       => trim((string) ($_POST['card_title'] ?? '')),
        'card_text'        => trim((string) ($_POST['card_text'] ?? '')),
        'eyebrow'          => trim((string) ($_POST['eyebrow'] ?? '')),
        'page_title'       => trim((string) ($_POST['page_title'] ?? '')),
        'page_description' => trim((string) ($_POST['page_description'] ?? '')),
        'points'           => implode("\n", lines($_POST['points'] ?? '')),
        'layout'           => ($_POST['layout'] ?? '') === 'cards' ? 'cards' : 'detail',
        'in_footer'        => empty($_POST['in_footer']) ? 0 : 1,
        'is_active'        => empty($_POST['is_active']) ? 0 : 1,
        'sort_order'       => (int) ($_POST['sort_order'] ?? 0),
        'card_image'       => $service['card_image'],
        'hero_image'       => $service['hero_image'],
    ];
    $slugInput = trim((string) ($_POST['slug'] ?? ''));
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $slugInput !== '' ? $slugInput : $data['page_title']), '-'));
    $data['slug'] = $slug;

    if ($data['card_title'] === '') $errors[] = 'Card title is required.';
    if ($data['page_title'] === '') $errors[] = 'Page title is required.';
    if ($slug === '') $errors[] = 'Page link (slug) is required.';
    if (in_array($slug, ['home', 'services', 'index', 'admin'], true)) $errors[] = 'That page link is reserved. Please choose another.';
    if (mb_strlen($data['card_title']) > 150 || mb_strlen($data['page_title']) > 150) $errors[] = 'Titles must be 150 characters or fewer.';
    if (mb_strlen($data['card_text']) > 255) $errors[] = 'Card text must be 255 characters or fewer.';

    if (!$errors) {
        $dupe = db()->prepare('SELECT COUNT(*) FROM services WHERE slug = ? AND id <> ?');
        $dupe->execute([$slug, $id]);
        if ($dupe->fetchColumn() > 0) {
            $errors[] = 'Another service already uses the page link “' . $slug . '”.';
        }
    }

    if (!$errors) {
        try {
            if ($path = handle_upload('card_image_file')) $data['card_image'] = $path;
            if ($path = handle_upload('hero_image_file')) $data['hero_image'] = $path;
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
        if ($data['card_image'] === '') $errors[] = 'Please upload a card image.';
        if ($data['hero_image'] === '') $errors[] = 'Please upload a page photo.';
    }

    if (!$errors) {
        $cols = array_keys($data);
        if ($id) {
            $set = implode(', ', array_map(function ($c) { return "$c = :$c"; }, $cols));
            db()->prepare("UPDATE services SET $set WHERE id = :id")->execute($data + ['id' => $id]);
            if ($oldSlug !== $slug) {
                db()->prepare('UPDATE gallery_images SET page_key = ? WHERE page_key = ?')->execute([$slug, $oldSlug]);
            }
            flash('success', 'Service saved.');
        } else {
            $placeholders = implode(', ', array_map(function ($c) { return ":$c"; }, $cols));
            db()->prepare('INSERT INTO services (' . implode(', ', $cols) . ") VALUES ($placeholders)")->execute($data);
            $id = (int) db()->lastInsertId();
            flash('success', 'Service created. Add photos to its gallery from the Gallery page.');
        }
        foreach ($oldImages as $old) {
            if ($old !== $data['card_image'] && $old !== $data['hero_image']) {
                delete_upload_if_unused($old);
            }
        }
        redirect(admin_url('service-edit.php?id=' . $id));
    }
    // Discard files uploaded during a failed save.
    foreach (['card_image', 'hero_image'] as $k) {
        if ($data[$k] !== $service[$k]) delete_upload_if_unused($data[$k]);
    }
    $service = array_merge($service, $data, ['card_image' => $service['card_image'], 'hero_image' => $service['hero_image'], 'slug' => $slugInput]);
}

$adminTitle = $id ? 'Edit Service' : 'Add Service';
$adminPage = 'services';
require __DIR__ . '/includes/layout-top.php';
?>
<a class="back-link" href="<?= e(admin_url('services.php')) ?>">&larr; All services</a>

<?php if ($errors): ?>
    <div class="alert alert-error" role="alert"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="edit-layout">
    <?= csrf_field() ?>
    <div class="edit-main">
        <section class="panel">
            <div class="panel-head"><h3>Service card</h3><span class="muted small">Shown on the home and services pages</span></div>
            <div class="form-grid">
                <label class="field"><span>Card title *</span><input type="text" name="card_title" maxlength="150" value="<?= e($service['card_title']) ?>" required></label>
                <label class="field"><span>Card text</span><input type="text" name="card_text" maxlength="255" value="<?= e($service['card_text']) ?>"></label>
            </div>
            <div class="field image-field">
                <span>Card image *</span>
                <div class="image-picker">
                    <img src="<?= e(asset($service['card_image'])) ?>" alt="" class="preview <?= $service['card_image'] === '' ? 'is-empty' : '' ?>" data-preview="card_image_file">
                    <input type="file" name="card_image_file" accept="image/jpeg,image/png,image/webp" <?= $service['card_image'] === '' ? 'required' : '' ?>>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head"><h3>Service page</h3></div>
            <div class="form-grid">
                <label class="field"><span>Small heading</span><input type="text" name="eyebrow" maxlength="120" value="<?= e($service['eyebrow']) ?>" placeholder="e.g. Beverage experiences"></label>
                <label class="field"><span>Page title *</span><input type="text" name="page_title" maxlength="150" value="<?= e($service['page_title']) ?>" required></label>
            </div>
            <label class="field"><span>Description</span><textarea name="page_description" rows="4"><?= e($service['page_description']) ?></textarea></label>
            <label class="field"><span>Signature service points</span><textarea name="points" rows="5" placeholder="One point per line"><?= e($service['points']) ?></textarea><small class="help">One per line. Shown in the “Signature service” box.</small></label>
            <div class="field image-field">
                <span>Page photo *</span>
                <div class="image-picker">
                    <img src="<?= e(asset($service['hero_image'])) ?>" alt="" class="preview <?= $service['hero_image'] === '' ? 'is-empty' : '' ?>" data-preview="hero_image_file">
                    <input type="file" name="hero_image_file" accept="image/jpeg,image/png,image/webp" <?= $service['hero_image'] === '' ? 'required' : '' ?>>
                </div>
            </div>
        </section>
    </div>

    <aside class="edit-side">
        <section class="panel">
            <div class="panel-head"><h3>Publish</h3></div>
            <label class="switch"><input type="checkbox" name="is_active" value="1" <?= $service['is_active'] ? 'checked' : '' ?>><span>Visible on website</span></label>
            <label class="switch"><input type="checkbox" name="in_footer" value="1" <?= $service['in_footer'] ? 'checked' : '' ?>><span>Show in footer links</span></label>
            <label class="field"><span>Page link (slug)</span><input type="text" name="slug" maxlength="80" value="<?= e($service['slug']) ?>" placeholder="auto from page title" pattern="[A-Za-z0-9\- ]*"><small class="help">Letters, numbers and dashes.</small></label>
            <label class="field"><span>Layout</span>
                <select name="layout">
                    <option value="detail" <?= $service['layout'] === 'detail' ? 'selected' : '' ?>>Detail boxes</option>
                    <option value="cards" <?= $service['layout'] === 'cards' ? 'selected' : '' ?>>All service cards</option>
                </select>
            </label>
            <label class="field"><span>Order</span><input type="number" name="sort_order" value="<?= (int) $service['sort_order'] ?>"></label>
            <button type="submit" class="btn btn-primary btn-block"><?= $id ? 'Save changes' : 'Create service' ?></button>
            <?php if ($id && $service['is_active']): ?>
                <a class="btn btn-secondary btn-block" href="<?= e(service_url($service['slug'])) ?>" target="_blank" rel="noopener">View page &#8599;</a>
            <?php endif; ?>
        </section>
    </aside>
</form>
<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
