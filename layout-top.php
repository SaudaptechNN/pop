<?php
/** @var string $adminTitle */
/** @var string $adminPage  key of the active menu item */
$admin = current_admin();
$newCount = new_inquiry_count();
$menu = [
    'dashboard' => ['index.php', 'Dashboard', '<path d="M3 13h8V3H3zM13 21h8V11h-8zM3 21h8v-6H3zM13 3v6h8V3z"/>'],
    'inquiries' => ['inquiries.php', 'Inquiries', '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>'],
    'services'  => ['services.php', 'Services', '<path d="M4 6h16M4 12h16M4 18h10"/>'],
    'gallery'   => ['gallery.php', 'Gallery', '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>'],
    'settings'  => ['settings.php', 'Site Settings', '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>'],
    'account'   => ['account.php', 'My Account', '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>'],
];
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($adminTitle) ?> | <?= e(setting('site_name')) ?> Admin</title>
    <link rel="icon" href="<?= e(asset(setting('logo'))) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(admin_url('assets/admin.css')) ?>">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="sidebar" id="sidebar">
        <a class="sidebar-brand" href="<?= e(admin_url('index.php')) ?>">
            <img src="<?= e(asset(setting('logo'))) ?>" alt="">
            <span><strong><?= e(setting('site_name')) ?></strong><small>Admin Panel</small></span>
        </a>
        <nav class="sidebar-nav">
            <?php foreach ($menu as $key => [$href, $label, $icon]): ?>
                <a href="<?= e(admin_url($href)) ?>" class="<?= $adminPage === $key ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><?= $icon ?></svg>
                    <span><?= e($label) ?></span>
                    <?php if ($key === 'inquiries' && $newCount > 0): ?><em class="nav-count"><?= $newCount ?></em><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-foot">
            <a href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener" class="view-site">View website &#8599;</a>
            <form method="post" action="<?= e(admin_url('logout.php')) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="logout-btn">Sign out</button>
            </form>
            <p class="sidebar-credit">Designed &amp; Developed by <strong>Muhammad Saud Javed</strong></p>
        </div>
    </aside>
    <div class="sidebar-backdrop" data-close-sidebar></div>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="menu-toggle" type="button" aria-label="Open menu" aria-controls="sidebar" aria-expanded="false"><span></span><span></span><span></span></button>
            <h1><?= e($adminTitle) ?></h1>
            <div class="topbar-user"><span class="avatar"><?= e(strtoupper(substr($admin['username'], 0, 1))) ?></span><span class="who"><?= e($admin['username']) ?></span></div>
        </header>
        <div class="admin-content">
            <?php foreach (take_flashes() as $f): ?>
                <div class="alert alert-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div>
            <?php endforeach; ?>
