<?php
/** @var string $page  current page key: home, services, or a service slug */
/** @var string $pageTitle */
$siteName = setting('site_name');
$phone = setting('phone');
$email = setting('email');
$navServices = get_services();
$fullTitle = isset($pageTitle) && $pageTitle !== '' ? $pageTitle . ' | ' . $siteName : $siteName;
$bookHref = $page === 'home' ? '#event-contact' : url('index.php') . '#event-contact';
$navItems = array_merge(
    [[url('index.php'), 'Home', 'home'], [url('services.php'), 'Services', 'services']],
    array_map(function ($s) { return [service_url($s['slug']), $s['card_title'], $s['slug']]; }, $navServices)
);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($fullTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription ?? setting('meta_description')) ?>">
    <link rel="icon" href="<?= e(asset(setting('logo'))) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,500;1,600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/styles.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/premium.css')) ?>">
</head>
<body data-page="<?= e($page) ?>">
<div id="preloader" class="preloader" aria-live="polite">
    <div class="preloader-inner">
        <div class="preloader-logo"><img src="<?= e(asset(setting('logo'))) ?>" alt="<?= e($siteName) ?>"></div>
        <div class="preloader-text-wrap"><h2><?= e(setting('preloader_title')) ?></h2><p><?= e(setting('preloader_text')) ?></p></div>
        <div class="loader-bar"><span></span></div>
        <div class="loader-label">Loading...</div>
    </div>
</div>
<script>
// The preloader only plays on the first page of a visit (per browser tab).
try {
    if (sessionStorage.getItem('popsy_preloader_seen')) {
        document.getElementById('preloader').remove();
    } else {
        sessionStorage.setItem('popsy_preloader_seen', '1');
    }
} catch (e) {}
</script>
<div class="page-shell">
    <div class="topbar">
        <div class="container topbar-inner">
            <div class="topbar-left"><?= e(setting('topbar_left')) ?></div>
            <span><?= e(setting('service_area')) ?></span>
            <div class="topbar-links">
                <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>">Call or Text <?= e($phone) ?></a>
                <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
            </div>
        </div>
    </div>
    <header class="site-header">
        <div class="container nav-wrap">
            <a class="brand" href="<?= e(url('index.php')) ?>" aria-label="<?= e($siteName) ?> home"><?php include __DIR__ . '/partials/logo.php'; ?></a>
            <nav class="main-nav" aria-label="Main navigation">
                <?php foreach ($navItems as $item): ?>
                    <a href="<?= e($item[0]) ?>" class="nav-link <?= $page === $item[2] ? 'active' : '' ?>"><?= e($item[1]) ?></a>
                <?php endforeach; ?>
            </nav>
            <a class="btn btn-primary nav-cta" href="<?= e($bookHref) ?>">Book an Event</a>
            <button class="nav-toggle" type="button" aria-label="Open menu" aria-expanded="false"><span></span><span></span></button>
        </div>
    </header>
