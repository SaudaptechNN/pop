<?php
require __DIR__ . '/includes/auth.php';
require_admin();

$statuses = ['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'archived' => 'Archived'];
$status = (string) ($_GET['status'] ?? '');
$q = trim((string) ($_GET['q'] ?? ''));
$perPage = 20;
$pageNo = max(1, (int) ($_GET['page'] ?? 1));

// Bulk actions on selected rows
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf(admin_url('inquiries.php'));
    $ids = array_values(array_filter(array_map('intval', (array) ($_POST['ids'] ?? []))));
    $action = (string) ($_POST['bulk_action'] ?? '');
    if ($ids) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        if ($action === 'delete') {
            db()->prepare("DELETE FROM inquiries WHERE id IN ($in)")->execute($ids);
            flash('success', count($ids) . ' inquiry(s) deleted.');
        } elseif (isset($statuses[$action])) {
            db()->prepare("UPDATE inquiries SET status = ? WHERE id IN ($in)")->execute(array_merge([$action], $ids));
            flash('success', count($ids) . ' inquiry(s) marked as ' . strtolower($statuses[$action]) . '.');
        }
    } else {
        flash('info', 'Select at least one inquiry first.');
    }
    redirect(admin_url('inquiries.php?' . http_build_query(array_filter(['status' => $status, 'q' => $q, 'page' => $pageNo > 1 ? $pageNo : null]))));
}

$where = [];
$params = [];
if (isset($statuses[$status])) {
    $where[] = 'status = ?';
    $params[] = $status;
}
if ($q !== '') {
    $where[] = '(name LIKE ? OR email LIKE ? OR event_type LIKE ? OR message LIKE ?)';
    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
    array_push($params, $like, $like, $like, $like);
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = db()->prepare("SELECT COUNT(*) FROM inquiries $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));
$pageNo = min($pageNo, $pages);
$offset = ($pageNo - 1) * $perPage;

$stmt = db()->prepare("SELECT * FROM inquiries $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$rows = $stmt->fetchAll();

$counts = db()->query('SELECT status, COUNT(*) c FROM inquiries GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$allCount = array_sum($counts);

function inquiries_link(array $overrides): string
{
    global $status, $q;
    $params = array_merge(['status' => $status, 'q' => $q], $overrides);
    return admin_url('inquiries.php?' . http_build_query(array_filter($params, function ($v) { return $v !== '' && $v !== null; })));
}

$adminTitle = 'Inquiries';
$adminPage = 'inquiries';
require __DIR__ . '/includes/layout-top.php';
?>
<div class="toolbar">
    <div class="tabs">
        <a href="<?= e(inquiries_link(['status' => '', 'page' => null])) ?>" class="<?= $status === '' ? 'active' : '' ?>">All <em><?= $allCount ?></em></a>
        <?php foreach ($statuses as $key => $label): ?>
            <a href="<?= e(inquiries_link(['status' => $key, 'page' => null])) ?>" class="<?= $status === $key ? 'active' : '' ?>"><?= e($label) ?> <em><?= (int) ($counts[$key] ?? 0) ?></em></a>
        <?php endforeach; ?>
    </div>
    <form class="search" method="get">
        <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, email, event...">
        <button class="btn btn-secondary btn-sm" type="submit">Search</button>
    </form>
</div>

<section class="panel">
    <?php if (!$rows): ?>
        <p class="empty"><?= $q !== '' ? 'No inquiries match your search.' : 'No inquiries here yet.' ?></p>
    <?php else: ?>
        <form method="post" id="bulk-form">
            <?= csrf_field() ?>
            <div class="bulk-bar">
                <select name="bulk_action" aria-label="Bulk action">
                    <option value="">With selected…</option>
                    <?php foreach ($statuses as $key => $label): ?><option value="<?= e($key) ?>">Mark as <?= e(strtolower($label)) ?></option><?php endforeach; ?>
                    <option value="delete">Delete</option>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm" data-bulk-submit>Apply</button>
                <span class="muted small"><?= $total ?> result<?= $total === 1 ? '' : 's' ?></span>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th class="check"><input type="checkbox" data-check-all aria-label="Select all"></th><th>Name</th><th>Event type</th><th>Event date</th><th>Received</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr class="<?= $r['status'] === 'new' ? 'is-new' : '' ?>">
                            <td class="check"><input type="checkbox" name="ids[]" value="<?= (int) $r['id'] ?>" aria-label="Select inquiry from <?= e($r['name']) ?>"></td>
                            <td><a class="row-link" href="<?= e(admin_url('inquiry.php?id=' . $r['id'])) ?>"><?= e($r['name']) ?></a><small><?= e($r['email']) ?></small></td>
                            <td><?= e($r['event_type']) ?></td>
                            <td><?= $r['event_date'] ? e(date('M j, Y', strtotime($r['event_date']))) : '<span class="muted">—</span>' ?></td>
                            <td><?= e(date('M j, Y g:i a', strtotime($r['created_at']))) ?></td>
                            <td><?= status_badge($r['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
        <?php if ($pages > 1): ?>
            <nav class="pagination" aria-label="Pages">
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <a href="<?= e(inquiries_link(['page' => $p > 1 ? $p : null])) ?>" class="<?= $p === $pageNo ? 'active' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
