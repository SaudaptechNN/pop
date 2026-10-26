<?php
require __DIR__ . '/includes/auth.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM inquiries WHERE id = ?');
$stmt->execute([$id]);
$inq = $stmt->fetch();
if (!$inq) {
    flash('error', 'That inquiry no longer exists.');
    redirect(admin_url('inquiries.php'));
}
$self = admin_url('inquiry.php?id=' . $id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf($self);
    if (($_POST['action'] ?? '') === 'delete') {
        db()->prepare('DELETE FROM inquiries WHERE id = ?')->execute([$id]);
        flash('success', 'Inquiry deleted.');
        redirect(admin_url('inquiries.php'));
    }
    $newStatus = (string) ($_POST['status'] ?? $inq['status']);
    if (!in_array($newStatus, ['new', 'read', 'replied', 'archived'], true)) {
        $newStatus = $inq['status'];
    }
    $notes = mb_substr(trim((string) ($_POST['admin_notes'] ?? '')), 0, 5000);
    db()->prepare('UPDATE inquiries SET status = ?, admin_notes = ? WHERE id = ?')->execute([$newStatus, $notes, $id]);
    flash('success', 'Inquiry updated.');
    redirect($self);
}

// Opening a new inquiry marks it as read.
if ($inq['status'] === 'new') {
    db()->prepare("UPDATE inquiries SET status = 'read' WHERE id = ?")->execute([$id]);
    $inq['status'] = 'read';
}

$replySubject = rawurlencode('Re: Your event inquiry - ' . setting('site_name'));
$replyBody = rawurlencode("Hi {$inq['name']},\n\nThank you for reaching out to " . setting('site_name') . ".\n\n");

$adminTitle = 'Inquiry';
$adminPage = 'inquiries';
require __DIR__ . '/includes/layout-top.php';
?>
<a class="back-link" href="<?= e(admin_url('inquiries.php')) ?>">&larr; All inquiries</a>

<div class="two-col wide-left">
    <section class="panel">
        <div class="panel-head">
            <div>
                <h3><?= e($inq['name']) ?></h3>
                <p class="muted small">Received <?= e(date('l, M j, Y \a\t g:i a', strtotime($inq['created_at']))) ?></p>
            </div>
            <?= status_badge($inq['status']) ?>
        </div>
        <dl class="detail-list">
            <div><dt>Email</dt><dd><a href="mailto:<?= e($inq['email']) ?>"><?= e($inq['email']) ?></a></dd></div>
            <div><dt>Event type</dt><dd><?= e($inq['event_type']) ?></dd></div>
            <div><dt>Event date</dt><dd><?= $inq['event_date'] ? e(date('l, M j, Y', strtotime($inq['event_date']))) : 'Not specified' ?></dd></div>
        </dl>
        <h4 class="sub-head">Their vision</h4>
        <div class="message-box"><?= nl2br(e($inq['message'])) ?></div>
        <div class="actions-row">
            <a class="btn btn-primary" href="mailto:<?= e($inq['email']) ?>?subject=<?= $replySubject ?>&amp;body=<?= $replyBody ?>">Reply by email</a>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h3>Manage</h3></div>
        <form method="post" class="form-stack">
            <?= csrf_field() ?>
            <label class="field"><span>Status</span>
                <select name="status">
                    <?php foreach (['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'archived' => 'Archived'] as $k => $label): ?>
                        <option value="<?= e($k) ?>" <?= $inq['status'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field"><span>Private notes</span><textarea name="admin_notes" rows="6" placeholder="Quote sent, deposit received, follow-up date..."><?= e($inq['admin_notes'] ?? '') ?></textarea></label>
            <button type="submit" class="btn btn-primary btn-block">Save changes</button>
        </form>
        <form method="post" class="danger-zone" data-confirm="Delete this inquiry permanently?">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="btn btn-danger btn-block">Delete inquiry</button>
        </form>
    </section>
</div>
<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
