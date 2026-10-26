<?php
require __DIR__ . '/includes/auth.php';

if (current_admin()) {
    redirect(admin_url('index.php'));
}

$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_valid()) {
        $error = 'Your session expired. Please try again.';
    } elseif (login_locked()) {
        $error = 'Too many failed attempts. Please wait ' . LOGIN_LOCK_MINUTES . ' minutes and try again.';
    } elseif ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } elseif (attempt_login($username, $password)) {
        redirect(admin_url('index.php'));
    } else {
        $error = 'Incorrect username or password.';
    }
}
$flashes = take_flashes();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in | <?= e(setting('site_name')) ?> Admin</title>
    <link rel="icon" href="<?= e(asset(setting('logo'))) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(admin_url('assets/admin.css')) ?>">
</head>
<body class="login-body">
<main class="login-wrap">
    <div class="login-visual" style="background-image:url('<?= e(asset(setting('hero_slide_1'))) ?>')">
        <div class="login-visual-copy">
            <span class="eyebrow">Admin Panel</span>
            <h2>Manage every moment of <em><?= e(setting('site_name')) ?>.</em></h2>
        </div>
    </div>
    <div class="login-card">
        <img class="login-logo" src="<?= e(asset(setting('logo'))) ?>" alt="<?= e(setting('site_name')) ?>">
        <span class="eyebrow">Welcome back</span>
        <h1>Sign in</h1>
        <p class="muted">Use your admin account to manage the website.</p>

        <?php foreach ($flashes as $f): ?><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div><?php endforeach; ?>
        <?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>

        <form method="post" class="form-stack" autocomplete="on">
            <?= csrf_field() ?>
            <label class="field"><span>Username</span><input type="text" name="username" value="<?= e($username) ?>" autocomplete="username" required autofocus></label>
            <label class="field"><span>Password</span>
                <span class="password-wrap"><input type="password" name="password" autocomplete="current-password" required><button type="button" class="show-pass" data-toggle-password>Show</button></span>
            </label>
            <button type="submit" class="btn btn-primary btn-block">Sign in</button>
        </form>
        <a class="back-link" href="<?= e(url('index.php')) ?>">&larr; Back to website</a>
    </div>
</main>
<script src="<?= e(admin_url('assets/admin.js')) ?>"></script>
</body>
</html>
