<?php
require __DIR__ . '/includes/auth.php';
$admin = require_admin();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf(admin_url('account.php'));
    $username = trim((string) ($_POST['username'] ?? ''));
    $current = (string) ($_POST['current_password'] ?? '');
    $new = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    $stmt = db()->prepare('SELECT password_hash FROM admins WHERE id = ?');
    $stmt->execute([$admin['id']]);
    if (!password_verify($current, (string) $stmt->fetchColumn())) {
        $errors[] = 'Your current password is incorrect.';
    }
    if (!preg_match('/^[A-Za-z0-9._@-]{3,60}$/', $username)) {
        $errors[] = 'Username must be 3–60 characters: letters, numbers, dot, dash, underscore or @.';
    } else {
        $dupe = db()->prepare('SELECT COUNT(*) FROM admins WHERE username = ? AND id <> ?');
        $dupe->execute([$username, $admin['id']]);
        if ($dupe->fetchColumn() > 0) {
            $errors[] = 'That username is already taken.';
        }
    }
    if ($new !== '') {
        if (strlen($new) < 8) $errors[] = 'New password must be at least 8 characters.';
        if ($new !== $confirm) $errors[] = 'New passwords do not match.';
    }

    if (!$errors) {
        db()->prepare('UPDATE admins SET username = ? WHERE id = ?')->execute([$username, $admin['id']]);
        if ($new !== '') {
            db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
            session_regenerate_id(true);
        }
        flash('success', $new !== '' ? 'Account and password updated.' : 'Account updated.');
        redirect(admin_url('account.php'));
    }
}

$adminTitle = 'My Account';
$adminPage = 'account';
require __DIR__ . '/includes/layout-top.php';
?>
<?php if ($errors): ?>
    <div class="alert alert-error" role="alert"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<form method="post" class="panel narrow-panel">
    <?= csrf_field() ?>
    <div class="panel-head">
        <h3>Sign-in details</h3>
        <?php if ($admin['last_login']): ?><span class="muted small">Last sign-in <?= e(date('M j, Y g:i a', strtotime($admin['last_login']))) ?></span><?php endif; ?>
    </div>
    <div class="form-stack">
        <label class="field"><span>Username</span><input type="text" name="username" value="<?= e($_POST['username'] ?? $admin['username']) ?>" autocomplete="username" required></label>
        <label class="field"><span>New password</span><input type="password" name="new_password" autocomplete="new-password" minlength="8" placeholder="Leave blank to keep current password"></label>
        <label class="field"><span>Confirm new password</span><input type="password" name="confirm_password" autocomplete="new-password"></label>
        <hr>
        <label class="field"><span>Current password *</span><input type="password" name="current_password" autocomplete="current-password" required><small class="help">Required to save any change.</small></label>
        <button type="submit" class="btn btn-primary">Save account</button>
    </div>
</form>
<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
