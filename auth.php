<?php
require dirname(__DIR__, 2) . '/includes/bootstrap.php';

const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCK_MINUTES = 15;
const ADMIN_IDLE_MINUTES = 120;

function admin_url(string $path = ''): string
{
    return url('admin/' . ltrim($path, '/'));
}

function current_admin(): ?array
{
    static $admin = false;
    if ($admin === false) {
        $admin = null;
        if (!empty($_SESSION['admin_id'])) {
            $stmt = db()->prepare('SELECT id, username, last_login FROM admins WHERE id = ?');
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch() ?: null;
        }
    }
    return $admin;
}

function require_admin(): array
{
    $idle = isset($_SESSION['admin_seen']) && time() - $_SESSION['admin_seen'] > ADMIN_IDLE_MINUTES * 60;
    $admin = $idle ? null : current_admin();
    if (!$admin) {
        unset($_SESSION['admin_id'], $_SESSION['admin_seen']);
        if ($idle) {
            flash('info', 'You were signed out after a period of inactivity.');
        }
        redirect(admin_url('login.php'));
    }
    $_SESSION['admin_seen'] = time();
    return $admin;
}

// Rejects a POST with a bad CSRF token and sends the admin back.
function require_post_csrf(string $back): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
        flash('error', 'Your session expired. Please try again.');
        redirect($back);
    }
}

function client_ip(): string
{
    return substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45);
}

function login_locked(): bool
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempted_at > (NOW() - INTERVAL ' . LOGIN_LOCK_MINUTES . ' MINUTE)');
    $stmt->execute([client_ip()]);
    return (int) $stmt->fetchColumn() >= LOGIN_MAX_ATTEMPTS;
}

function attempt_login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT id, password_hash FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($password, $row['password_hash'])) {
        db()->prepare('INSERT INTO login_attempts (ip_address) VALUES (?)')->execute([client_ip()]);
        return false;
    }

    if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
        db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $row['id']]);
    }
    db()->prepare('DELETE FROM login_attempts WHERE ip_address = ?')->execute([client_ip()]);
    db()->prepare('UPDATE admins SET last_login = NOW() WHERE id = ?')->execute([$row['id']]);

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $row['id'];
    $_SESSION['admin_seen'] = time();
    unset($_SESSION['csrf']);
    return true;
}

function logout_admin(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_seen'], $_SESSION['csrf']);
    session_regenerate_id(true);
}

function new_inquiry_count(): int
{
    return (int) db()->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
}

function status_badge(string $status): string
{
    $labels = ['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'archived' => 'Archived'];
    return '<span class="badge badge-' . e($status) . '">' . e($labels[$status] ?? $status) . '</span>';
}

// Page keys a gallery photo can belong to.
function gallery_page_options(): array
{
    $options = ['home' => 'Home & Services page'];
    foreach (get_services(false) as $s) {
        $options[$s['slug']] = $s['page_title'];
    }
    return $options;
}
