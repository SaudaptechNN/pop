<?php
// ---------------------------------------------------------------
// Database
// ---------------------------------------------------------------
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = $GLOBALS['config']['db'];
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], $c['port'], $c['name']);
        try {
            $pdo = new PDO($dsn, $c['user'], $c['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $ex) {
            http_response_code(500);
            $detail = APP_DEBUG ? '<p style="color:#a45f61">' . e($ex->getMessage()) . '</p><p>Make sure MySQL is running in XAMPP and <code>database/thepopsybar.sql</code> has been imported.</p>' : '';
            exit('<div style="font-family:sans-serif;max-width:640px;margin:80px auto;padding:24px"><h2>Database connection failed</h2>' . $detail . '</div>');
        }
    }
    return $pdo;
}

function config(string $key, $default = null)
{
    return $GLOBALS['config'][$key] ?? $default;
}

// ---------------------------------------------------------------
// Output helpers
// ---------------------------------------------------------------
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Admin-edited headings may use <em>, <br> and <strong> for styling.
function rich($value): string
{
    return strip_tags((string) $value, '<em><br><strong>');
}

function lines($text): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $text)), 'strlen'));
}

// ---------------------------------------------------------------
// URLs
// ---------------------------------------------------------------
function base_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $configured = trim((string) config('base_url', ''));
    if ($configured !== '') {
        return $base = '/' . trim($configured, '/') . (trim($configured, '/') === '' ? '' : '/');
    }
    $docRoot = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $appRoot = str_replace('\\', '/', (string) realpath(APP_ROOT));
    $path = ($docRoot !== '' && stripos($appRoot, $docRoot) === 0) ? substr($appRoot, strlen($docRoot)) : '';
    $path = trim($path, '/');
    return $base = '/' . ($path === '' ? '' : $path . '/');
}

function url(string $path = ''): string
{
    return base_url() . ltrim($path, '/');
}

// Stored media paths are relative to the site root (assets/..., uploads/...).
function asset(?string $path): string
{
    $path = (string) $path;
    if ($path === '' || preg_match('#^(https?:)?//#i', $path)) {
        return $path;
    }
    return url($path);
}

function service_url(string $slug): string
{
    return url('service.php?slug=' . rawurlencode($slug));
}

function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

// ---------------------------------------------------------------
// Settings
// ---------------------------------------------------------------
function settings_schema(): array
{
    static $schema = null;
    return $schema ?? ($schema = require APP_ROOT . '/includes/settings-schema.php');
}

function setting_defaults(): array
{
    $defaults = [];
    foreach (settings_schema() as $group) {
        foreach ($group['fields'] as $key => $field) {
            $defaults[$key] = $field['default'] ?? '';
        }
    }
    return $defaults;
}

function settings_all(bool $refresh = false): array
{
    static $values = null;
    if ($values === null || $refresh) {
        $values = setting_defaults();
        foreach (db()->query('SELECT setting_key, setting_value FROM settings') as $row) {
            $values[$row['setting_key']] = (string) $row['setting_value'];
        }
    }
    return $values;
}

function setting(string $key, string $default = ''): string
{
    $all = settings_all();
    return array_key_exists($key, $all) ? $all[$key] : $default;
}

function save_setting(string $key, string $value): void
{
    $stmt = db()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $stmt->execute([$key, $value]);
}

// ---------------------------------------------------------------
// Content queries
// ---------------------------------------------------------------
function get_services(bool $activeOnly = true): array
{
    $sql = 'SELECT * FROM services' . ($activeOnly ? ' WHERE is_active = 1' : '') . ' ORDER BY sort_order, id';
    return db()->query($sql)->fetchAll();
}

function get_service(string $slug, bool $activeOnly = true): ?array
{
    $stmt = db()->prepare('SELECT * FROM services WHERE slug = ?' . ($activeOnly ? ' AND is_active = 1' : '') . ' LIMIT 1');
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function get_gallery(string $pageKey): array
{
    $stmt = db()->prepare('SELECT * FROM gallery_images WHERE page_key = ? AND is_active = 1 ORDER BY sort_order, id');
    $stmt->execute([$pageKey]);
    return $stmt->fetchAll();
}

// ---------------------------------------------------------------
// CSRF + flash messages
// ---------------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    return isset($_POST['csrf']) && is_string($_POST['csrf']) && hash_equals(csrf_token(), $_POST['csrf']);
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

// ---------------------------------------------------------------
// Uploads
// ---------------------------------------------------------------
const UPLOAD_TYPES = [
    'image' => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'],
    'video' => ['video/mp4' => 'mp4', 'video/webm' => 'webm'],
];

/**
 * Saves an uploaded file into uploads/ and returns its site-relative path,
 * or null when no file was chosen. Throws RuntimeException on invalid files.
 */
function handle_upload(string $field, string $kind = 'image'): ?string
{
    $file = $_FILES[$field] ?? null;
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException('The file is larger than the server allows (' . ini_get('upload_max_filesize') . ').');
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('The upload failed. Please try again.');
    }

    $maxMb = $kind === 'video' ? (int) config('max_video_mb', 60) : (int) config('max_image_mb', 8);
    if ($file['size'] > $maxMb * 1024 * 1024) {
        throw new RuntimeException("The file must be smaller than {$maxMb} MB.");
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowed = UPLOAD_TYPES[$kind];
    if (!isset($allowed[$mime])) {
        $list = strtoupper(implode(', ', array_unique(array_values($allowed))));
        throw new RuntimeException("Unsupported file type. Allowed: {$list}.");
    }
    if ($kind === 'image' && @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('That file is not a valid image.');
    }

    $dir = APP_ROOT . '/uploads/' . date('Y/m');
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new RuntimeException('Could not create the uploads folder.');
    }
    $name = bin2hex(random_bytes(10)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }
    return 'uploads/' . date('Y/m') . '/' . $name;
}

// Removes a file from uploads/ when nothing in the database still uses it.
// Original files in assets/ are never deleted.
function delete_upload_if_unused(?string $path): void
{
    $path = (string) $path;
    if (strpos($path, 'uploads/') !== 0 || strpos($path, '..') !== false) {
        return;
    }
    $checks = [
        'SELECT COUNT(*) FROM gallery_images WHERE image = ?',
        'SELECT COUNT(*) FROM services WHERE card_image = ? OR hero_image = ?',
        'SELECT COUNT(*) FROM settings WHERE setting_value = ?',
    ];
    foreach ($checks as $sql) {
        $stmt = db()->prepare($sql);
        $stmt->execute(array_fill(0, substr_count($sql, '?'), $path));
        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }
    }
    $full = APP_ROOT . '/' . $path;
    if (is_file($full)) {
        @unlink($full);
    }
}
